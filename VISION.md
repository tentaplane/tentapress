# VISION

## The fastest way for agencies to ship and manage small sites

### One sentence
A PHP-first platform for agencies to build, publish, and centrally manage landing pages and small sites - quickly, safely, and with minimal operational overhead.

---

## Who this is for
### Primary users
- Agency owners and leads who want predictable delivery and healthier margins
- Producers and project managers who need repeatable delivery
- Developers who want a modern workflow without reinventing WordPress every project

### Secondary users
- Client editors who need to update content safely without breaking layouts

---

## The wedge
We win by being the best default for:
- Landing pages
- Brochure sites
- Small content sites

We do not try to replace WordPress for every edge case on day one.

---

## Product pillars
### 1) Speed without chaos
- Templates and recipes that ship real sites quickly
- Pages made from constrained sections
- Strong defaults that keep outcomes consistent

### 2) Client-safe editing
- Content changes without layout breakage
- Clear permissions and audit trail
- Predictable publishing flow

### 3) Central management
- Portfolio dashboard for all sites
- Standards and shared components across sites
- Observability that reduces surprises (status, domains, SSL, forms, publishing)

### 4) Easy to run
- PHP-based, works locally without Docker
- SQLite-first onboarding for instant evaluation
- Clear path to production databases and deployment options

### 5) Portability and trust
- Exports and offboarding are first-class
- Redirects and SEO metadata are not an afterthought
- No intentional lock-in

### 6) Modular core
- Extensions are packages with explicit boundaries
- The core stays stable, lean, and well-tested

---

## Core concepts
- **Agency** owns many **Clients**
- A **Client** owns one or more **Sites**
- A **Site** contains **Pages**
- A **Page** is a list of **Sections** (typed blocks with validated props)
- Optional **Collections** exist, but are progressive - you should not need them to ship v1

---

## Non-goals
- Recreating the entire WordPress plugin ecosystem
- Supporting arbitrary, untrusted code upload as “plugins”
- Becoming a general-purpose e-commerce platform
- Being a blank-canvas page builder

---

## What success looks like
- A new contributor can get a working instance running in minutes
- An agency can ship a good-looking 5-page site without modelling content
- A client can safely update copy and images without breaking layout
- A team can manage 50+ small sites from one console with confidence
- Releases are routine because CI and tests enforce quality

---

## Roadmap shape (high level)
### Foundation
- SQLite-first local install
- Agency console scaffold
- Pages + sections editor
- Publishing flow
- Basic exports

### Agency-ready
- Multi-site dashboard
- Permissions and audit trail
- Shared components and standards
- Offboarding and exports matured

### Extensible ecosystem
- Stable plugin extension points
- First-party modules shipped as packages
- Community contributions guided by clear contracts and examples