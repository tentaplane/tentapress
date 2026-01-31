# Quality and delivery

This project aims to be fast to contribute to and safe to change.

### We rely on
- CI to validate the install path and core behaviour
- Tests to protect user-visible workflows
- PRDs and ADRs to keep decisions and intent discoverable

### CI expectations (high level)
- formatting and linting
- unit and feature tests
- basic “fresh install” smoke test using SQLite

### Testing expectations (high level)
- Unit tests for pure logic and validation
- Feature tests for workflows and isolation boundaries
- Keep E2E tests minimal and high-value (smoke flows only)