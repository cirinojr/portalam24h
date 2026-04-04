# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- `docs/ARCHITECTURE.md` with detailed implementation notes on assets, critical CSS, template modularity, dependency posture, and maintainability decisions.
- GPL-compatible `LICENSE` file suitable for WordPress theme distribution (`GPL-2.0-or-later`).
- Technical README sections for architecture, security, i18n, workflow, and future engineering improvements.

### Changed
- Rewrote `README.md` to reflect real architecture and practical engineering decisions.
- Updated Composer package license metadata to `GPL-2.0-or-later`.

## [0.1.0] - 2026-04-04

### Added
- Baseline modular WordPress editorial theme architecture under `includes/`.
- Critical CSS inline strategy and separate non-critical stylesheet loading.
- Local typography pipeline for font validation, storage, registration, and generated `@font-face` output.
- Optional cookie consent banner and accessibility popup implemented with vanilla JavaScript.
- Translation catalog scaffolding in `languages/`.
- PHPCS tooling and WordPress coding standards configuration.
