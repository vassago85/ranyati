# Ranyati admin: motivations UX overhaul

Put the PNGs from this pack in `docs/mockups/` and attach them to the chat. The yellow numbered dots on each image match the numbered notes below. The mockups show layout and behaviour. They are not pixel specs, and all names and figures in them are sample data.

## Context

The admin at motivations.ranyati.co.za now tracks firearm licence motivations from intake to completion. The UI hasn't caught up. The dashboard covers enquiries only. Motivation requests can't be moved forward from their detail page. Configuration is split across overlapping screens, and the same person can exist as an enquiry contact, a walk-in member and a SAPS tracker client.

Observed stack: Laravel with Blade, Alpine.js and hand-written CSS (`btn`, `btn-primary`, `btn-secondary`, `btn-sm`, `card`, `card-header`, `card-body`, `badge`, `badge-orange`, `form-input`, `sidebar-link`, `topbar`). There's no Livewire, Tailwind or Filament on these pages. Pages carry about 44 inline `style=""` attributes each. Destructive actions use `onsubmit="return confirm(...)"`.

Before writing code, read the routes, controllers, models, migrations and Blade views for: motivation requests, motivation members, motivation templates, application types, questionnaires, documents, enquiries and the SAPS applications tracker. Report the actual model names, the status column and its values, and the relationships. Wait for my go-ahead before Phase 3.

Rules:

- Target the latest stable Laravel and PHP.
- Keep the current visual language: dark navy surfaces, orange `#f58220` accent, Inter, 12px card radius, 8px button radius.
- Pull repeated inline styles into the stylesheet as you touch each view.
- Feature test every new route and every status transition.
- Each phase ships on its own.

The 12 motivation statuses currently in the UI, in order:

Draft, Submitted, Changes requested, Details confirmed, Awaiting endorsement, Ready to print, Motivation printed, Awaiting payment, Payment received, Ready for collection, Shipped, Completed

## Phase 1: bugs from the audit

1. **Enquiries table clipped.** At a 1534px viewport the table is 1258px wide inside a 1213px wrapper with `overflow: hidden`, so the "View" button shows as "Vi". Make the table wrapper `overflow-x: auto`. Make the whole row a link and drop the View column. Clamp long names to two lines.
2. **Motivation detail can't progress.** On `/admin/motivation-requests/{id}` the empty state says "Confirm the details above to generate…" but there is no confirm button. "Regenerate motivations" shows when nothing has been generated yet. Phase 3 replaces this with the next-action bar. Until then, add a working "Confirm details" action, and show "Generate" or "Regenerate" depending on whether motivations exist.
3. **SAPS tracker contradiction.** On `/admin/applications` the stat card shows 1 application while Batches says "No applications tracked yet". Find out why the two queries disagree and fix it.
4. **Mobile overflow on the requests list.** At 390px the 13-tab status bar makes the page 1101px wide. Phase 2 replaces the tabs. Until then, contain them in a horizontally scrolling strip.
5. **Detail cards don't stack.** On mobile the Member and Status cards sit side by side at about 150px each. Stack all multi-column grids below 768px.
6. **No Documents empty state.** On the detail page the empty Documents table shows headers only. Show a sentence instead.
7. **Duplicate heading.** `/admin/motivation-members` renders two `h1`s. Keep one.
8. **Unlabelled search inputs.** Give every search input an `aria-label`. Placeholders are not labels.

## Phase 2: dashboard and list (mockups 01, 02, 06)

### Mockup 01, Overview at `/admin`

1. **KPI row and global search.** Four tiles: Needs your action, Motivations in progress, Awaiting payment, Unread enquiries. Each tile links to the filtered list. The top bar gets a global search across client name, ID number, cell, email and RM reference. Pressing `/` focuses it.
2. **Pipeline card.** Group the 12 statuses into four phases:

   | Phase | Statuses |
   |---|---|
   | Intake | Draft, Submitted, Changes requested |
   | Preparation | Details confirmed, Awaiting endorsement, Ready to print, Motivation printed |
   | Payment | Awaiting payment, Payment received |
   | Fulfilment | Ready for collection, Shipped, Completed |

   Fulfilment counts Completed for the last 30 days only. Every count links to the list filtered by that status or phase. An orange dot marks statuses that need an admin action.
3. **Needs your action table.** Show the statuses where the next move is ours: Submitted, Details confirmed, Awaiting endorsement, Payment received. Sort by time in the current stage, oldest first. "Waiting" turns amber after 3 days and red after 5; put both thresholds in config. Each row's button runs the next action, or deep-links to it on the detail page.
4. **Sidebar.** The area switcher (Motivations, Arms, Storage) moves into the sidebar as a segmented control, and the sidebar shows only the active area's links. Today all three areas show at once, which makes `/admin/choose` pointless. Store the choice on the user. The Motivations area nav becomes:
   - Overview
   - Work: Motivations, Enquiries, SAPS tracker, Clients
   - Setup: Application types, Public documents, Activity log
   - Footer: Settings, Users

   Show count badges on Motivations (needs action) and Enquiries (unread). Fix the greeting so it uses a real display name instead of "Developer".

Put a single `MotivationStatus` enum in charge of order, labels, badge colour, phase and the `needsAdminAction()` flag. The dashboard, list, badges and stepper all read from it.

### Mockup 02, list at `/admin/motivation-requests`

1. **Phase tabs.** Replace the 13 tabs with: All, Needs action, Intake, Preparation, Payment, Fulfilment, Completed. Each tab shows a count.
2. **Toolbar.** Search, plus filters for exact status, application type, and waiting time (any, over 3 days, over 7 days). Keep filter state in the query string. Add "+ New motivation" in the top bar; it opens a client picker that can also create a walk-in client. Today the only way in is through a member profile. Add "Export CSV" for the current filter.
3. **Columns.** Ref, Client (name, masked ID, masked cell), Type (name and section), Status badge, Progress (bar plus "n of 12"), In stage (coloured age), Updated, Next step. The whole row is clickable.
4. **Next step button.** Shown only when the status needs admin action. Every list gets a written empty state.
5. **Masking.** ID numbers show as the first 6 digits plus asterisks in all lists, and cell numbers as `082 *** 1234`. The full value appears on the detail page only, behind a "show" toggle that is logged. POPIA (South Africa's data protection act) is the reason.

### Mockup 06, mobile

Below 768px tables become cards: ref, age, name, type, status and the next-step button. Phase tabs become a scrolling chip row. On the detail page, a progress bar ("Step 4 of 12") replaces the stepper, sections collapse into rows that open, and the primary action is a sticky full-width bottom button. There is no horizontal page scroll at 360px.

## Phase 3: motivation detail (mockup 03)

1. **Header and stepper.** The header shows the ref, a status badge, type, section and created date, plus "Open client". Below it, a 12-step stepper: done steps are filled, the current step has a ring, and phase labels are optional.
2. **Next-action bar.** One primary button driven by the current status, with secondary "Send back for changes" where it applies. The button text uses real data: "Generate 2 motivations" counts the listed firearms, and the template line names the active template and its version. Put transitions in one service, such as `MotivationWorkflow::advance($request, $action)`, that:
   - validates the allowed `from → to` transitions
   - records who made the change and when
   - fires events for notifications

   Do not let a raw status `<select>` bypass it. Admins need a "Move to…" override in the `⋯` menu, which requires a reason and is logged.
3. **Answers.** Hide questions from branches that don't apply, along with unanswered optional questions. Today every Section 13 sub-branch (employed, housewife, pensioner, student…) renders as rows of dashes. Add a "Show unanswered" toggle and a "Jump to" section menu. Show a "required answered" badge.
4. **Documents checklist.** Build it from the application type's required documents: ✓ received or ! missing. Each missing item gets a "Request" action that emails or messages the client.
5. **Activity panel.** An internal notes box, plus a timeline of status changes, notes, client submissions and document events. Add a `motivation_request_events` table, or equivalent, if nothing like it exists.

The left column shows client details and a Linked card: the source enquiry, the SAPS application if one exists, and the client's other motivations.

## Phase 4: application types become the single config screen (mockup 04)

Today Application types, Motivation templates and Digital questionnaires overlap, and type and template names are identical. "Motivation: Self Defence (Handgun)" now has two templates attached, the original and "Self Defence, one page", and the UI doesn't show which one generates.

1. **Tabs.** `/admin/application-types/{id}` gets tabs: Overview, Questions, Motivation template, Documents, Requests.
2. **Active template.** The template tab lists attached templates with a radio for the one active template. Each template keeps its versions. A request stores the template version it was generated with, and switching the active template never changes existing requests.

Move the standalone Motivation templates and Questionnaires pages under this screen and redirect their old URLs. The Documents page becomes "Public documents", the downloads library, linked from each type's Documents tab. On the templates list, make the row open the builder and move Duplicate and Archive into a `⋯` menu.

## Phase 5: unified clients (mockup 05)

1. **Header.** Avatar initials, name, masked ID, cell and email, plus counts for enquiries, motivations, SAPS applications and items in storage.
2. **Tabs.** Timeline (everything merged, newest first), Motivations, Enquiries, SAPS tracker, Storage, Profile.

Merge Members (walk-in), SAPS tracker clients and enquiry contacts into one `clients` concept. Propose the schema and a migration plan first; don't write it yet. Match records on ID number, then email, then cell. Enquiries without an ID number stay unlinked until converted. Add "Start motivation" on an enquiry: it creates or links the client, creates the request, and records the enquiry as its source. When a motivation reaches the stage where it is lodged with SAPS, offer to create the tracker application from it.

## Phase 6: polish

- Replace `confirm()` with an Alpine modal that names the item and states the consequence. Archive and delete get a red confirm button.
- Give every list, table and panel an empty state.
- Show a toast for every successful action.
- Make keyboard focus visible on every interactive element.

## Done when

- No page scrolls horizontally at 390px.
- Every status transition goes through the workflow service, with tests.
- The dashboard counts match the list filters.
- No full ID number appears in any list view.
- The SAPS tracker numbers agree with each other.
