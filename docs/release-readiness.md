# Release Readiness

Status of the cancer registry app for its first release to registrars, QA and admin users.

- **Assessed:** 2026-11-06, on branch `feat/qa-review` (tag `v0.1.0`)
- **Updated:** 2026-11-06, on branch `feat/review-notifications` (see *Resolved since v0.1.0*)
- **Verdict:** not ready for real patient data yet. Fix the blockers, clean up production, then run a supervised pilot with a few registrars, one QA user and one admin before a wider rollout.

## Blockers

Fix these before real registrars enter real patient data. Numbers are kept from the original assessment; blocker 3 is resolved (see *Resolved since v0.1.0*).

### 1. The offline-sync API accepts records from anyone

`PatientApiController` (`/patient-api/create`) has no access control. Anyone who can reach the server can create patient records, bypassing login and every role check.

- **Fix:** require a logged-in user with `createPatient`.
- **Decide first:** what happens to records queued offline when the session has expired. For example, keep them queued and ask the user to log in again before syncing.

### 2. Registrars can open and edit any patient by URL

The registrar's patient list only shows their own flagged cases, but `patient/view` and `patient/update` aren't restricted. A registrar can open any patient by typing its ID.

- **Decide:** should registrars only see and edit cases they created?
- **Note:** after creating a case, a registrar is sent to its view page, so any restriction must still allow their own cases.

### 4. Debug mode is on in the code

`web/index.php` sets `YII_DEBUG = true` and `YII_ENV = 'dev'`, which also enables the Gii and debug modules. Production's 404 page looks normal and sends no debug headers, but this hasn't been confirmed for a server error.

- **Fix:** production must run with `YII_DEBUG = false` and `YII_ENV = 'prod'`, ideally set from `.env`.

### 5. Secrets are in Git history

- `cookieValidationKey` is committed in `config/web.php`.
- The old database and SMTP passwords are in earlier commits, from before `.env` was untracked and `config/db.php` read from it.
- **Fix:** move `cookieValidationKey` into `.env`, then change the key, the database password and the SMTP password.

### 6. Production isn't set up for this version

- Merge and push the release: `feat/qa-review` → `develop` → `master`. None of it is pushed yet.
- On the production server, run in order:
  1. `php yii migrate --migrationPath=@yii/rbac/migrations`
  2. `php yii migrate`
  3. `php yii user/assign-role <username> admin`
- Remove the fake test records from the production database (e.g. "Mrs. Evelyn Shields V" with "sui pecto vapulus" fields), or start from a clean database.

### 7. Not tested in a browser

- So far the controllers and views have only been run from the command line, inside transactions that were rolled back. Nobody has clicked through the screens.
- The automated test suite is the Yii template's and is broken: it expects the old hardcoded `admin`/`demo` users.
- The QA review emails have never been sent through the real SMTP server.
- **Fix:** click through each role's workflow (registrar, QA, admin), send one real QA email, and update or replace the template tests.

## Fix soon

These are fine for a supervised pilot, but not for a full release.

- **Template pages:** the About and Contact pages are still the Yii template's.
- **No audit trail:** records store who created and last updated them, but not who viewed or changed what. A cancer registry usually needs this for data protection.
- **Offline sync and QA warnings:** the sync API returns save warnings (for example, an unrecognised site/histology combination), but the offline sync script ignores them.
- **REDCap mismatches:**
  - The essential TNM fields, treatment types and source types are still hardcoded; the dictionary models them differently.
  - Surgery's "Unknown" is coded `99`, while the other treatment status fields use `9`.
  - Both will matter for a REDCap export.
- **Backups and monitoring:** no backup or monitoring setup is in place for the production database.
- **Possible QA follow-ups:**
  - email QA when a registrar resubmits a case;
  - email the registrar when a case is resolved.

## Resolved since v0.1.0

### Blocker 3: the Overview dashboard showed made-up numbers

Resolved on `feat/dashboard`.

- **Before:** `views/site/index.php` had hardcoded figures (12,842 patients, 92.4%, …).
- **Now:** a new `app\components\Dashboard` calculates every figure from the database, excluding deleted patients:
  - totals and new cases this month;
  - review workload;
  - data completeness;
  - the IARC MV% and DCO% indicators;
  - cases by site, stage and basis of diagnosis;
  - the most active registrars.
- **Tested** against seeded data, with every figure matching the hand-computed values, and against sparse data, where the empty states show correctly.

### Fix soon: decorative header controls

Resolved on `feat/review-notifications`.

- **Removed:** the search box and the help icon (the settings gear in the patient-form layout), which did nothing.
- **Notification bell:** now a review inbox, with a red bubble counting the cases waiting on the user. The count comes from a new `app\components\ReviewInbox`:

  | Role | Counts |
  |---|---|
  | Registrar | their own flagged cases waiting on them (not ones already resubmitted to QA) |
  | QA | cases resubmitted by registrars, plus system-flagged cases with no QA review yet |
  | Admin | every flagged case |

- **Clicking the bell** opens the patient list filtered to exactly those cases, using the same rule as the count, with a "Show all patients" link.
- **Tested** with a scenario covering each case type: the bell count equals the filtered list for every role.

## In good shape

- **Patient data entry:** validated against the ICD-O and REDCap dictionaries.
  - Typeable primary site and "C" sub-site fields.
  - Source number suggestions that also work offline.
- **Roles:** registrar, QA and admin are enforced on every patient action. Patients are soft-deleted, not removed.
- **QA review workflow:** flag, email the registrar, resubmit, resolve, with history on each case.
- **Review notifications:** a header bell with a count of the review items waiting on each user.
- **Overview dashboard:** live registry figures and IARC quality indicators for QA and admins.
- **Installable app:** installable from any page, with correct icons, favicon and an offline fallback page.
- **Admin screens:** Settings for ICD-O codes, site rules, site–morphology rules and sources; RBAC screens for roles, permissions, user roles and system users.
- **Accounts:** login, signup, email verification and password reset, all with consistent styling.
