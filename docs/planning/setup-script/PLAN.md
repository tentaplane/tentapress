# Setup Script UX Overhaul Plan

## Goal

Create a WordPress-style setup experience for `php tentapress.php setup` that feels fast, friendly, and non-technical.
Hide noisy technical output by default while keeping full logs available for troubleshooting.

## Audience

Primary: non-technical WordPress users (site owners, content teams, agency PMs). Secondary: developers who may want
verbose output and advanced options.

## UX Principles

- **Short, guided steps:** Clear progress labels with a small number of stages.
- **Plain language:** Avoid jargon (use “Install dependencies” vs “composer install”).
- **Safe defaults:** Prefer auto choices, explain only when needed.
- **Quiet by default:** Show a clean status line, save details to a log.
- **Recovery-friendly:** Provide next actions on failure and point to the log.

## Proposed Experience

### Stage 1: Welcome

- Friendly welcome banner and a one-line explanation.
- Quick readiness check (PHP version, Composer availability) with plain-language outcomes.

### Stage 2: Install Core

Progress list with short labels:

1. Preparing environment (create `.env`, sqlite file)
2. Installing dependencies
3. Initializing app (key, migrations, storage link)
4. Activating plugins and permissions

Only show:

- A single-line status per step
- A spinner or simple “...” indicator

All command output is captured to a log file.

### Stage 3: Theme + Demo

- “Install a starter theme?” default to Tailwind.
- “Build theme assets now?” default to Yes if bun/npm detected (defaults to bun if detected, npm next if detected).
- “Create demo homepage?” default to Yes.

### Stage 4: Admin User

- Simple prompts with helpful hints.
- If password blank, show where to find the generated password.

### Stage 5: Finish

- Short success message
- Next steps with URLs or commands
- Where to find logs

## Output Strategy

### Default (quiet)

- Show a short progress UI (step labels + success/fail).
- Redirect command output to a log file:
    - Example: `storage/logs/setup-YYYYMMDD-HHMMSS.log`.

### Verbose Mode

- `--verbose` flag to show raw output (passthru to console).
- `--quiet` flag to suppress even progress (useful for CI).

### Error Handling

- On failure, show:
    - Step name that failed
    - One-line reason if known
    - “See full log at …”
    - One recovery hint (e.g., “Run with --verbose for details”).

## Functional Enhancements

- **Step timing:** Record elapsed time per step and overall time.
- **Idempotence:** Detect existing installs and skip safely with clear messaging.
- **Dependency checks:** If Composer/Bun missing, provide a friendly explanation and a link/command.
- **Safe defaults:** Keep `--no-user` behavior, but make prompts optional via `--yes`/`--defaults`.

## Proposed Flags

- `--verbose` show raw command output
- `--quiet` suppress UI (exit codes only)
- `--yes` accept default choices (theme, assets, demo)
- `--no-user` skip admin creation (existing behavior)
- `--log=<path>` optional custom log location

## Implementation Plan

### Phase 1: UI Scaffolding

- Introduce a simple output helper:
    - `stepStart(label)`
    - `stepSuccess()` / `stepFail()`
    - `info()` / `warn()` / `error()`
- Add a log writer that captures output for each step.

### Phase 2: Command Runner

- Replace `passthru` with a runner that can:
    - pipe to log
    - optionally echo to STDOUT when `--verbose`
    - return exit codes
- Add timing and friendly messages.

### Phase 3: Prompts + Defaults

- Implement `--yes` default auto-answers.
- Make all prompts skippable by defaults.
- Improve phrasing to be WordPress-like.

### Phase 4: Failure UX

- Catch non-zero exits
- Print recovery hints and log location
- Ensure exit codes propagate for CI

### Phase 5: Polish

- Refine banner and step ordering
- Add a short “what happened” summary at the end

## Acceptance Criteria

- A non-technical user can run `php tentapress.php setup` without understanding Composer or migrations.
- Console output is clean and friendly; technical logs are stored in a file.
- `--verbose` shows current detailed output.
- Failed steps show a clear, single-line error and where to find logs.
- Setup remains idempotent and safe for re-runs.
