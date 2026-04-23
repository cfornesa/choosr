# Decisions
<!-- IMPORTANT: Load CONSTRAINTS.md and DESIGN.md alongside this
file at every session start. Constraints listed in CONSTRAINTS.md are binding regardless of what is recorded here. Design identity in DESIGN.md informs all gallery
options regardless of session context. -->

## Project Profile

<!-- Operational details for this project. Kept here, not in AGENTS.md,
     to keep the root instruction file framework-agnostic and safe to
     publish. Do not put credentials, hostnames, file paths, or API
     keys here — those belong in .env.

     An agent fills this section during Phase 1 by asking the person
     plain-language questions. If this section is empty, ask before
     writing any code. See AGENTS.md → Detect the Framework. -->

- **Stack:** <!-- e.g. Next.js 15 + TypeScript, FastAPI + Python 3.12 -->
- **Deployment:** <!-- type only — e.g. "Node.js PaaS, single process,
  npm start". No hostnames or server details. -->
- **Database:** <!-- type and ORM only — e.g. "SQLite via Drizzle ORM".
  No file paths. -->
- **Version pins:** <!-- key constraints — e.g. "Node 20, Next.js 15.x".
  None if using stable defaults. -->
- **Framework AGENTS.md:** <!-- e.g. "nextjs/AGENTS.md does not yet
  exist — sessions follow root AGENTS.md only" -->
- **Profile switch rule:** Stop before touching existing files. Record
  current state and reason here. Confirm new profile explicitly. Flag
  every file needing migration before starting.

---

## REVIEW REQUIRED — Read before starting next session
<!-- Agent writes this block. Human must confirm or override each item before new code is written. -->
- [ ] [DATE] [TOOL] Conservative default applied: [what was chosen and why]
- [ ] [DATE] Gallery deferred: [what decision was skipped and what options were considered]

---

## Example Phase 1 — [Tool Name]

<!-- Created by the agent at session start.
     Record every significant decision made during this phase.
     Use bullet points. One fact per bullet.
     Flag gaps or deferred items as noted below. -->

### Stack Confirmed
<!-- e.g. which framework, runtime, package manager, config approach -->

### Schema and Data Decisions
<!-- e.g. ID strategy, timestamp format, default values, unique columns -->

### Files Created
<!-- List every file created and its purpose -->

### Vendor Dependencies Added
<!-- For each: name, purpose, sends data off-domain (yes/no),
     self-hosting alternative, documented in docs/dependencies.md -->

### Environment Variables Required
<!-- List names only — no values. e.g.:
     - DATABASE_URL
     - API_KEY_NAME -->

### Gaps and Deferred Items
<!-- Any Phase 1 deliverable not completed, logged for the next phase -->

### Unresolved Checkpoints Entering Phase 2
- [ ] <!-- item -->

---

## Example Phase 2 — [Tool Name]

<!-- Same structure as Phase 1. Add a "Corrections Applied" subsection
     if prior-phase errors were fixed in this session. -->

### Phase N Gap Discovered
<!-- If a prior-phase deliverable was missing, record it here with a
     note that it is a prior-phase gap, not a Phase 2 decision. -->

### Components Built
<!-- List each component/route/feature with its file path and purpose -->

### Corrections Applied
<!-- What was wrong, what was fixed, what file changed -->

### Vendor Dependencies Added
<!-- Same format as Phase 1 -->

### Environment Variables Required
<!-- Cumulative list — include all variables from prior phases plus new -->

### Unresolved Checkpoints Entering Phase 3
- [ ] <!-- item -->

---

<!-- Add a new dated section at the start of each phase following
     the same pattern. Resolved checkpoints from the prior phase
     should be marked [x] and left in place — do not delete them.
     They are the audit trail. If empty, begin with Phase 1. -->