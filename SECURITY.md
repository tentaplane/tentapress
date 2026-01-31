# SECURITY

## Security policy

We take security seriously. This project is designed to be safe by default and suitable for running multi-site, multi-client workloads - but security is a shared responsibility between maintainers, contributors, and operators.

---

## Supported versions
Security fixes are applied to the latest release and the `main` branch.

If you are running an older release, please upgrade to a supported version before reporting an issue unless you have a strong reason not to.

---

## Reporting a vulnerability
Please do not open a public GitHub issue for security vulnerabilities.

Instead, report issues privately using one of the following:
- GitHub Security Advisories (preferred, if enabled in the repo)
- A private email address listed in the repository (if present)

Include:
- A clear description of the issue and impact
- Steps to reproduce (or a proof of concept where possible)
- Affected versions or commit hashes
- Any suggested mitigation or fix
- Your preferred attribution name (if you want credit)

If you are unsure whether something is a vulnerability, report it anyway - we would rather investigate than miss it.

---

## What we consider a security issue
Examples include:
- Remote code execution (RCE)
- Authentication or authorisation bypass
- Cross-site scripting (XSS)
- SQL injection
- Cross-site request forgery (CSRF)
- Data leakage across tenants (multi-tenancy isolation failures)
- Insecure direct object references (IDOR)
- Secrets exposure (tokens, API keys, credentials)
- Supply chain risks (malicious dependency changes)

---

## Safe defaults we aim to maintain
- Secure session and CSRF protection
- Least-privilege roles and permissions
- Strong tenant isolation boundaries
- Dependency updates and review
- Audit trails for privileged actions (where applicable)

---

## Disclosure process
We aim to:
1) Acknowledge receipt within a reasonable timeframe
2) Confirm whether we can reproduce the issue
3) Work on a fix and prepare a release
4) Credit the reporter (if requested) once a fix is available

Please allow time for maintainers to investigate and coordinate a fix before public disclosure.

---

## Security best practices for operators
If you deploy this project publicly:
- Use HTTPS everywhere
- Keep dependencies up to date
- Store secrets outside the repo and rotate them regularly
- Restrict admin access and use strong authentication
- Use least-privilege database credentials
- Apply rate limiting and a WAF where appropriate
- Back up data and test restores regularly

---

## Thanks
Thank you for helping keep the project and its users safe.