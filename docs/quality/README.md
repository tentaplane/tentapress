# Quality and delivery

This project aims to be fast to contribute to and safe to change.

### We rely on
- CI to validate the install path and core behaviour
- Tests to protect user-visible workflows (when a test harness exists)
- PRDs and ADRs to keep decisions and intent discoverable

### CI expectations (current)
- formatting and linting (Pint)
- build + migrations (fresh install smoke)
- tests when configured (not yet in CI)

### Testing expectations (once configured)
- Unit tests for pure logic and validation
- Feature tests for workflows and isolation boundaries
- Keep E2E tests minimal and high-value (smoke flows only)
