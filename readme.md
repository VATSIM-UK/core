# Core

Core application for VATSIM UK
[www.vatsim.uk](https://vatsim.uk)

## Getting Started

 - [Setup guide](https://github.com/VATSIM-UK/core/blob/main/.github/setup.md)
 - [Contributing guide](https://github.com/VATSIM-UK/core/blob/main/.github/CONTRIBUTING.md)
 - [Support page](https://github.com/VATSIM-UK/core/blob/main/.github/SUPPORT.md)

## Architecture note

Yes: domain logic should remain independent of transport layers, and controllers should not contain business rules.

Controllers are transport adapters (HTTP/I-O boundaries) and should stay focused on request parsing, authorization, and response formatting. Services that hold domain/business rules must remain transport-agnostic so the same behavior can be reused from HTTP, console commands, queues, and other entry points.

In practice:

- Keep controllers and commands focused on transport concerns only; move business/domain rules into services or domain classes.
- Keep domain/business rules in reusable service/domain classes.
- Avoid embedding transport decisions (for example, HTTP-specific status handling) in domain logic.
