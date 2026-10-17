# Data Model: Separate Role Login Panels

## Existing Entity: Account (`User`)

The feature uses existing user records and does not add or migrate persisted data.

| Field | Meaning | Feature Use | Validation / Rules |
|---|---|---|---|
| `name` | Account holder's display name | Existing account data | No change |
| `email` | Sign-in identifier | Submitted by either panel | Must satisfy existing email validation and match an account |
| `password` | Account secret | Submitted by either panel | Must satisfy existing credential verification |
| `role` | Account access classification | Determines which panel may authenticate the account | Existing supported values: `admin`, `karyawan`; the selected panel's allowed role is determined server-side |

### Relationships

- Each account has one role value in the existing `users` table.
- No separate admin or employee account entity is introduced.

### Authentication State

The authenticated user is held in the existing session guard after successful credential verification. A role mismatch, missing role, unrecognized role, invalid password, or invalid email must leave the request unauthenticated. The chosen panel is request context, not persisted account state.

### State Transitions

```text
Signed out
  ├─ matching credentials + matching panel role → signed in → shared dashboard
  └─ invalid credentials or role mismatch       → signed out → same panel with error
```

No migration or backfill is required.
