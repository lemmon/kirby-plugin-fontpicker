# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-01-18

### Added
- Validate font selections in the Panel, with guidance for unresolved or unsafe catalog entries.

### Security
- Treat catalog family names containing `<` as invalid and skip CSS fallback tokens with `<` to prevent breaking out of `<style>` blocks.

## [1.0.0] - 2025-11-16

### Added
- Initial release of the Kirby Font Picker plugin.
