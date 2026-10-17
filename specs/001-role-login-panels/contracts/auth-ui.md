# Authentication UI Contract

This contract describes the user-facing routes and required behavior for the two role-specific sign-in panels.

| Method | Path | Purpose | Required behavior |
|---|---|---|---|
| GET | `/login` | Neutral sign-in chooser; keeps the existing named `login` route | Shows links to administrator and employee sign-in panels; accepts no credentials |
| GET | `/admin/login` | Administrator sign-in panel | Clearly labels the administrator role and links to the employee panel and password recovery |
| POST | `/admin/login` | Submit administrator credentials | Authenticates only an account with role `admin`; a mismatch fails without creating a session |
| GET | `/karyawan/login` | Employee sign-in panel | Clearly labels the employee role and links to the administrator panel and password recovery |
| POST | `/karyawan/login` | Submit employee credentials | Authenticates only an account with role `karyawan`; a mismatch fails without creating a session |
| GET/POST | Existing password recovery routes | Shared password reset experience | Remains accessible from both panels and returns successful resets to `/login` |

## Response Behavior

- A valid account using its matching role panel signs in and reaches the existing `/dashboard`.
- Invalid credentials, an account from the other role, or an absent/unrecognized role produce the existing user-safe failed sign-in response and leave the user signed out.
- Existing throttling applies across both panels so changing panels does not reset failed-attempt protection.
- A signed-in user opening a role panel remains signed in as the same account; opening a panel never changes a role.
- The `/login` route remains available to existing links and guest redirects.

## Scope Boundaries

- No new account registration process, role assignment flow, session guard, database schema, or dashboard is introduced.
- Existing route authorization, including the `admin` middleware, remains the authority for protected application areas.
