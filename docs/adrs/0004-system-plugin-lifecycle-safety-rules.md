# ADR 0004 - System Enforces Plugin Lifecycle Safety Rules

- Status: accepted
- Date: 2026-02-14
- Decision owners: TentaPress maintainers
- Related PRD(s): docs/project-status.md
- Supersedes:
- Superseded by:

---

## Context
Plugin enable/disable operations can break admin/runtime flows if critical plugins are disabled accidentally or if non-installed providers are enabled.

---

## Decision
Enforce lifecycle safety in `PluginRegistry` and `tp:plugins` commands:

- Protected plugin IDs cannot be disabled by bulk disable unless `--force` is provided.
- Enabling validates provider availability (autoloadable class or explicit provider path contract where applicable).
- Default plugins can be enabled automatically from config for new discoveries.
- Cache rebuild is part of command lifecycle outcomes.

---

## Options considered
### Option A
- Pros:
  - Prevents common self-inflicted outages.
  - Makes dangerous operations explicit (`--force`).
- Cons:
  - Adds opinionated guardrails that may need override in edge environments.

### Option B
- Pros:
  - Fully permissive lifecycle commands are simpler.
- Cons:
  - Higher risk of accidental lockout and broken admin.

### Option C (optional)
- Pros:
  - External orchestration could own all safety policies.
- Cons:
  - Pushes critical controls outside system package.

---

## Consequences
What changes as a result of this decision?

- Positive:
  - Safer operations by default.
  - Clear command semantics for dangerous actions.
- Negative:
  - Some workflows require explicit force flags.
- Trade-offs:
  - Safety and recoverability are prioritized over unrestricted operator freedom.

---

## Notes
Implemented primarily in:
- `packages/tentapress/system/src/Plugin/PluginRegistry.php`
- `packages/tentapress/system/src/Console/PluginsCommand.php`
- `config/tentapress.php`
