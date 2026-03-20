# Core Architecture

A practical guide to architectural patterns for new work and refactors.

## Principles

- Separate transport concerns (HTTP, queue delivery) from domain/business logic.
- Favor explicit constructor injection.
- Prefer small, composable units that can be covered by focused tests.

## Layer Boundaries

### Controllers

Handle HTTP concerns only: request/response, status codes, auth checks. Delegate all business logic to services. Controllers must not contain domain workflows, eligibility logic, or direct external integration behavior.

### Services

The home for domain/business logic. Each service should have one clear responsibility, typed inputs/outputs where practical, and no reliance on controller state. Return structured payloads (DTO-style arrays or DTO classes) that controllers pass through with minimal transformation.

### Repositories and Integrations

Isolate persistence and external API interaction from orchestration logic so services remain focused on decision-making.

## Queue and Job Standards

- Route work to dedicated queues per integration area.
- Set explicit `$tries` and `$backoff`.
- Use `WithoutOverlapping` for account/check-scoped work and `RateLimitedWithRedis` for downstream protection.
- Log start, completion, and failure via `failed(Throwable $exception)`.
- When adding a new queue, update Horizon configuration so it is actively consumed.

## Webhook Processing Standards

- Process action batches in deterministic order (e.g. sorted by timestamp).
- Use explicit action-to-handler mapping rather than large `switch` blocks.
- Log unknown actions clearly and return an explicit client error response.
- Share repeated delta-parsing logic through a common helper or trait.

## Service Provider Binding Standards

- Use `$bindings` / `$singletons` for straightforward class mappings.
- Use `register()` closures only when construction requires runtime configuration.
- Avoid empty provider overrides.

## Testing Expectations

- Unit tests for newly extracted services and domain logic.
- Job configuration tests where queue name, retries, or middleware are significant.
- Feature/integration tests focused on contract parity (response shape, status codes, auth behavior).
