<?php

class Am24h_ClsDebug
{
    public function register_hooks(): void
    {
        if (is_admin() || ! $this->is_enabled()) {
            return;
        }

        add_action('wp_head', array($this, 'render_debug_script'), 99);
    }

    private function is_enabled(): bool
    {
        if (! isset($_GET['clsdebug'])) {
            return false;
        }

        return sanitize_text_field(wp_unslash($_GET['clsdebug'])) === '1';
    }

    public function render_debug_script(): void
    {
        ?>
        <script id="am24h-cls-debug">
            (function () {
                'use strict';

                const marks = [];
                const shiftHits = new Map();
                const phaseMarks = {
                    styleCssAt: null,
                    fontsReadyAt: null,
                    windowLoadAt: null
                };
                const trackedSelectors = [
                    'header.cc-header',
                    '.cc-header__top',
                    '.cc-header__bottom',
                    '.cc-header__top-container',
                    '.cc-header__left',
                    '.cc-header__logo',
                    '.cc-header__logo .custom-logo-link',
                    '.cc-header__logo .custom-logo',
                    '.cc-header__bottom-container',
                    '.cc-header__bottom-menu',
                    '.cc-single__hero',
                    '.cc-single__breadcrumbs',
                    '.cc-single__title',
                    '.cc-single__meta',
                    '.cc-single__share',
                    '.cc-single__media',
                    '.rank-math-breadcrumb, .breadcrumbs, .breadcrumb, nav[aria-label*="breadcrumb" i]',
                    '.cc-single__breadcrumbs *',
                    '.after-post-meta, [data-after-post-meta], [class*="after-post-meta" i], [class*="post-meta" i]',
                    '.cc-single',
                    '.cc-single__post',
                    '.cc-single__post-header',
                    '.cc-single__post-title',
                    '.cc-single__post-metadata',
                    '.cc-share-bar',
                    '.cc-single__post-thumbnail',
                    '.cc-single__post-content',
                    '[id*="ad" i], [class*="ad-" i], [class*="ads" i], .adsbygoogle, iframe, .widget'
                ];

                const logPrefix = '[CLSDEBUG]';
                const now = function () {
                    return Number(performance.now().toFixed(2));
                };

                const pushMark = function (label, data) {
                    const payload = {
                        t: now(),
                        label: label,
                        data: data || {}
                    };

                    if (label === 'style-css-load' || label === 'style-css-already-active') {
                        phaseMarks.styleCssAt = payload.t;
                    }

                    if (label === 'document-fonts-ready') {
                        phaseMarks.fontsReadyAt = payload.t;
                    }

                    if (label === 'window-load') {
                        phaseMarks.windowLoadAt = payload.t;
                    }

                    marks.push(payload);
                    console.log(logPrefix, 'MARK', payload);
                };

                const classifyPhase = function (entryTime) {
                    if (phaseMarks.styleCssAt === null || entryTime < phaseMarks.styleCssAt) {
                        return 'antes-do-style.css';
                    }

                    if (phaseMarks.fontsReadyAt === null || entryTime < phaseMarks.fontsReadyAt) {
                        return 'apos-style.css';
                    }

                    if (phaseMarks.windowLoadAt === null || entryTime < phaseMarks.windowLoadAt) {
                        return 'apos-document.fonts.ready';
                    }

                    return 'apos-window.load';
                };

                const descriptor = function (el) {
                    if (!(el instanceof Element)) {
                        return '(not-an-element)';
                    }

                    const id = el.id ? '#' + el.id : '';
                    const cls = (typeof el.className === 'string' && el.className.trim() !== '')
                        ? '.' + el.className.trim().split(/\s+/).slice(0, 4).join('.')
                        : '';

                    return el.tagName.toLowerCase() + id + cls;
                };

                const uniqueSelector = function (el) {
                    if (!(el instanceof Element)) {
                        return '';
                    }

                    if (el.id) {
                        return '#' + CSS.escape(el.id);
                    }

                    const path = [];
                    let node = el;

                    while (node && node.nodeType === Node.ELEMENT_NODE && path.length < 6) {
                        let part = node.tagName.toLowerCase();

                        if (node.classList.length > 0) {
                            part += '.' + Array.from(node.classList).slice(0, 2).map(function (item) {
                                return CSS.escape(item);
                            }).join('.');
                        }

                        const parent = node.parentElement;

                        if (parent) {
                            const siblings = Array.from(parent.children).filter(function (sibling) {
                                return sibling.tagName === node.tagName;
                            });

                            if (siblings.length > 1) {
                                const index = siblings.indexOf(node) + 1;
                                part += ':nth-of-type(' + index + ')';
                            }
                        }

                        path.unshift(part);

                        const candidate = path.join(' > ');

                        try {
                            if (document.querySelectorAll(candidate).length === 1) {
                                return candidate;
                            }
                        } catch (error) {
                            break;
                        }

                        node = parent;
                    }

                    return path.join(' > ');
                };

                const gatherAncestors = function (el) {
                    const items = [];
                    let current = el instanceof Element ? el.parentElement : null;

                    for (let i = 0; i < 4 && current; i += 1) {
                        items.push({
                            level: i + 1,
                            descriptor: descriptor(current),
                            selector: uniqueSelector(current)
                        });

                        current = current.parentElement;
                    }

                    return items;
                };

                const getGeometry = function (el) {
                    if (!(el instanceof Element)) {
                        return null;
                    }

                    const cs = getComputedStyle(el);
                    const rect = el.getBoundingClientRect();

                    return {
                        offsetHeight: el.offsetHeight,
                        clientHeight: el.clientHeight,
                        rect: {
                            x: Number(rect.x.toFixed(2)),
                            y: Number(rect.y.toFixed(2)),
                            width: Number(rect.width.toFixed(2)),
                            height: Number(rect.height.toFixed(2))
                        },
                        minHeight: cs.minHeight,
                        paddingTop: cs.paddingTop,
                        paddingBottom: cs.paddingBottom,
                        marginTop: cs.marginTop,
                        marginBottom: cs.marginBottom,
                        lineHeight: cs.lineHeight,
                        fontFamily: cs.fontFamily,
                        display: cs.display,
                        alignItems: cs.alignItems,
                        gap: cs.gap,
                        position: cs.position
                    };
                };

                const highlight = function (el) {
                    if (!(el instanceof HTMLElement)) {
                        return;
                    }

                    const prevOutline = el.style.outline;
                    const prevOffset = el.style.outlineOffset;
                    el.style.outline = '3px solid #ff3b30';
                    el.style.outlineOffset = '2px';

                    window.setTimeout(function () {
                        el.style.outline = prevOutline;
                        el.style.outlineOffset = prevOffset;
                    }, 1800);
                };

                const snapshot = function (label) {
                    const report = [];

                    trackedSelectors.forEach(function (selector) {
                        const target = document.querySelector(selector);

                        report.push({
                            selector: selector,
                            found: Boolean(target),
                            element: descriptor(target),
                            uniqueSelector: target ? uniqueSelector(target) : '',
                            geometry: getGeometry(target)
                        });
                    });

                    console.groupCollapsed(logPrefix + ' GEOMETRY ' + label);
                    console.table(report.map(function (row) {
                        return {
                            selector: row.selector,
                            found: row.found,
                            element: row.element,
                            height: row.geometry ? row.geometry.rect.height : null,
                            offsetHeight: row.geometry ? row.geometry.offsetHeight : null,
                            minHeight: row.geometry ? row.geometry.minHeight : null,
                            lineHeight: row.geometry ? row.geometry.lineHeight : null,
                            fontFamily: row.geometry ? row.geometry.fontFamily : null,
                            display: row.geometry ? row.geometry.display : null,
                            alignItems: row.geometry ? row.geometry.alignItems : null,
                            gap: row.geometry ? row.geometry.gap : null
                        };
                    }));
                    console.groupEnd();
                };

                const watchMainStylesheet = function () {
                    const selector = 'link[href*="assets/styles/style.css"]';
                    const links = Array.from(document.querySelectorAll(selector));

                    if (links.length === 0) {
                        pushMark('style-css-link-not-found');
                        return;
                    }

                    links.forEach(function (link, index) {
                        const onceLoad = function () {
                            pushMark('style-css-load', {
                                index: index,
                                rel: link.rel,
                                as: link.getAttribute('as') || ''
                            });
                            snapshot('after-style.css-load');
                        };

                        link.addEventListener('load', onceLoad, { once: true });

                        if (link.sheet) {
                            pushMark('style-css-already-active', {
                                index: index,
                                rel: link.rel
                            });
                            snapshot('style.css-already-active');
                        }
                    });
                };

                const summarize = function () {
                    const ranking = Array.from(shiftHits.entries())
                        .map(function (entry) {
                            return { selector: entry[0], hits: entry[1] };
                        })
                        .sort(function (a, b) {
                            return b.hits - a.hits;
                        });

                    console.group(logPrefix + ' SUMMARY');
                    console.log(logPrefix, 'Marks', marks);
                    console.log(logPrefix, 'Phase marks', phaseMarks);
                    console.table(ranking);
                    console.groupEnd();
                };

                pushMark('debug-script-start', {
                    criticalCssPresent: Boolean(document.getElementById('critical-css'))
                });
                snapshot('script-start');

                try {
                    const observer = new PerformanceObserver(function (list) {
                        list.getEntries().forEach(function (entry) {
                            if (!entry || entry.hadRecentInput) {
                                return;
                            }

                            const sources = (entry.sources || []).map(function (source) {
                                const el = source.node instanceof Element ? source.node : null;
                                const sel = el ? uniqueSelector(el) : '';

                                if (sel !== '') {
                                    shiftHits.set(sel, (shiftHits.get(sel) || 0) + 1);
                                }

                                if (el) {
                                    highlight(el);
                                }

                                return {
                                    element: descriptor(el),
                                    selector: sel,
                                    phase: classifyPhase(Number(entry.startTime.toFixed(2))),
                                    tag: el ? el.tagName.toLowerCase() : '',
                                    id: el ? el.id : '',
                                    classes: el ? (el.className || '') : '',
                                    rect: source.currentRect || source.previousRect || null,
                                    geometry: getGeometry(el),
                                    ancestors: gatherAncestors(el)
                                };
                            });

                            console.group(logPrefix + ' LAYOUT_SHIFT');
                            console.log({
                                value: entry.value,
                                startTime: Number(entry.startTime.toFixed(2)),
                                phase: classifyPhase(Number(entry.startTime.toFixed(2))),
                                sources: sources
                            });
                            console.groupEnd();
                        });
                    });

                    observer.observe({ type: 'layout-shift', buffered: true });
                    pushMark('layout-shift-observer-ready');
                } catch (error) {
                    console.warn(logPrefix, 'PerformanceObserver layout-shift unavailable', error);
                }

                document.addEventListener('DOMContentLoaded', function () {
                    pushMark('dom-content-loaded');
                    snapshot('dom-content-loaded');
                    watchMainStylesheet();
                }, { once: true });

                if (document.fonts && document.fonts.ready) {
                    document.fonts.ready.then(function () {
                        pushMark('document-fonts-ready');
                        snapshot('document-fonts-ready');
                    }).catch(function (error) {
                        console.warn(logPrefix, 'document.fonts.ready failed', error);
                    });
                } else {
                    pushMark('document-fonts-api-unavailable');
                }

                window.addEventListener('load', function () {
                    pushMark('window-load');
                    snapshot('window-load');
                    summarize();
                }, { once: true });
            })();
        </script>
        <?php
    }
}
