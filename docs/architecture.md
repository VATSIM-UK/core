# Core Architecture

This document captures the current architecture direction, including patterns introduced through [#4545](https://github.com/VATSIM-UK/core/pull/4545), [#4549](https://github.com/VATSIM-UK/core/pull/4549), [#4559](https://github.com/VATSIM-UK/core/pull/4559), and [#4577](https://github.com/VATSIM-UK/core/pull/4577).

Use this as a practical guide for new work and refactors.

## Architectural Principles

- Keep transport concerns (HTTP, queue payload delivery) separate from domain/business logic.
- Favor explicit dependencies through constructor injection.
- Keep refactors behavior-preserving unless a functional change is intentionally scoped and documented.
- Prefer small, composable units that can be covered by focused tests.

## Layer Boundaries

### Controllers

Controllers should:

- Handle HTTP concerns only (request/response, status codes, auth checks).
- Delegate business logic to dedicated services.
- Avoid direct query orchestration, eligibility logic, or response payload assembly beyond lightweight response wiring.

Controllers should not:

- Contain multi-step domain workflows.
- Directly own external integration behavior.
- Duplicate business rules already handled in services.

### Services

Services are the home for domain/business logic.

Service expectations:

- One clear responsibility per service.
- Typed inputs and outputs where practical.
- No reliance on controller state.
- Return structured payloads (DTO-style arrays or DTO classes) that controllers can pass through with minimal transformation.

### Repositories and Integrations

Use repositories/integration classes to isolate persistence and external API interaction details from orchestration logic.

This keeps services focused on decision-making, not transport mechanics.

## DTO-Style Response Shaping

When extracting logic from controllers:

- Move payload assembly to service-layer structures.
- Keep response key names stable unless a breaking change is explicitly intended.
- Centralize null/default handling in one place (service or DTO), not across multiple controllers.

## Queue and Job Standards

For jobs that interact with external systems:

- Route work to dedicated queues per integration area.
- Set explicit retry behavior (`$tries`) and backoff (`$backoff`).
- Use overlap protection (`WithoutOverlapping`) for account/check scoped work.
- Use rate limiting middleware (`RateLimitedWithRedis`) for downstream protection.
- Add structured logs for start, completion, and failure (`failed(Throwable $exception)`).

If a new queue is introduced, update worker/Horizon configuration so the queue is actively consumed.

## Webhook Processing Standards

Webhook pipelines should be deterministic and observable:

- Process action batches in a deterministic order (for example, sorted by timestamp).
- Use explicit action-to-handler mapping instead of large `switch` blocks where possible.
- Handle unknown actions with clear error logging and an explicit client error response.
- Handle empty action payloads gracefully.
- Share repeated delta-parsing logic through a common helper/trait to avoid divergence.

## Service Provider Binding Standards

Use consistent container binding patterns:

- Put straightforward class mappings in `$bindings` and `$singletons`.
- Keep factory/closure-based registrations in `register()` only when construction requires runtime configuration.
- Avoid empty provider overrides.
- Keep boot-order-sensitive package behavior explicit (for example, permission registrar overrides) when safety requires it.

## Testing Expectations

Refactors are not complete without coverage:

- Add unit tests for newly extracted services and domain logic.
- Add configuration tests for queue settings and middleware where job behavior depends on them.
- Keep feature/integration tests focused on contract parity (response shape, status codes, auth behavior).

## Refactor Delivery Rules

For architecture PRs:

- Keep structural refactors separate from functional changes whenever possible.
- Keep PR scope focused and reviewable.
- Link related work to the relevant open issue.
- Call out intended vs unintended behavioral changes explicitly in the PR description.
