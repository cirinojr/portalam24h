(function () {
  'use strict';

  const hasCopyButton = document.querySelector('[data-share-copy]');

  if (!hasCopyButton) {
    return;
  }

  const statusElement = document.querySelector('[data-share-copy-status]');
  const container = statusElement ? statusElement.closest('.cc-share-bar') : null;
  const successMessage = container?.dataset.copySuccess || 'Link copied';
  const fallbackMessage = container?.dataset.copyFallback || 'Press Ctrl+C to copy the link';

  let announceTimer = null;

  const announce = (message) => {
    if (!statusElement) {
      return;
    }

    if (announceTimer) {
      globalThis.clearTimeout(announceTimer);
    }

    statusElement.textContent = message;
    announceTimer = globalThis.setTimeout(() => {
      statusElement.textContent = '';
      announceTimer = null;
    }, 1800);
  };

  const focusForManualCopy = (url) => {
    const input = document.createElement('input');
    input.type = 'text';
    input.value = url;
    document.body.appendChild(input);
    input.focus();
    input.select();

    globalThis.setTimeout(() => {
      input.remove();
    }, 2000);

    return false;
  };

  document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-share-copy]');

    if (!button) {
      return;
    }

    const shareUrl = button.dataset.shareUrl;

    if (!shareUrl) {
      return;
    }

    if (navigator?.clipboard && typeof navigator.clipboard.writeText === 'function') {
      navigator.clipboard
        .writeText(shareUrl)
        .then(() => {
          announce(successMessage);
        })
        .catch(() => {
          if (!focusForManualCopy(shareUrl)) {
            announce(fallbackMessage);
          }
        });

      return;
    }

    if (!focusForManualCopy(shareUrl)) {
      announce(fallbackMessage);
    }
  });
})();
