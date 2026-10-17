# Tasks: Separate Role Login Panels

**Input**: Design documents from `/specs/001-role-login-panels/`

**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/auth-ui.md`, `quickstart.md`

**Tests**: No test implementation tasks are included; the spec and quickstart define independent acceptance checks.

**Organization**: Tasks are grouped by user story after shared authentication prerequisites.

## Format: `- [ ] [ID] [P?] [Story?] Description`

- **[P]**: Can run in parallel because the task edits a separate file and has no incomplete dependency.
- **[Story]**: Maps a task to a user story in `spec.md`.
- Every task description names its target file.

## Phase 1: Setup

**Purpose**: No project setup is required. The existing Laravel 12, Breeze authentication scaffolding, user table, and Pest environment are reused; no dependency or schema change is planned.

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Add server-selected role scoping to the shared authentication path and expose the role-specific endpoints used by all three stories.

- [X] T001 Update `app/Http/Requests/Auth/LoginRequest.php` so `authenticate(string $expectedRole)` includes the expected role in the credential attempt; preserve the current email/IP throttling, validation, and failed-login response, and never read the allowed role from form input.
- [X] T002 Update `app/Http/Controllers/Auth/AuthenticatedSessionController.php` so `create()` maps server-side `loginRole` defaults `admin` and `karyawan` to `auth.login-admin` and `auth.login-karyawan` (or renders `auth.login` when absent), and `store()` passes that default to `LoginRequest::authenticate()`, regenerates the session only after success, and redirects to the existing dashboard.
- [X] T003 Update `routes/auth.php` with GET and POST endpoints for `/admin/login` and `/karyawan/login`, assigning server-side `loginRole` defaults of `admin` and `karyawan` and route names `login.admin`, `login.admin.store`, `login.karyawan`, and `login.karyawan.store`; keep GET `/login` named `login` as the neutral chooser and remove the unscoped credential POST route so it cannot bypass role checks.

**Checkpoint**: The shared authentication flow can only authenticate the role assigned by each server-side endpoint; the existing session guard, throttling, password reset routes, and dashboard remain in use.

## Phase 3: User Story 1 - Admin Login Panel (Priority: P1)

**Goal**: Give administrators a clearly identified sign-in panel that submits only to the administrator endpoint.

**Independent Test**: Open `/admin/login`; confirm the administrator label, submit valid `admin` credentials and reach `/dashboard`; submit a `karyawan` account and confirm sign-in fails with no authenticated session.

- [X] T004 [P] [US1] Create `resources/views/auth/login-admin.blade.php` with the administrator label, email and password fields, CSRF protection, errors and retry behavior, a form action of `route('login.admin.store')`, a link to `route('password.request')`, and a switch link to `route('login.karyawan')`.

**Checkpoint**: Administrators can use their own panel; an employee account cannot authenticate through it.

## Phase 4: User Story 2 - Employee Login Panel (Priority: P1)

**Goal**: Give employees a clearly identified sign-in panel that submits only to the employee endpoint.

**Independent Test**: Open `/karyawan/login`; confirm the employee label, submit valid `karyawan` credentials and reach `/dashboard`; submit an `admin` account and confirm sign-in fails with no authenticated session.

- [X] T005 [P] [US2] Create `resources/views/auth/login-karyawan.blade.php` with the employee label, email and password fields, CSRF protection, errors and retry behavior, a form action of `route('login.karyawan.store')`, a link to `route('password.request')`, and a switch link to `route('login.admin')`.

**Checkpoint**: Employees can use their own panel; an administrator account cannot authenticate through it.

## Phase 5: User Story 3 - Role-Specific Access (Priority: P1)

**Goal**: Let users choose the correct panel while ensuring role mismatches cannot create a session or alter an account role.

**Independent Test**: Open `/login` and follow each role link; submit mismatched, missing, or unrecognized roles through the corresponding panels and confirm the request remains signed out with a retryable error.

- [X] T006 [P] [US3] Replace the credential form in `resources/views/auth/login.blade.php` with a neutral chooser linking to `route('login.admin')` and `route('login.karyawan')`; retain the session status message so password-reset success remains visible and retain the existing registration link.

**Checkpoint**: `/login` no longer accepts credentials; each user can identify and reach either role-specific panel, and only the matching account role can sign in.

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: No additional polish task is required. The existing named `login` route remains the return point for root, registration, and password-reset links; authorization of protected application routes remains unchanged.

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No work required; the current application setup is reused.
- **Foundational (Phase 2)**: Complete T001, then T002 and T003 before implementing the role panels.
- **User Stories (Phases 3-5)**: All depend on the foundational routes and shared authentication flow.
- **Polish (Phase 6)**: No additional work beyond the story tasks.

### User Story Dependencies

- **US1 (P1)**: Depends on Phase 2; does not depend on another story.
- **US2 (P1)**: Depends on Phase 2; does not depend on another story.
- **US3 (P1)**: Depends on Phase 2 route names and both role-specific endpoint definitions; its chooser can be built independently of the two panel view files.

### Within Each User Story

- Complete the story's view task after foundational routes exist.
- The controller selects views and allowed roles from server-side route defaults; views must not send a role field.
- Cross-role denial is provided by the shared authentication task and exercised through each story's independent acceptance check.

### Parallel Opportunities

- After Phase 2, T004, T005, and T006 edit separate Blade files and can be implemented in parallel.
- T001, T002, and T003 are sequential because the controller depends on role-scoped authentication and the views depend on the named routes.

## Parallel Example: User Story Views

```text
Task: T004 Create resources/views/auth/login-admin.blade.php
Task: T005 Create resources/views/auth/login-karyawan.blade.php
Task: T006 Replace resources/views/auth/login.blade.php with the role chooser
```

## Implementation Strategy

### MVP First

The safe MVP includes the shared role-scoped authentication flow and all three P1 stories (T001-T006). Shipping only one role panel would leave the other role without a sign-in path or leave the old generic form as a role-check bypass.

### Incremental Delivery

1. Complete Phase 2 and confirm both scoped endpoints exist.
2. Build the admin panel and employee panel in parallel.
3. Replace the generic credential form with the chooser and confirm the shared registration and password-recovery paths still return to `/login`.

## Notes

- **Task count**: 6 total; US1: 1, US2: 1, US3: 1, foundational: 3.
- **Independent acceptance checks**: Listed at the start of each user story phase.
- **Test code**: Not included as a task; use acceptance checks and `quickstart.md` when implementation verification is requested.
- **MVP scope**: T001-T006, since every user story is P1 and all are needed for a complete two-role sign-in experience.
- `[P]` tasks only edit separate Blade files after shared route/controller work is complete.
