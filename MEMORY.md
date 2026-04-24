# MEMORY.md

<!-- GOVERNANCE
     This file records confirmed durable lessons from prior sessions.
     Only entries the owner has explicitly confirmed are added here.
     Each entry is a single confirmed lesson — not a summary or a note.

     Format:
     YYYY-MM-DD · CATEGORY · Lesson in one sentence.
         [Optional: the exact exchange or context that surfaced it]

     Valid categories:
     DESIGN · ARCHITECTURE · CONSTRAINT · WORKFLOW · IDENTITY

     Entries are permanent unless explicitly removed by the owner.
     When approaching 50 entries, ask the owner to review —
     consolidate stable patterns and archive older entries to
     docs/memory-archive.md. -->

---

## Confirmed Lessons

2026-04-23 · DESIGN · The dark atelier palette was chosen deliberately
    over a light studio ground because distinctiveness from generic art
    tools is a design value, not merely an aesthetic preference.
    [User: "a dark atelier at night would be more unique compared to
    most light-themed artistic websites."]

2026-04-23 · DESIGN · fornesusart.com is the canonical palette reference
    for any project where the UI must recede behind user-generated visual
    content — emotionally varied, chromatic, and abstract-first.
    [User: "the fornesusart.com color scheme may be more varied, which
    is what I envision for this application."]

2026-04-23 · IDENTITY · The canvas-as-hero / controls-as-instruments
    tension is a confirmed structural principle for the Data-to-Art
    Studio — the generative canvas is always the dominant visual zone
    and all controls are secondary instruments arranged around it, not
    a feature checklist.
    [Confirmed during Derived Identity review, 2026-04-23.]

2026-04-23 · CONSTRAINT · User-uploaded file sanitization is
    non-negotiable before any data reaches the normalization pipeline
    or canvas renderer — MIME type validation, file size limits,
    extension allowlist enforcement, and content scanning must all
    occur server-side before processing begins.
    [Recorded as C-04 in CONSTRAINTS.md, 2026-04-23.]

2026-04-23 · ARCHITECTURE · Opencode Zen free models are supplemental
    and substitutable by design — no feature, architectural decision,
    or session plan may depend on a specific free Zen model being
    available between sessions.
    [Recorded as C-05 in CONSTRAINTS.md, 2026-04-23.]

2026-04-23 · ARCHITECTURE · The C-04 sanitization pipeline must execute
    in strict order — validate (size, extension, MIME via finfo) before
    move, move before parse, parse before DB write — with is_sanitized = 0
    on initial insert and = 1 only after successful parse, all wrapped in
    a transaction with rollback and file cleanup on failure.
    [Implemented in api/upload.php, Session 3, 2026-04-23.]

2026-04-23 · ARCHITECTURE · The canvas renderer uses a global namespace
    pattern (`window.DataToArt`) with IIFE-wrapped modules and script load
    order dependency — style files must load before `artStyles.js`, which
    must load before `renderer.js`. No ES modules or bundler.
    [Implemented in src/canvas/*, Session 6, 2026-04-23.]

2026-04-23 · ARCHITECTURE · Data normalization is embedded in renderer.js
    rather than a separate module — detects column type
    (number/boolean/date/string) from a 50-value sample, applies
    type-specific min-max normalization, and returns null for missing
    values so style modules can apply fallbacks.
    [Implemented in src/canvas/renderer.js, Session 6, 2026-04-23.]

2026-04-23 · DESIGN · Canvas rendering is synchronous by default with
    animation opt-in via `renderingConfig.animate === true`, aligning with
    the principle that rendering is intentional and user-triggered — no
    ambient or auto-playing canvas output.
    [Implemented in src/canvas/renderer.js, Session 6, 2026-04-23.]

2026-04-23 · ARCHITECTURE · The app entry point (src/app.js) follows the
    IIFE-on-window.DataToArt namespace pattern, wiring DOM events to module
    APIs in a DOMContentLoaded handler — all other modules must be loaded
    before app.js via the 11-script sequence in index.html.
    [Implemented in src/app.js and public/index.html, Session 8, 2026-04-23.]

2026-04-23 · ARCHITECTURE · Dataset list comes from a single GET to
    datasets.php (no detail endpoint); app.js caches the list and filters
    client-side by id to load individual datasets.
    [Implemented in src/app.js, Session 8, 2026-04-23.]

2026-04-23 · DESIGN · The sidebar layout (canvas ~65%, controls ~35%)
     implements the "atelier workstation" metaphor from DESIGN.md — canvas
     as hero, controls as instruments arranged beside it, not in a feature
     checklist.
     [Implemented in public/index.html and public/css/app.css, Session 8, 2026-04-23.]

2026-04-23 · DESIGN · Canvas frame uses neutral #2a2a2a at 2px rather than
     warm palette tones — warmer colors like #2a2420 compete with the gold
     sidebar accent; the gallery-wall metaphor benefits from a border that
     recedes rather than asserts itself.
     [Implemented in public/css/app.css, Session 9, 2026-04-23.]

2026-04-23 · DESIGN · The border-left accent pattern on .dta-control-group
     (3px gold #c9922a) is a reusable "instrument frame" convention that
     requires no HTML changes — any future sidebar section can adopt it
     for visual grouping without adding new wrapper elements.
     [Implemented in public/css/app.css, Session 9, 2026-04-23.]

2026-04-24 · ARCHITECTURE · Project root serves as document root for PHP
    hosting — `public/` directory eliminated, all assets served from root.
    [Structure refactor: public/index.html → index.html, public/css/ → css/]

2026-04-24 · WORKFLOW · Empty states are first-class UX concerns — canvas
    overlay with quiet messaging for new users, dataset select with
    clear next-action copy. Aligns with "playful lab inside calm gallery" tone.
    [Implemented in index.html, css/app.css, src/app.js]

2026-04-24 · ARCHITECTURE · Auth flow completeness requires logout
    capability — session destruction endpoint and UI controls are
    minimum viable for user account management in Phase 2.
    [Created api/auth/logout.php, added logout button with show/hide logic]

2026-04-24 · ARCHITECTURE · Single-owner app positioning disables public
    registration — api/auth/register.php returns static disabled response,
    all register UI removed from landing page and studio sidebar. Existing
    auth endpoints unchanged; manually created DB users only.
    [Implemented in Session 13: api/auth/register.php, index.php,
    studio.php, src/app.js]

2026-04-24 · ARCHITECTURE · Defense-in-depth data cleaning uses both
    preprocessing (filter invalid rows) and runtime guards (sanitize normalized
    values) — dataMapper.cleanData() filters rows with null/NaN/non-numeric
    values for mapped numeric dimensions, while renderer.js sanitizes to
    prevent NaN/Infinity in style modules.
    [Implemented in Session 14: src/data/dataMapper.js, src/canvas/renderer.js]

2026-04-24 · ARCHITECTURE · RESTful endpoint separation: single resource
    CRUD at artwork.php, collection queries at artworks.php — cleaner than
    overloading single endpoint with filter parameters.
    [Implemented in Session 14: api/artwork.php (POST/PATCH/GET/DELETE),
    api/artworks.php (GET with filter param)]

2026-04-24 · WORKFLOW · Additive changes must maintain graceful empty states
    — all public pages show friendly messages and navigation options when no
    data exists, preventing crashes or errors.
    [Implemented in Session 14: index.php, portfolio.php, exhibit.php]

2026-04-24 · DESIGN · Public portfolio/exhibit pages extend dark atelier identity
    consistently — same color palette (#1c1814, #242018, #0d0d0d, #c9922a,
    #4a8fa8, #f0ece4), hard offset shadows, no gradients, system fonts only.
    [Implemented in Session 14: portfolio.php, exhibit.php with inline styles
    matching css/app.css palette]

2026-04-24 · ARCHITECTURE · User confirmation required for irreversible
    decisions — ALTER TABLE statements provided as SQL comments for manual
    execution via phpMyAdmin, not executed automatically.
    [Session 14: db/schema.sql includes commented ALTER TABLE statements]

2026-04-24 · WORFLOW · JS-PHP state passing requires explicit reliability checks
    — session-dependent attributes (data-username) may be empty even for
    authenticated users; always provide a fallback attribute (data-authenticated)
    for critical state detection.
    [Session 16: studio.php added data-authenticated="1", src/app.js checks
    data-authenticated || data-username]

2026-04-24 · ARCHITECTURE · Public asset URLs must be passed to frontend JS via
    PHP-echoed config variables — never hardcode paths that differ between
    development and production environments.
    [Session 15: index.php and portfolio.php use DTA_CONFIG.thumbnailUrl from
    ARTWORK_THUMBNAIL_URL constant]

2026-04-24 · WORKFLOW · Regression diagnosis must trace state propagation
    through the full stack — PHP session → HTML data attributes → JS initialization
    → CSS class toggles → DOM visibility. Auth UI issues often stem from
    broken state chains, not UI code.
    [Session 16: studio.php auth panel regression traced to null username
    causing empty data-username, failing JS truthy check]

2026-04-24 · ARCHITECTURE · Private closure variables in IIFE modules are
    inaccessible to other modules — cross-module state must be exposed as
    public object properties, not private vars, when accessed by external code.
    [Session 18: Controls._currentDataset was private closure var; app.js
    Export/Save handlers needed access — exposed as Controls public properties]

2026-04-24 · ARCHITECTURE · PHP version-specific functions (e.g.,
    array_is_list() requires PHP 8.1+) must be avoided in shareable code
    — use compatible alternatives or polyfills.
    [Session 18: array_is_list() caused fatal errors on PHP < 8.1;
    replaced with simple is_array() check in artwork.php]

2026-04-24 · WORKFLOW · Database schema changes require retroactive ALTER
    TABLE statements for existing installations — CREATE TABLE IF NOT EXISTS
    does not automatically add new columns to pre-existing tables.
    [Session 18: Missing is_featured column caused 500 error on artwork POST;
    user must run ALTER TABLE manually per schema.sql comments]
