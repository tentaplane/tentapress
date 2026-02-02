# MANIFESTO

## An agency-first, WordPress-adjacent site platform

### The problem we’re solving
Agencies and small teams need to ship landing pages and small sites fast, keep them stable, and manage them at scale. WordPress is often the default because it’s familiar and quick - but the long-term cost shows up as plugin sprawl, fragile builds, inconsistent performance, and constant maintenance.

This project exists to keep the **speed** and remove the **drag**.

---

## Our promise
Build and manage small sites with the pace of WordPress, the confidence of product engineering, and the operational clarity agencies need.

- **Fast to ship** - beautiful sites in hours, not weeks
- **Hard to break** - client-safe editing and sensible constraints
- **Easy to run** - PHP-based, no Docker required, SQLite-first for instant setup
- **Easy to leave** - export and offboarding are first-class, not an afterthought
- **Easy to extend** - modular by design, with a clear plugin architecture

---

## Principles
### 1) Start simple, grow into structure
You can ship without modelling content upfront. Structure should be optional and progressive, not a ceremony.

### 2) Guardrails beat flexibility
Unlimited flexibility creates support debt. We prefer constrained systems that produce consistently good outcomes.

### 3) Agencies are the primary user
We optimise for agency workflows: repeatable builds, multi-site visibility, permissions, handover, and ongoing care plans.

### 4) “Clone to running” is a feature
If you can’t get a working instance locally in minutes, we’ve failed.

### 5) No surprise maintenance
Updates should be predictable. Dependencies should be explicit. The platform should not rely on a fragile web of third-party add-ons to be usable.

### 6) Portability builds trust
Lock-in kills adoption. Exports, redirects, and handover paths are core product features.

### 7) Product engineering discipline, without the bureaucracy
We write down decisions and expectations so contributors can move fast with confidence.

- **PRDs** for meaningful user-facing features
- **ADRs** for architectural decisions that affect future change cost
- **CI and tests** to protect behaviour and prevent regressions

---

## What we are building
### A central agency console
A single place to create, observe, and maintain many small sites and landing pages - without logging into dozens of dashboards.

### A page system that matches agency pace
Pages built from sections with sensible constraints. Ship first, refine later.

### A modular core
Extensions are packages, not hacks. The core stays lean and stable.

---

## What we are not building
- A general-purpose replacement for every WordPress site on earth
- An all-things-to-all-people plugin marketplace on day one
- A platform that requires Kubernetes knowledge to run
- A “blank canvas” builder where anything goes and everything breaks

---

## Quality bar
- Security and tenant isolation are non-negotiable
- Changes must be testable
- The README install path must stay accurate (CI should prove it)
- Backwards compatibility matters - migrations and releases should be considered, not casual

---

## Open source stance
This is a public project intended to be useful in the real world.

- We value contributors, documentation, and maintainability
- We prefer small, reviewable changes over big-bang rewrites
- We will keep the core approachable for PHP developers, including those coming from WordPress

---

## The north star
Agencies should be able to say:

> “We can ship faster than WordPress, clients can’t break it, and we can manage everything from one place - without a pile of plugins.”