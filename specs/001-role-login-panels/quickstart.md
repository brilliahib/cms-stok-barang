# Quickstart: Separate Role Login Panels

This guide is for validating the feature after implementation.

## Prerequisites

- PHP and Composer dependencies are installed.
- The application database is migrated and has at least one `admin` account and one `karyawan` account with known credentials.
- The Laravel development environment can serve Blade views and accept web sessions.

## Run the Application

```bash
php artisan serve
```

Open `/login`. It should present the two role choices. Open `/admin/login` and `/karyawan/login` to confirm each panel is clearly labeled and provides links to the other panel and password recovery.

## Validate Sign-In Behavior

1. Submit valid `admin` credentials at `/admin/login`; confirm the user is authenticated and sent to `/dashboard`.
2. Submit valid `karyawan` credentials at `/karyawan/login`; confirm the user is authenticated and sent to `/dashboard`.
3. Submit each account at the other role's panel; confirm sign-in fails and the user remains signed out.
4. Submit an invalid password at each panel; confirm a clear error appears and the same panel remains available for retry.
5. Submit repeated failures while switching panels; confirm the existing login throttling still applies.
6. Open each panel's password recovery link; complete a reset and confirm the success message appears on `/login`.
7. While signed in, open the other role's login path; confirm the existing identity and role remain unchanged.
8. Open an administrator-protected route as an employee; confirm the existing authorization still denies access.

## Automated Checks

Run the focused authentication feature tests:

```bash
php artisan test tests/Feature/Auth/AuthenticationTest.php
php artisan test tests/Feature/Auth/PasswordResetTest.php
```

Then run the full suite:

```bash
php artisan test
```

Expected outcomes: both matching-role logins succeed, cross-role and invalid attempts remain guests, login panels and links render, the shared reset flow remains functional, and the existing role authorization remains in force.
