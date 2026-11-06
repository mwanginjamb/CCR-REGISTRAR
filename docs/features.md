# KEMRI Cancer Registry: Feature Overview

Pre-launch report · Version 0.1.0 · 6 November 2026

## 1. Purpose

The KEMRI Cancer Registry application is a web system for recording cancer cases. Registrars abstract each case from hospital, laboratory and hospice records into a structured form. Quality assurance (QA) staff check each case, and administrators maintain the reference data and user access.

The application follows the registry's REDCap data dictionary and the international ICD-O-3 coding standard for cancer site and tissue type. Its data therefore lines up with the existing REDCap project and is comparable with other registries.

## 2. Who uses it

The application has three roles. Each role includes everything the role before it can do.

| Role | What they can do |
|---|---|
| **Registrar** | Register new cases; add and correct tumour, treatment, source and follow-up details; respond to QA by correcting and resubmitting cases they registered |
| **Quality Assurance (QA)** | Everything a registrar can do, plus: edit patient details, flag cases for correction, close reviews, delete records (records are hidden, never erased), view the full registry and the Overview page |
| **Administrator** | Everything QA can do, plus: maintain coding reference data, manage user accounts, roles and permissions |

**Starting page by role**
- **Registrars** land on the new-case form after logging in.
- **QA and administrators** land on the Overview page.

## 3. Case registration

A single guided form captures a full case, section by section, with a step navigator on the side.

| Section | Captured |
|---|---|
| **Patient** | Name, national ID or passport, next-of-kin phone, date of birth (age is calculated automatically), place of birth, ethnic group, religion |
| **Tumour** | Incidence date, basis of diagnosis, primary site, sub-site, laterality, histology, behaviour, grade, stage, and TNM staging (full TNM or the essential TNM fields) |
| **Treatment** | One or more treatments (surgery, chemotherapy, immunotherapy, radiation, other), each with status and date, plus concurrent illness |
| **Sources** | One or more information sources (hospital, laboratory, hospice), each with source number, the patient's file number at that source, and date |
| **Follow-up** | Present status (alive or dead), cause of death, last date of contact, remarks |

**Built-in help for registrars**
- **Coded fields** show both the code and its meaning, for example "3 - Malignant" or "C50.9 - Breast, NOS". This matches the paper forms and REDCap.
- **Primary site** can be typed by code or by name (for example "C50" or "breast"). The site name appears as soon as a valid code is entered.
- **Sub-site ("C")** suggestions follow the chosen primary site. For example, C50.9 (breast) offers C50.0 to C50.8.
- **Histology** lists the morphologies most common for the chosen site first.
- **Laterality** only offers sides when the site is a paired organ; otherwise it is set to "Not Applicable".
- **Behaviour** fills in automatically from the chosen histology, where the coding reference defines one.
- **Source numbers** are suggested from the list of known hospitals and laboratories (101 hospitals are pre-loaded from REDCap). The source name is shown as it is typed.
- **Registration location** is recorded automatically from the device's GPS, with its accuracy, and shown on a map on the case page.

## 4. Data quality controls

**Checks when a case is saved**
- **Required fields:** missing required information is rejected. Every problem is listed in one summary, grouped by form section, and the affected fields are highlighted.
- **Coding:** only valid codes from the REDCap dictionary and the ICD-O-3 reference lists are accepted.
- **Dates:** incidence dates in the future are rejected.
- **Laterality:** must be consistent with the primary site.
- **Sub-site:** must belong to the chosen primary site.
- **Grade:** is only accepted where the tumour's behaviour allows it.
- **TNM:** full TNM and the essential TNM fields cannot both be used.

**Automatic review flag.** An unusual combination, such as a histology not normally seen at the chosen site, does not block the save. The case is saved and flagged for QA review, and the reason is shown on the case.

## 5. Quality assurance review

QA can send any case back to the registrar who recorded it.

1. **Flag:** the QA reviewer selects a reason (incorrect coding, missing data, inconsistent dates, incomplete sources, possible duplicate, other) and writes a note.
2. **Email notification:** the registrar who registered the case is emailed immediately. The email names the QA reviewer, quotes the note and links to the case. Replies go directly to the QA reviewer.
3. **Correct and resubmit:** the registrar sees the note on the case and in their case list. They correct the record and resubmit it with an optional note.
4. **Close:** QA marks the case reviewed, with an optional resolution note.

**Review history.** Every flag, resubmission and resolution is kept on the case: who acted, when, and what they wrote. If an email could not be delivered, the history records why.

**Patient privacy in emails.** Emails identify cases by case number only (for example PT-00003). Patient names and clinical details stay inside the application, behind the login.

## 6. Case registry and case pages

**Registry list**
- **Search and sort:** QA and administrators can search and sort all cases.
- **Export:** cases can be copied or exported to CSV, Excel or PDF, or printed.
- **Case numbers** link straight to each case page.
- **Pending review:** a counter shows how many cases are flagged for review.
- **Registrars' view:** registrars see a focused list: the cases they registered that are currently flagged for review.

**Case page**
- **Full case summary:** patient details, tumour details with code descriptions, staging, treatments, sources, follow-up, the registration map and the record's history.
- **Review banner:** shows any active review note and the actions available to the user's role.

**Deleted records** are hidden from the registry but kept in the database, so they can be recovered and audited.

## 7. Working offline

The application is designed for fieldwork with unreliable connectivity.

- **Installable:** it can be installed on a phone, tablet or laptop like an app, and opens straight to the new-case form.
- **Offline saving:** when the connection drops, cases are saved on the device. The form shows that it is in offline mode, and a counter shows how many cases are waiting to upload.
- **Automatic upload:** saved cases upload automatically when the connection returns, with automatic retries. Failed uploads are kept on the device for another attempt.
- **Offline suggestions:** suggestions such as source numbers keep working offline.

## 8. Reference data management (administrators)

Administrators can maintain the coding reference lists in the application, without technical support.

| Area | Purpose |
|---|---|
| **ICD-O topography** | Primary site and sub-site codes (331 codes) |
| **ICD-O morphology** | Histology codes and their default behaviour (637 codes) |
| **Site rules** | Whether laterality applies to, or is required for, each site (70 site groups) |
| **Site–morphology rules** | Which histologies are common or accepted for each site (257 rules) |
| **Sources** | Hospitals, laboratories and hospices offered as source suggestions (101 pre-loaded) |

**How changes work**
- **Immediate effect:** changes apply straight away to the case form and its checks.
- **Deactivate, don't delete:** codes still used by past cases are deactivated rather than deleted, so historical records stay intact.

**REDCap dictionary import.** The registry's REDCap data dictionary can be re-imported to refresh the coded answer lists. This keeps the application aligned with the REDCap project.

## 9. Accounts and access management

**For all users**
- **Self-registration:** users sign up and confirm their email address before they can log in.
- **Password reset:** done by email, with password confirmation.
- **New accounts** start as registrars until an administrator assigns another role.
- **Header display:** each user's name and roles appear at the top of every page.

**For administrators**
- **User role assignment:** promote or change a user's roles, for example registrar to QA.
- **Roles and permissions:** view and adjust what each role can do. Built-in roles and permissions are protected from accidental renaming or deletion.
- **System users:** see all accounts and their status, and activate or deactivate them. A deactivated user can no longer log in.
- **Lockout protection:** administrators cannot remove their own administrator role or deactivate their own account.

## 10. Security and privacy measures

- **Login required:** every page with patient data requires a login, and every action is checked against the user's role.
- **Hidden actions:** users only see the actions their role allows.
- **Records are never erased:** deleting a record hides it but keeps it in the database. Each record stores who created it and who last changed it.
- **No patient identifiers in emails:** notification emails use case numbers only.
- **Forms protected against forgery:** forms and actions are protected against cross-site request forgery.
- **Connection security:** all traffic is encrypted (HTTPS), and the production server sends standard browser security protections.
- **Separate configuration:** database and email credentials are kept out of the application code.

## 11. Pre-launch status

The features above are built, and the QA review workflow has been tested end to end. Before real patient data is entered, the team is closing a short list of launch items:

- **Secure the offline upload connection:** require a logged-in user.
- **Decide case visibility:** whether registrars should see only the cases they registered.
- **Overview dashboard:** connect it to live statistics. It currently shows placeholder figures.
- **Production settings and credentials:** finalise them.
- **Production database:** prepare it and remove test data.
- **Testing:** a hands-on test of every role's workflow, including email delivery.

Once these are done, a supervised pilot with a small group of registrars, one QA reviewer and one administrator is recommended before a wider rollout.
