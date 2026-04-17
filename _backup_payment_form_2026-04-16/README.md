# Backup — Custom Payment Form Design (2026-04-16)

## Why this exists

On the call with Kader on **2026-04-16**, we decided to migrate the payment flow to **Up2Pay Hosted iFrame mode**. The current custom payment form in `app/stages-recuperation-points/[slug]/[id]/inscription/page.tsx` was the result of a lot of design work (2-step form with Step 1 "Coordonnées" + Step 2 custom CB block). Kader asked us to **keep this design safe** so we can revert to it if the iFrame approach doesn't fit our needs later.

## What's backed up here

- `inscription-page.tsx` — the FULL 4,504-line inscription page file as of commit `938fa0c` (16 April 2026)

This file contains :
- The 2-step form UX
- The custom CB input fields (card number, expiry, CVV, holder name)
- The mobile-optimized design (grey widget for stage date, benefits box, Étape 1/2 headers)
- All the dynamic payment block show/hide logic
- The "Modifier" flow after form validation

## How to restore this design

### Option 1 — via git tag (cleanest)

```bash
# From the project root
git checkout payment-form-custom-backup-2026-04-16 -- app/stages-recuperation-points/[slug]/[id]/inscription/

# If you also want to restore the departement/region inscription pages
git checkout payment-form-custom-backup-2026-04-16 -- app/stages-recuperation-points/
```

Then `git commit` with a message like "revert: restore custom payment form".

### Option 2 — via this folder (manual)

```bash
cp _backup_payment_form_2026-04-16/inscription-page.tsx \
   app/stages-recuperation-points/[slug]/[id]/inscription/page.tsx
```

### Option 3 — read-only inspection

Just open `inscription-page.tsx` here to inspect the original code without restoring.

## Git tag

A permanent git tag **`payment-form-custom-backup-2026-04-16`** exists at commit `938fa0c`. It will survive any future branch deletions, force-pushes, etc. Even if this folder gets deleted, the code can be recovered from the tag forever.

## When to consider restoring

- iFrame UX feels too limiting (can't customize enough)
- Up2Pay iFrame has bugs on some browsers
- PCI-DSS requirements change and the hybrid approach becomes OK
- Any reason Kader or Yakeen decides

## When NOT to restore

- Just because the iFrame "looks different" at first — give it 1 week in production before judging
- Before testing iFrame customization fully (logo, colors, CSS)

---

**Do not edit anything in this folder.** It's a reference. If you want to change the design, work on the live files in `app/` and commit normally. The backup stays frozen.
