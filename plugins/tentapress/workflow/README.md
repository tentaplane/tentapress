# Workflow

Editorial workflow, approvals, and publishing governance for TentaPress pages and posts.

## Plugin Details

| Field | Value |
|------|-------|
| ID | `tentapress/workflow` |
| Version | `0.1.0` |
| Provider | `TentaPress\\Workflow\\WorkflowServiceProvider` |

## Purpose

Provide a first-party workflow layer for agency editorial teams without replacing the existing public publish model.

## Features

- Editorial states for draft, in review, changes requested, and approved
- Assignment of owner, reviewer, and approver per page or post
- Revision-backed working copies for edits to already published content
- Publish and schedule guards that require approval before release
- Workflow queue for assigned work, review work, and scheduled publications
- Audit history for assignments, transitions, approvals, schedules, publishes, and publish blocks
- Laravel events for assignment, transition, approval, schedule, and publish lifecycle hooks

## Admin Menu

| Label | Route | Capability | Position |
|------|-------|------------|----------|
| Workflow | `tp.workflow.index` | `view_workflow_queue` | 35 |

## Configuration

This plugin is intentionally self-contained and ships with strong defaults. It does not require root config or `.env` changes for normal use.

## Dependency Behaviour

- `tentapress/users` provides workflow actors and capabilities
- `tentapress/pages` and `tentapress/posts` provide the managed content types
- `tentapress/revisions` provides working-copy storage for published content edits
- If the plugin is disabled, workflow routes, menu entries, UI panels, and publish guards disappear cleanly

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/workflow
./vendor/bin/pint --dirty
composer test:filter -- Workflow
php artisan tp:workflow:publish-scheduled
```
