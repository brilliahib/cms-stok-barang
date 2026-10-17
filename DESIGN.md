# Design System

This document records the existing Laravel Breeze guest and authentication style. The role-specific login panels reuse that system without introducing a new visual direction or component layer.

## 1. Atmosphere & Identity

- **Product**: SHR Jaya Motor inventory application.
- **Atmosphere**: Familiar, legible business software with a photographic sign-in background and a centered white form surface.
- **Signature treatment**: Existing dark photographic background, translucent black overlay, white brand mark, and white card.
- **Responsive intent**: Center the sign-in content on narrow screens; constrain it to the existing medium form width on larger screens. Keep the page vertically scrollable when content grows.

## 2. Color

| Role | Existing token | Usage |
|---|---|---|
| Form surface | `bg-white` | Guest form card |
| Primary text | `text-gray-900` | Body and headings |
| Secondary text | `text-gray-600`, `text-gray-300` | Supporting copy and brand subtitle |
| Field label | `text-gray-700` | Form labels |
| Field border | `border-gray-300` | Input boundary |
| Focus | `indigo-500` | Input and keyboard focus rings |
| Primary action | `gray-800`, hover `gray-700`, active `gray-900` | Submit button |
| Error | `red-600` | Validation feedback |
| Success | `green-600` | Session status |
| Background overlay | black at 50% opacity | Contrast behind the sign-in card |

Use these existing Tailwind semantic utility classes. Do not introduce raw color values in the new views.

## 3. Typography

- **Family**: Figtree (400, 500, 600), loaded by the existing guest layout; use the existing sans-serif fallback.
- **Body**: Existing Tailwind `text-sm` and default body sizing.
- **Form labels**: `text-sm font-medium`.
- **Page heading**: `text-lg font-semibold`.
- **Action label**: Existing primary-button `text-xs font-semibold uppercase tracking-widest`.

## 4. Spacing & Layout

- Use existing Tailwind spacing utilities and the current Breeze form rhythm (`mt-1`, `mt-4`, `px-6`, `py-4`).
- Use the guest layout's `min-h-screen`, centered flex column, and `sm:max-w-md` card width.
- Keep each form in one readable column and let links wrap on narrow viewports.
- Use intrinsic sizing and existing responsive utilities; do not add fixed pixel widths.

## 5. Components

### Guest authentication surface
- **Structure**: `x-guest-layout` containing status, title, form content, and navigation links.
- **Variants**: Neutral role chooser, administrator sign-in, employee sign-in.
- **Spacing**: Existing card and Breeze spacing utilities.
- **States**: Default, validation error, session status, keyboard focus.
- **Accessibility**: One page heading; labeled email/password inputs; semantic links and submit buttons; visible focus rings.
- **Motion**: Existing primary button transition only.
- **Layout**: Centered, single-column form inside the guest layout.

### Existing form primitives
- **Structure**: `x-input-label`, `x-text-input`, `x-input-error`, `x-primary-button`, and `x-auth-session-status`.
- **Variants**: Use current component defaults; do not add visual variants for the role panels.
- **Spacing**: Caller uses the existing Tailwind spacing utilities.
- **States**: Default, focused, disabled where supported, field error, and session status.
- **Accessibility**: Preserve each field's `id`/label relationship, autocomplete hints, validation feedback, and focus indication.
- **Motion**: Existing primary-button transition only.
- **Layout**: Form fields in a vertical stack.

Do not create another shared component for the two forms unless implementation reveals genuinely repeated structure beyond the current Blade components.

## 6. Motion & Interaction

- Keep the current button's 150ms ease-in-out transition.
- Provide hover, active, and focus feedback using the existing component styles.
- Add no animation or motion to the role chooser or form.

## 7. Depth & Surface

- **Strategy**: Existing shadow treatment.
- The guest form card uses `shadow-md` on the white surface.
- The background overlay provides contrast; do not add new borders, shadows, or decorative surfaces to the role panels.

## 8. Accessibility Constraints & Accepted Debt

### Constraints

- Keep visible labels, keyboard-operable links and controls, and visible focus rings.
- Use clear role-specific page headings and action labels.
- Keep validation errors adjacent to the corresponding fields.
- No new motion is introduced.

### Accepted Debt

| Item | Location | Why accepted | Owner / Exit |
|---|---|---|---|
| Guest background is loaded from an external Unsplash URL | `resources/views/layouts/guest.blade.php` | Existing sign-in visual; replacing its asset is outside this feature | Revisit with a separately scoped asset/performance change |
