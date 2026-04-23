# CONSTRAINTS.md

<!-- GOVERNANCE
     This file records non-negotiable rules that apply to this project.
     Constraints are permanent until explicitly lifted by the owner.
     Each entry includes: CONSTRAINT (the rule), SCOPE (what it applies to),
     and SET (when and why it was established).

     Do not move taste preferences here from DESIGN.md unless they
     have become technical requirements. Do not record model routing
     preferences here — those belong in DECISIONS.md. -->

---

## Active Constraints

---

### C-01 · No External Network Requests for Typography

**CONSTRAINT:** No web fonts may be loaded. All typography must use the
system-UI stack (`system-ui, -apple-system, BlinkMacSystemFont`) for UI
and body copy, and Courier New / monospace for data readouts and metadata.
No `@font-face` declarations, no Google Fonts imports, no external CDN
font requests of any kind.

**SCOPE:** All pages, components, and views across the entire application.

**SET:** 2026-04-23 · Inherited from established design practice across
prior projects (creatrweb.com, open.creatrweb.com). Eliminates FOUT,
external network dependency, and privacy surface from font CDNs.

---

### C-02 · Prohibited UI Visual Patterns

**CONSTRAINT:** The following visual patterns must never appear on any
UI surface in this application:
- Gradients (decorative or background — UI surfaces only; see C-03 for exemption)
- Soft drop shadows (all shadows must use hard offset pattern: `4px 4px 0px`)
- Stock photography
- Blur-heavy hero or background sections
- Icon libraries or SVG icon imports (Unicode symbols preferred)
- Auto-playing media of any kind
- Emoji used as ornament or navigation
- Any visual language identifiable as SaaS product design

**SCOPE:** All UI surfaces, layouts, components, and pages.

**SET:** 2026-04-23 · Derived from confirmed Declared Preferences in
DESIGN.md. Non-negotiable design identity constraint.

---

### C-03 · Canvas Output Exemption from Visual Constraints

**CONSTRAINT:** The no-gradient and no-soft-shadow rules in C-02 apply
exclusively to UI surfaces. User-generated canvas output is fully exempt —
the generative art renderer may produce any visual output the user
configures, including gradients, soft edges, and complex color blending.
No visual constraint from C-02 may be applied to or enforced on canvas
render output.

**SCOPE:** HTML5 Canvas rendering layer only.

**SET:** 2026-04-23 · Established to prevent C-02 from being
misapplied to the generative output, which is user-controlled content,
not UI chrome.

---

### C-04 · Server-Side File Upload Sanitization

**CONSTRAINT:** All user-uploaded files (CSV, TSV, XLSX) must be
sanitized server-side before any parsing, processing, or database write
occurs. Sanitization must include: MIME type validation, file size limits,
extension allowlist enforcement, and content scanning before normalization.
No raw user file content may be written to the database or passed to the
canvas renderer without passing through the normalization pipeline.

**SCOPE:** PHP file upload handler and all data ingest paths.

**SET:** 2026-04-23 · Required to prevent malicious file injection,
SQL injection via uploaded data, and unexpected schema breaks in the
normalization pipeline.

---

### C-05 · Opencode Zen Free Model Instability

**CONSTRAINT:** No application feature, architectural decision, or
session plan may depend on a specific Opencode Zen free model being
available. Free Zen models (including Nemotron 3 Super Free, Ling 2.6
Flash Free, Big Pickle, MiniMax M2.5 Free) are time-limited offerings
whose availability may change between sessions without notice. All work
assigned to free Zen models must be substitutable with another model
from the available stack without breaking the build plan.

**SCOPE:** All model routing decisions and session plans involving
Opencode Zen free tier.

**SET:** 2026-04-23 · Established based on Opencode Zen documentation
confirming free model availability is time-limited and subject to change.

---

### C-06 · Opencode Zen Prompt Data Notice

**CONSTRAINT:** Personal user data and sensitive content must not be
submitted through Opencode Zen free models during development. Free Zen
models may collect prompt data for model improvement purposes. All
prompts sent through free Zen models must contain only non-sensitive
development content (code, schema, queries) — never real user data,
credentials, or private project content.

**SCOPE:** All development sessions using Opencode Zen free tier models.

**SET:** 2026-04-23 · Established based on Opencode Zen free tier
data collection terms.

---

<!-- Constraints are permanent until explicitly lifted by the owner.
     When a constraint is lifted, mark it LIFTED with date and reason
     rather than deleting it, so the decision history is preserved. -->