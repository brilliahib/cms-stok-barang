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

## 9. Admin Application Surface

The authenticated inventory pages use the shared Blade app layout and Bootstrap 5.3.3. New admin pages, including Kasir, follow these existing primitives instead of creating a second design language.

### Tokens

| Role | Existing token or value | Usage |
|---|---|---|
| Page background | Existing `#f4f7f6` from `resources/views/layouts/app.blade.php` | Admin content canvas |
| Main surface | Bootstrap `bg-white` | Page header and cards |
| Primary text | Bootstrap `text-body` / default body color | Labels and content |
| Secondary text | Bootstrap `text-secondary` | Supporting copy and metadata |
| Borders | Bootstrap default component borders | Inputs, tables, and card edges |
| Primary action | Bootstrap `btn-primary` | Main actions |
| Success action | Bootstrap `btn-success` | Checkout and export completion actions |
| Destructive action | Bootstrap `btn-outline-danger` or `btn-danger` | Remove a cart line |
| Error state | Bootstrap `is-invalid`, `invalid-feedback`, `alert-danger` | Field and request errors |
| Success state | Bootstrap `alert-success` | Completed transaction feedback |
| Print ink | `#212529` | Standalone receipt and history PDFs |
| Print separator | `#dee2e6` | Standalone PDF table rules |

Use Bootstrap classes and existing layout styles. Do not add raw colors, arbitrary font sizes, or one-off shadows to feature views.

### Admin primitives and states

- **Application shell**: `x-app-layout`, fixed-width dark sidebar, flexible content, rounded white header. The shared layout owns the page background and Bootstrap assets.
- **Card**: Bootstrap `.card`, `.card-header.bg-white`, `.card-body`, and existing `shadow-sm` treatment for grouped page content.
- **Form field**: `.form-label`, `.form-control` or `.form-select`, `.is-invalid`, adjacent `.invalid-feedback`, and visible labels tied to input IDs.
- **Action**: semantic `<button>` for state changes and `<a>` for navigation, using Bootstrap button variants and visible keyboard focus.
- **Responsive table**: `.table-responsive` around `.table`; on narrow screens allow horizontal table scrolling rather than shrinking text or hiding required data.
- **Status message**: `.alert` with success or danger variant; pair it with field-level feedback where an input caused the error.
- **Cashier catalog result**: a white Bootstrap card with product name, current stock, and a clearly labeled add button. Disabled when stock is zero.
- **Cashier cart line**: labeled quantity and unit-price controls, line amount, and a semantic remove button. Custom lines include a required description.
- **Cashier total panel**: card grouping subtotal, cash received, change, and the primary checkout button; stack below the cart on narrow screens.

### Admin interaction and responsive behavior

- Keep existing Bootstrap focus indicators and native form validation behavior; provide explicit `aria-live="polite"` for changing totals and cart status.
- Product search remains a labeled GET form. Adding/removing cart lines and recalculating totals must work by keyboard as well as pointer.
- Use Bootstrap grid breakpoints so catalog and cart are side-by-side on wide screens and stack on narrow screens. Preserve all product, quantity, price, and payment controls at every width.
- Do not introduce decorative motion. State changes use immediate text and Bootstrap's existing hover/focus feedback.
