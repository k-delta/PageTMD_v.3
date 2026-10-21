# Duración estimada y estilo del formulario de alquiler Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add the optional rental-duration selector, pass the selected value to the sales email, and apply the requested visual card treatment to the rental form.

**Architecture:** Keep the existing quote-section markup and Contact Form 7 markup in place, style the `.tmd-rental-v2__quote` section as the card in the rental-specific stylesheet, and map the duration tag in the existing target mail body. Preserve current page copy, fields, privacy notice text and destination, and mail properties; apply the muted privacy styling shown in the reference. Extend the current renderer, recipient-preservation, and updater tests; use the guarded updater in form-only mode so it aborts unless the page title/content and Rank Math already match the targets, and never writes those values.

**Tech Stack:** PHP, WordPress, Contact Form 7, WP-CLI, existing PHP test scripts.

**Spec:** `docs/specs/2026-10-20-duracion-alquiler-cotizacion.md` (approved)

## Global Constraints

- The form CF7 1556 shows an optional dropdown with the label “Duración estimada del alquiler”.
- The dropdown offers exactly: “Mínimo 15 días”, “1 mes”, “3 meses”, “6 meses”, “1 año” and “Más de 1 año”.
- The user can submit the form without selecting a duration.
- If an option is selected, its text appears in the request email under “Duración estimada del alquiler”. If left empty, the submission is not rejected.
- Preserve the five existing controls, their required states, privacy note, honeypot and anti-spam protections.
- The minimum rental period is 15 days; do not offer hours or periods shorter than 15 days.
- Do not change the current recipient, mail headers, page copy, Rank Math metadata, or other forms.
- Production persistence requires a verified full database backup and a private snapshot of page 1558 and form 1556.

## Review Focus

- Empty selection: the optional field remains submittable and the mail tag resolves without rejecting the request. Test: `test_rental_duration_select_is_optional` in Task 1.
- Exact values and accents: the selector exposes only the six approved labels. Test: `test_rental_duration_select_has_exact_choices` in Task 1.
- Selected value mapping: the sales email includes the named duration tag. Test: `test_target_mail_body_maps_rental_duration` in Task 1.
- Existing required controls and spam/privacy protections: adding the selector does not weaken or remove them. Test: `test_rental_form_preserves_existing_controls_and_protections` in Task 1.
- Mail routing: recipient, subject, reply-to, secondary mail, messages and additional settings remain unchanged. Test: existing preservation assertions in `tests/test-commercial-landing-recipient.php`, extended in Task 1.
- Field presentation: the dropdown uses the same dark background, border, radius, typography and sizing as the other fields in the rental form. Test: rental CSS assertion in `tests/test-commercial-landing-rental-v2-render.php`, extended in Task 1.
- Visual presentation: the form wrapper reads as a rounded, subtly bordered navy card; fields use dark surfaces and legible text; the yellow CTA remains prominent. Review manually at desktop and mobile widths in Task 1.
- Content boundary: preserve current copy and controls. Do not add the two-hour response guarantee or a consent checkbox.

---

### Task 1: Add the selector, mail mapping, and card presentation

**Files:**
- Modify: `scripts/commercial-landing-rental-v2.php:139-159`
- Modify: `scripts/create-commercial-landing-pages.php:1361-1365`
- Modify: `scripts/update-rental-landing-v2.php:33-43`
- Modify: `wp-content/themes/blocksy-child/assets/css/tmd-commercial-landing-rental-v2.css:924-1045`
- Modify: `tests/test-commercial-landing-rental-v2-render.php`
- Modify: `tests/test-commercial-landing-rental-v2-update.php`
- Modify: `tests/test-commercial-landing-recipient.php`

**Interfaces:**
- Consumes: `tmd_commercial_landing_rental_v2_form_markup(): string` and `tmd_commercial_landing_rental_v2_target_form_properties(array $current): array`.
- Produces: the optional CF7 tag `[select duracion_alquiler include_blank "Mínimo 15 días" "1 mes" "3 meses" "6 meses" "1 año" "Más de 1 año"]`; the mail body includes `Duración estimada del alquiler: [duracion_alquiler]`.

- [ ] **Step 1: Write the failing assertions.** Extend the renderer test to assert the exact optional tag, label, six choices, unchanged five existing fields and required states, and that the rental CSS field rule includes `select` but excludes the submit input. Extend the recipient test to assert the duration mail mapping and preservation of all current mail properties. Extend the updater test to assert that the target form persisted through the guarded update includes the selector and mail tag, and that form-only mode rejects page/metadata differences in both dry-run and execute mode and writes only CF7.
- [ ] **Step 2: Run the focused tests and confirm the new assertions fail.**

  Run: `php tests/test-commercial-landing-rental-v2-render.php && php tests/test-commercial-landing-recipient.php && php tests/test-commercial-landing-rental-v2-update.php`

  Expected: failure on the missing duration selector/mail mapping; no unrelated baseline failure.
- [ ] **Step 3: Add the optional selector and mail mapping.** Add the select in `tmd_commercial_landing_rental_v2_form_markup()` with the exact name, options, blank choice, and label from the SPEC; map its tag in the target mail body. Keep the other five fields and all current mail properties.
- [ ] **Step 4: Apply the reference presentation to the form.** Style the existing `.tmd-rental-v2__quote` section as a padded navy card with a subtle border and rounded corners, keeping its current heading, subtitle, form markup, and page content intact. Use dark surfaces, borders, text, and placeholders for the scoped controls; include `select` in the field rules and exclude the submit input from generic field styling. Preserve the current CTA text, copy, control order, required states, privacy notice text and destination, honeypot, and spam protections. Style the privacy notice in the muted gray-blue treatment shown in the reference. Do not add the two-hour guarantee or an authorization checkbox.
- [ ] **Step 5: Run focused validation and visual review.**

  Run: `php -l scripts/commercial-landing-rental-v2.php && php -l scripts/update-rental-landing-v2.php && php -l tests/test-commercial-landing-rental-v2-render.php && php -l tests/test-commercial-landing-rental-v2-update.php && php -l tests/test-commercial-landing-recipient.php && php tests/test-commercial-landing-rental-v2-render.php && php tests/test-commercial-landing-recipient.php && php tests/test-commercial-landing-rental-v2-update.php && git diff --check`

  Expected: all PHP lint checks are clean, each focused test passes, and `git diff --check` reports no whitespace errors. Inspect the CSS against the reference and check desktop/mobile rendering, readable contrast, keyboard focus, and overflow.

### Task 2: Persist the form update on the requested page

**Files:**
- Production content: WordPress page 1558 and Contact Form 7 form 1556.
- Runner: `scripts/update-rental-landing-v2.php` from the authorized repository release.

**Interfaces:**
- Consumes: the renderer and mail mapping from Task 1; the existing guarded updater's source/target hashes, backup validator, private rollback artifact, and transaction.
- Produces: form 1556 stores the optional selector and mail tag; page content and Rank Math metadata remain byte-for-byte at their current values.

- [ ] **Step 1: Prepare the release with `safe-deploy`.** Run the production synchronization check. If it reports drift, stop and compare before continuing. Release only the approved changed files through the repository's deployment path.
- [ ] **Step 2: Read the current page, form, and Rank Math state; create and verify the full MariaDB backup and private rollback snapshot.** Do not write while any source hash or backup check is missing or mismatched.
- [ ] **Step 3: Run the guarded updater with `TMD_RENTAL_V2_FORM_ONLY=yes` in dry-run mode.** Confirm that the page title/content and Rank Math source/target hashes are identical, that only form 1556 differs, and that no write or transaction occurs. The form-only mode must reject any page or metadata difference.
- [ ] **Step 4: Execute the guarded update for form 1556 with `TMD_RENTAL_V2_FORM_ONLY=yes`, the verified backup, and existing commercial/contrast gates.** The runner must verify persistence and commit its transaction without writing page 1558 or Rank Math; otherwise preserve the rollback artifact and stop.
- [ ] **Step 5: Verify production.** Read the saved CF7 markup and mail body back; check that the public URL returns HTTP 200 and renders the label/options; submit synthetic values with the selector both empty and selected, then verify each result in the configured form record/mail evidence without using personal data.

