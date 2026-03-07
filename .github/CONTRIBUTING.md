# Contributing to Core

Welcome, and thanks for your interest in contributing to VATSIM UK Core.

## Before You Start

- Read the local setup guide: [setup.md](https://github.com/VATSIM-UK/core/blob/main/.github/setup.md)
- Read the architecture guide: [docs/architecture.md](https://github.com/VATSIM-UK/core/blob/main/docs/architecture.md)
- If you need help, use the support page: [SUPPORT.md](https://github.com/VATSIM-UK/core/blob/main/.github/SUPPORT.md)

## Contribution Scope

Some work cannot be delegated publicly due to privileged access requirements. Publicly open issues are suitable for external contribution.

If you are new to the project, start with issues labeled `good-first-issue` or `up-for-grabs`.

## Contributor License Agreement

By submitting code as an individual, you agree that VATSIM UK can use your amendments, fixes, patches, changes, modifications, submissions, and creations in Core, and that ownership of your submitted changes transfers to VATSIM UK in full.

See [LICENSE.md](https://github.com/VATSIM-UK/core/blob/main/LICENSE.md) for licensing information.

## Issue Workflow

- Search existing issues before opening a new one.
- Use the issue template and provide complete reproduction/context details.
- Add screenshots or documentation links when helpful.
- If you cannot self-assign, comment on the issue to indicate you are taking it.

## Pull Request Workflow

1. Fork the repository.
2. Create a branch named `issue-[issue_number]` (for example, `issue-1234`).
3. Implement a focused change set for one issue.
4. Add or update tests as required.
5. Push to your fork and open a PR to `main`.
6. Link the related issue in the PR description.

### PR Expectations

- PR title clearly describes the change.
- PR description includes:
  - What changed
  - Why it changed
  - How it was verified
  - Whether behavior changed or should be identical
- Include screenshots for UI changes.
- Keep structural refactors and functional feature changes separate where possible.

## Architecture Expectations for New Work

Core is actively being refactored. Contributions should follow the architecture standards from [docs/architecture.md](https://github.com/VATSIM-UK/core/blob/main/docs/architecture.md).

In practice, this means:

- Keep controllers focused on HTTP concerns.
- Move business/domain logic into services.
- Prefer structured service outputs (DTO-style payloads or DTO classes).
- Keep dependency injection and service bindings consistent.
- For queued external integrations, use dedicated queues plus overlap/rate-limit protections.

## Testing Requirements

Refactors and behavior changes require meaningful coverage:

- Add unit tests for extracted or newly introduced services.
- Keep feature tests validating external contracts (status codes, payload shape, auth behavior).
- Add job configuration tests when queue names, middleware, retries, or backoff are part of the change.

PRs without adequate test coverage may be asked to add tests before merge.

## Refactor PR Guidelines

For architectural PRs:

- Keep behavior parity unless a functional change is explicitly intended and documented.
- Highlight risk areas in the PR description.
- Prefer smaller PRs for easier review and reduced merge conflicts.
- Link related refactor work to the relevant open issue.

## Code of Conduct

By contributing, you agree to follow the project Code of Conduct: [CODE_OF_CONDUCT.md](https://github.com/VATSIM-UK/core/blob/main/.github/CODE_OF_CONDUCT.md).
