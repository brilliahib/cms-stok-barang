# Implementation Plan: Separate Role Login Panels

**Branch**: `001-role-login-panels` | **Date**: 2026-10-17 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/001-role-login-panels/spec.md`

## Summary

Provide distinct administrator and employee sign-in panels while keeping the existing account store, session guard, role authorization, and password recovery. Keep `/login` as the neutral role chooser for existing links and named-route callers. Each role-specific login attempt must be constrained to the role selected by its server-side route, and both roles continue to the existing shared dashboard.

## Technical Context

**Language/Version**: PHP 8.2+

**Primary Dependencies**: Laravel Framework 12; Laravel Breeze 2.3 auth scaffolding; Blade; Tailwind CSS 3; Vite 7

**Storage**: Existing relational `users` table; no schema change planned

**Testing**: Pest 3 / Laravel feature tests

**Target Platform**: Laravel web application

**Project Type**: Server-rendered web application

**Performance Goals**: No new performance target; sign-in should retain existing request behavior and throttling.

**Constraints**: Use existing `admin` and `karyawan` role values; do not infer or change a role from client-submitted data; preserve the existing session guard, session regeneration, login throttling, password reset broker, route compatibility, and admin authorization; avoid a database migration.

**Scale/Scope**: Two role-specific sign-in panels and their routes, the neutral `/login` chooser, shared authentication request handling, links to the panels, and focused auth feature coverage.

## Constitution Check

The checked-in `.specify/memory/constitution.md` still contains template placeholders and defines no active project principles or gates. No enforceable constitution gate can be evaluated. The proposed design adds no new dependency, service, data store, or role system and keeps the existing application layers.

**Gate result**: No actionable constitution rules are present; proceed with the project’s existing Laravel structure and auth conventions.

## Project Structure

### Documentation (this feature)

```text
specs/001-role-login-panels/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
└── contracts/
    └── auth-ui.md
```

### Source Code (repository root)

```text
app/Http/Controllers/Auth/AuthenticatedSessionController.php
app/Http/Requests/Auth/LoginRequest.php
routes/auth.php
resources/views/auth/login.blade.php
resources/views/auth/               # role panels and role chooser
resources/views/welcome.blade.php   # existing entry link, if changed
tests/Feature/Auth/AuthenticationTest.php
tests/Feature/Auth/PasswordResetTest.php
```

**Structure Decision**: Keep the current single Laravel web application structure. Add role-specific routes and Blade views in the existing authentication layers. Reuse `AuthenticatedSessionController`, `LoginRequest`, `User`, the `web` guard, and the current `/dashboard`; add no new project or persistence layer. Keep `/login` named `login` as a neutral chooser to preserve existing redirects and links.

## Complexity Tracking

No constitution violations or added architectural complexity require justification.
