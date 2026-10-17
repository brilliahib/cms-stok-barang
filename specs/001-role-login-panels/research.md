# Research: Separate Role Login Panels

**Date**: 2026-10-17

## Existing Project Findings

- The app uses Laravel Framework `^12.0`, PHP `^8.2`, Breeze `^2.3`, Blade views, and Pest feature tests.
- Authentication currently uses one `AuthenticatedSessionController`, one `LoginRequest`, and the default `web` session guard. The request validates email/password, applies a five-attempt rate limit keyed by normalized email and IP, and calls `Auth::attempt`.
- The users table has a `role` string defaulting to `karyawan`; existing code uses `admin` for administrator access. There are no separate identity tables or guards.
- The current login route is `/login` and is referenced by the root redirect, welcome page, registration view, password reset completion, and tests. Password recovery uses shared named routes and one password broker.
- Both roles currently use `/dashboard`. Admin-only routes are already protected by the `admin` middleware.
- The project constitution contains only template placeholders, so it supplies no active design constraints.

## Decisions

### Reuse the existing session guard and account provider

**Decision**: Keep one user account store and web session guard. Treat the selected panel as a server-side role constraint for a login attempt.

**Rationale**: The application already distinguishes roles on one user record and enforces admin permissions after authentication. Separate panels do not require separate identity stores or sessions.

**Alternatives considered**: Separate guards or providers would duplicate authentication configuration without a requirement for simultaneous identities or independent account stores.

**Source**: [Laravel 12 Authentication: Introduction](https://laravel.com/docs/12.x/authentication#introduction).

### Constrain credentials before creating an authenticated session

**Decision**: Derive the allowed role from the selected panel route and include it as a condition in the credential attempt. Keep the existing throttling and regenerate the session only after successful authentication.

**Rationale**: A wrong-role account should fail as an ordinary failed credential attempt and must not become authenticated even briefly. The submitted form must not choose its own allowed role.

**Alternatives considered**: Authenticating first and checking the role afterward introduces an avoidable authenticated state before rejection. Separate login-request implementations would duplicate validation and throttling logic.

**Source**: [Laravel 12 Authentication: Manually Authenticating Users](https://laravel.com/docs/12.x/authentication#authenticating-users), including additional credential conditions and session regeneration; [Login Throttling](https://laravel.com/docs/12.x/authentication#login-throttling).

### Keep `/login` as the neutral role chooser

**Decision**: Retain the current named `/login` route as a page linking to both dedicated role panels. Use distinct GET and POST endpoints for administrator and employee credentials. After a successful login, route either role to the existing shared dashboard.

**Rationale**: Existing app links, tests, password reset completion, and Laravel guest redirects refer to the named `login` route. Keeping it avoids breaking callers while removing the single mixed credential form. The application has no separate role dashboard today.

**Alternatives considered**: Removing `/login` would break existing route consumers; assigning `/login` to one role would make the entry point ambiguous and strand the other role.

**Source**: [Laravel 12 Authentication: Redirecting Unauthenticated Users](https://laravel.com/docs/12.x/authentication#redirecting-unauthenticated-users) and [Redirecting Authenticated Users](https://laravel.com/docs/12.x/authentication#redirecting-authenticated-users).

### Use the existing password recovery flow

**Decision**: Keep the shared password reset routes, broker, and views. Link both sign-in panels to the existing recovery entry point; preserve the completion redirect to the neutral `/login` chooser with its success status.

**Rationale**: Both panels use the same user accounts and credentials, so one recovery flow remains sufficient.

**Alternatives considered**: Separate reset brokers or duplicate reset pages are not needed for the existing shared account store.

**Source**: [Laravel 12 Password Reset: Routing](https://laravel.com/docs/12.x/passwords#routing), including “Requesting the Password Reset Link” and “Resetting the Password.”

### Use a deterministic shared dashboard destination

**Decision**: Successful sign-in goes to the current shared `/dashboard` unless the existing intended redirect is confirmed to be valid for the authenticated role. Prefer the shared dashboard as the safe fallback.

**Rationale**: The admin middleware protects administrator-only routes, and Laravel’s intended destination can reflect a URL reached before sign-in. Role eligibility remains an application concern even when Laravel redirects to an intended URL.

**Alternatives considered**: Always trusting the intended URL can send an employee back to an administrator-only page; adding separate dashboards is outside the existing feature scope.

**Source**: [Laravel 12 Authentication: Manually Authenticating Users](https://laravel.com/docs/12.x/authentication#authenticating-users) describes intended redirects; the role-safe destination behavior follows from the local route and middleware structure.

## Resolved Unknowns

- **Role source**: Existing `users.role` values `admin` and `karyawan`; no role is taken from the form.
- **Destination**: Existing shared `/dashboard`; no role-specific dashboard exists in the current code.
- **Persistence changes**: None.
- **Recovery flow**: Shared password reset broker and routes.
- **Login protection**: Keep existing rate limiting, failed-authentication response, and successful-session regeneration.
