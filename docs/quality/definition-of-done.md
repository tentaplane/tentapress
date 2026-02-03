# Definition of done

A change is “done” when:

- It has a clear user or product goal
- It includes tests when a test harness exists (or a stated reason and follow-up when it does not)
- CI passes (Pint/build/migrations; tests when present)
- Docs are updated when behaviour changes (README, PRD, ADR as needed)
- It does not worsen the “clone to running” path

For user-facing features:
- permissions are defined
- error states are handled
- migration and rollout are considered
