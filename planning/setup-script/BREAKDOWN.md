# Setup Script UX Overhaul - Task Breakdown

## Scope

Improve `tentapress.php setup` with a friendly, WordPress-style flow. Hide technical command output by default, log to file, and add simple progress UI. Keep behavior backward compatible and safe for re-runs.

## Phase 0: Baseline Review

- [ ] Map current steps and outputs in `tentapress.php`
- [ ] Confirm all prompts and defaults (theme install, asset build, demo page, admin user)
- [ ] Identify all command invocations to route through the new runner

## Phase 1: Output + Logging Infrastructure

- [ ] Add a structured output helper
    - `stepStart(label)`
    - `stepSuccess()`
    - `stepFail(error)`
    - `info()`, `warn()`, `error()`
- [ ] Add log file handling
    - Default path: `storage/logs/setup-YYYYMMDD-HHMMSS.log`
    - Ensure directory exists
    - Record start/end timestamps and command outputs
- [ ] Add flags
    - `--verbose` shows raw command output
    - `--quiet` suppresses UI
    - `--log=<path>` override log path

## Phase 2: Command Runner Refactor

- [ ] Replace `passthru` usage with a runner that:
    - Pipes stdout/stderr to log
    - Echoes to terminal in `--verbose`
    - Returns exit codes
- [ ] Track per-step timing and total elapsed time
- [ ] Stop on failure with a single-line error + log location

## Phase 3: Prompt UX + Defaults

- [ ] Add `--yes` / `--defaults` to auto-accept recommended answers
- [ ] Simplify prompt wording (WordPress-style)
    - “Install a starter theme?”
    - “Build theme assets now?”
    - “Create a demo homepage?”
- [ ] Keep `--no-user` behavior intact

## Phase 4: Step Flow Improvements

- [ ] Group core setup into a clear 4-step flow
    1. Prepare environment (.env, sqlite)
    2. Install dependencies (Composer)
    3. Initialize app (key, migrate, storage link)
    4. Enable plugins + permissions
- [ ] Present theme/demo steps as optional stage
- [ ] Present admin creation as final stage

## Phase 5: Error + Recovery UX

- [ ] Standardize failure messaging:
    - Step name that failed
    - One-line reason if known
    - Log location
    - “Re-run with --verbose for details”
- [ ] Preserve non-zero exit codes for CI

## Phase 6: Final Polish

- [ ] Update welcome banner and closing summary
- [ ] Add next steps with URLs/commands
- [ ] Confirm quiet output is clean and readable

## Acceptance Checks

- [ ] Default output is clean and understandable for non-technical users
- [ ] Full output available in log file
- [ ] `--verbose` prints raw command output
- [ ] Setup remains idempotent and safe to re-run
- [ ] Errors are clear and actionable
