# DECISIONS.md

## Project: Data-to-Art Studio

### Project Description

A generative art workstation where users bring data (uploaded CSV, TSV, or XLSX files, curated preloaded public datasets, or live API feeds), map data columns to visual dimensions, and compose generative artwork by choosing art styles, color palettes, and rendering modes. Users can create accounts, save artwork state, and share their pieces via a persistent gallery. Built as a two-phase project.

***

## Stack

| Layer | Technology |
|---|---|
| Frontend | HTML, CSS, JavaScript (HTML5 Canvas for rendering) |
| Backend | PHP |
| Database | MySQL |
| Build tools | None required for Phase 1 |

***

## Phases

### Phase 1 — Core Workstation
- Data ingest and normalization pipeline (CSV, TSV, XLSX → common schema)
- Preloaded public dataset library UI
- Live API feed ingestion with MySQL TTL caching
- HTML5 Canvas generative art renderer
- Full creative controls: column-to-visual-dimension mapping, art style selector, palette picker, rendering mode selector
- PNG image export via Canvas blob download

### Phase 2 — Accounts, Save, and Share
- User authentication (registration, login, sessions)
- Persistent artwork state serialization and storage in MySQL
- User gallery and shareable artwork links
- Social/discovery features (TBD)

***

## Data Sources

| Source Type | Format | Ingest Method |
|---|---|---|
| User uploads | CSV, TSV, XLSX | PHP file handler → normalized schema → MySQL |
| Preloaded public datasets | Pre-normalized | Curated by developer, selectable from library UI |
| Live API feeds | JSON (external APIs) | PHP fetch → MySQL cache with TTL |

All three source types converge into a single normalized column/row schema before the frontend canvas renderer consumes the data.

***

## Creative Controls (Full User Control)

Users have full control over the following dimensions:

- **Column mapping**: Assign any data column to visual properties (color, size, position, opacity, stroke weight, density, etc.)
- **Art style**: Choose from enumerated rendering modes (e.g., particle field, geometric grid, flowing curves — to be defined in Phase 1 scoping)
- **Color palette**: User-selectable or custom palette applied across the canvas
- **Rendering mode**: Controls how the art engine interprets and draws mapped data

***

## Model Routing

| Concern | Tool | Model |
|---|---|---|
| Scaffold and project setup | Vibe CLI | Devstral 2 |
| PHP backend and MySQL schema | Opencode Go | Kimi K2.6 |
| HTML5 Canvas renderer and generative art JS | Opencode Go | MiMo-V2-Pro |
| UI/UX components and creative controls | Opencode Go | GLM-5.1 |
| Database queries and API feed caching | Opencode Zen | Nemotron 3 Super Free |
| Inline fixes and completions | Opencode Zen | Ling 2.6 Flash Free |
| Final review and refactor pass | Vibe CLI | Devstral 2 |

> **Notice**: Opencode Zen free models (Nemotron 3 Super Free, Ling 2.6 Flash Free) may collect prompt data for model improvement. Do not submit personal user data or sensitive content through these models during development.

> **PRC-Affiliated Model Notice**: Opencode Go routes include GLM-5.1, Kimi K2.6, and MiMo-V2-Pro, which are PRC-affiliated. These models must not be used for projects involving sensitive humanitarian content (Uyghur, Rohingya, Palestine, Sudan, Congo). This project does not involve such content and is cleared for full Opencode Go use.

***

## Architecture Notes

- All data sources normalize to a shared internal schema before the canvas renderer consumes them
- PHP handles file parsing, normalization, authentication, and API caching
- MySQL stores normalized datasets, user accounts, saved artwork state, and API cache entries
- JavaScript (Canvas API) handles all rendering client-side; PHP serves data via JSON endpoints
- No build tools required in Phase 1; may introduce a bundler in Phase 2 if complexity warrants it

***

## Session 3 — Architectural Choices (2026-04-23)

| Choice | Decision | Rationale |
|--------|----------|-----------|
| PDO connection pattern | Return-value (`$pdo = require 'database.php'`) over global variable | Cleaner, testable, no global state pollution. Caller assigns: `$pdo = require __DIR__ . '/../config/database.php'` |
| XLSX parsing | ZipArchive + SimpleXML (PHP built-in) | No Composer dependency. Verified `zip` extension available on runtime. Handles shared strings and cell references. |
| Upload filenames | UUIDv4-based (`{uuid}.{ext}`) | No original filename in storage path. Prevents path traversal and info leakage. |
| Column type inference | Heuristic: string > number > date > boolean priority | Scans first N non-null values per column. Returns `string` if any value is clearly text (short-circuits). |
| Upload user_id | `NULL` until Phase 2 auth is wired | `datasets.user_id` is nullable in schema. Auth is Phase 2. Upload handler explicitly sets NULL. |
| C-04 pipeline order | Validate → Move → Parse → DB write | Validation (size, ext, MIME) before `move_uploaded_file()`. Parse after move. DB write only after successful parse. `is_sanitized = 0` on insert, `= 1` after parse. Transaction wraps all DB writes with rollback + file cleanup on failure. |

***

## Session 4 — Authentication Endpoints (2026-04-23)

| Choice | Decision | Rationale |
|--------|----------|-----------|
| Auth directory | `api/auth/` at project root | Matches existing `api/` structure; session.php usable via `require_once __DIR__ . '/auth/session.php'` from sibling endpoints |
| session.php type | Utility file, not standalone endpoint | Outputs JSON 401 + `exit` on failure; exposes `$currentUserId` and `$currentUsername` on success via `require_once` |
| Login failure message | Generic "Invalid credentials" for ALL cases | Prevents user enumeration — wrong email, inactive account, wrong password all return identical 401 response |
| Session fixation | `session_regenerate_id(true)` in login.php | Regenerates session ID after successful authentication to prevent session fixation attacks |
| Duplicate checks | Separate queries for username and email | Provides specific error messages ("Username already taken" vs "Email address already registered") while using prepared statements | MIME allowlist format | Array-per-extension in `env.php`; `in_array()` check in `upload.php` | Flat associative map failed Windows XLSX uploads (`application/octet-stream`). Fixed 2026-04-23 post-review. |

***

## Session 5 — API Endpoints (2026-04-23)

| Choice | Decision | Rationale |
|--------|----------|-----------|
| datasets.php GET | Fetches user-owned + preloaded in single query, hydrates columns per dataset | Preloaded datasets must be universally accessible regardless of user_id |
| datasets.php DELETE | Verifies ownership before delete; physical file removed via unlink() with error_log fallback | Non-fatal file delete failure — DB record still removed cleanly |
| apiFeeds.php cache | ON DUPLICATE KEY UPDATE for cache upsert | Handles both first-write and refresh atomically without separate SELECT + INSERT logic |
| apiFeeds.php cache failure | Cache write failure is non-fatal | Logs error and returns fresh data — feed data is not lost on cache miss |
| artwork.php GET auth | Optional auth pattern for single ?id= GET | Unauthenticated users can view public artworks; authenticated users see own private artworks too |
| artwork.php POST | Returns HTTP 201 Created | Correct REST status for resource creation |
| artwork.php JSON fields | Decoded before returning in all GET responses | column_mapping, palette_config, rendering_config stored as JSON strings in MySQL; decoded to objects for frontend |

***

## Session 6 — Canvas Renderer and Art Style Modules (2026-04-23)

| Choice | Decision | Rationale |
|--------|----------|-----------|
| Data normalization | Embedded in renderer.js rather than separate normalizer.js | normalizer.js was empty stub; renderer handles column type detection, min-max numeric, categorical index, boolean, and date normalization internally |
| Global namespace | `window.DataToArt` with IIFE per file | No build tools per Phase 1 decisions. Each module self-registers on global. Style files load before artStyles.js which auto-detects. |
| Renderer factory | Constructor function (`new Renderer(canvas, opts)`) not class syntax | Avoids ES6 class transpilation concern; consistent with IIFE pattern |
| ResizeObserver | Primary resize strategy with window.resize fallback | Modern browsers support ResizeObserver; fallback covers older browsers per plan assumption |
| PNG export | `canvas.toBlob()` not `toDataURL()` | Better memory efficiency for large canvases; creates temporary anchor for download |
| Animation model | Synchronous by default, rAF only when `renderingConfig.animate === true` | Aligns with DESIGN.md "rendering is intentional and user-triggered" principle |
| Style module init | Called before each render (idempotent) rather than once | Allows styles to recalculate on resize; no persistent state needed for Phase 1 |
| Exposed normalization | `window.DataToArt.normalize` namespace | Exposes normalization utilities for potential reuse by other modules without duplicating logic |
| Destroy method | Renderer.destroy() cleans up observers, timers, animation frames | Prevents memory leaks when canvas is removed from DOM |

## Session 8 — App Entry Point and HTML Shell (2026-04-23)

| Choice | Decision | Rationale |
|--------|----------|-----------|
| Layout | Option A: Sidebar layout (canvas ~65% / controls ~35%) | Aligns with DESIGN.md "atelier workstation" metaphor — canvas as hero, controls as instruments arranged beside it |
| Auth UI | Minimal Phase 1: inline forms inside collapsible `<details>` | Sufficient for basic auth without over-building; register/login mode toggle with button row |
| Dataset loading | Fetch full list from datasets.php, filter client-side by `id` | No separate detail endpoint exists; simplest correct approach |
| Script paths | Relative from `/public/` using `../src/` and `../api/` | Consistent with existing codebase path convention — PHP server serves from project root |
| Error display | Inline `<div>` with CSS toggles, auto-hide with timer | Follows established pattern from other modules — no `alert()` calls |
| Style selector | Populated dynamically from `ArtStyles.listStyles()` | Auto-syncs when styles are added; defaults to first available (particleField) |
| CSS strategy | Minimal structural + visual styles in `public/css/app.css` | DESIGN.md palette (#1c1814, #242018, #0d0d0d, #c9922a, #4a8fa8, #f0ece4); hard offset shadows; no gradients on UI surfaces per C-02; system-ui type stack per C-01 |
| Auth mode toggle | Button pair with `.active` class, JS toggles form visibility | Simpler than tabs; both forms exist in DOM, only one visible at a time |
| Canvas sizing | `width: 100%; height: 100%` on canvas element | Renderer.js handles ResizeObserver internally for proper high-DPI scaling |

## Session 9 — CSS Refinements: "Playful Lab, Gallery Calm" (2026-04-23)

| Choice | Decision | Rationale |
|--------|----------|-----------|
| Base font-size | 14px → 15px | Improved readability on atelier-dark backgrounds; safer at sidebar width |
| Line-height | 1.5 → 1.6 | Better text breathing room at the increased font-size; calmer rhythm |
| Sidebar padding/gap | 16px → 20px each | Reduces cramped feel; establishes 4px spacing rhythm multiples throughout |
| Canvas region frame | 1px solid #2a2420 border + 4px padding | Creates subtle "atelier wall" depth without breaking ResizeObserver; stays within C-02 (no soft shadows) |
| Control group accent | 3px border-left #c9922a + 12px padding-left | Visually groups controls as "instruments" — consistent with the design metaphor without new HTML elements |
| Input/select hover | border-color transition to #c9922a (0.15s) | Subtle lab-like tactility; gold accent ties hover to the primary action color |
| Button enhancements | min-height: 40px, filter brightness on hover/active | Larger click targets for comfort; brightness shift is more playful than color swap; stays within C-02 |
| Auth visual demotion | border 2px → 1px, summary color #4a8fa8 → #8a8580 | Auth is a secondary concern; summary muted to visually recede until expanded |
| Auth toggle clarity | Inactive buttons: background #1c1814, color #6a6560 with hover transitions | Makes inactive state unmistakably dim; hover brightens to confirm interactivity |
| Auth input focus | 1px → 2px outline #4a8fa8 | Ensures keyboard focus is clearly visible on dark backgrounds (accessibility) |
| Auth form inputs | padding 6px → 7px 10px, hover border #4a4a44 | Slightly more comfortable; hover gives subtle feedback without gold (reserved for primary controls) |
| Media queries | ≤1200px and ≤1024px breakpoints | Desktop-first narrowing only; 1200px reduces padding/gaps, 1024px shrinks sidebar to 320px flex basis with 260px min and 14px font |
| No HTML changes | All refinements achieved through CSS only | No new elements or ID changes needed; class-based styling applied to existing structure |
| C-02 compliance | No gradients, no soft shadows | All shadows remain hard offset (4px 4px 0px pattern or inset variant); brightness filter is not a gradient |

## Session 10 — Structure Refactor & Integration Pass 1

| Choice | Decision | Rationale |
|--------|----------|-----------|
| Project root as document root | Moved `public/` contents to project root, deleted `public/` directory | Enables typical PHP hosting where domain root maps directly to project root without special `/public` mapping |
| Static HTML entry point | Kept `index.html` as static file (not `index.php`) | No PHP logic needed in entry point; API endpoints in `api/` handle server-side processing |
| Relative path strategy | Changed all `../src/` to `src/` and `../api/` to `api/` in `index.html` | With document root at project root, all paths are relative to root |
| Empty state overlay | Added `#dta-empty-state` div in canvas region with quiet message | First-run UX: clear guidance for new users without datasets, consistent with calm gallery tone |
| Empty state CSS | Hard offset shadow (4px 4px 0px), semi-transparent dark background | C-02 compliant: no gradients, no soft shadows, aligns with dark atelier identity |
| Auth summary dynamic display | Added `#dta-auth-summary` span for showing "Logged in as {username}" | Auth flow clarity: user immediately sees their logged-in state in the collapsed auth section |
| Logout endpoint | Created `api/auth/logout.php` with session destruction | Completes auth flow: register, login, and now logout without workarounds |
| Logout button visibility | Hidden by default, shown only when logged in | Clean UI: logout action only available when relevant |
| Export validation | Check `_currentDataset` before triggering export | Prevents confusing export of blank canvas, consistent with error prevention |
| Error/status exposure | Exposed `_showError`, `_showStatus` on `window.DataToArt.App` | Enables consistent error handling across modules without duplication |

## Session 11 — Account Section Placement + Logout UX (2026-04-23)

| Choice | Decision | Rationale |
|--------|----------|-----------|
| Account section moved to top | Moved `<details id="dta-auth-section">` to first child of `<aside id="dta-sidebar">` | Visibility: account status and logout were too hidden at bottom; top placement ensures users see their auth state immediately |
| Teal left border accent | Added `border-left: 3px solid #4a8fa8` to `#dta-auth-section` | Distinguishes "meta" (account) from "creative" (art controls with gold #c9922a); consistent with existing color system |
| PHP `data-username` attribute | Added `data-username="{session_username}"` on sidebar via `get_current_username()` | Avoids extra API call to `/api/auth/session.php`; studio.php only renders for authenticated users anyway |
| JS auto-populates auth state | `init()` reads `data-username` and sets `_currentUsername`, updates summary/status, shows logout button | Clean initialization without waiting for login event; logout button visible by default on studio.php |
| Logout button always visible | Removed `style="display:none;"` from `#dta-logout-btn` in studio.php | studio.php redirects unauthenticated users, so logout is always relevant here |
| `:focus-visible` added | Added `outline: 2px solid #4a8fa8` to `.dta-auth-logout-btn:focus-visible` | Accessibility: keyboard navigation clear on dark backgrounds; matches existing auth input focus style |

## Session 12 — Auth Panel Behavior Fix (2026-04-23)

| Choice | Decision | Rationale |
|--------|----------|-----------|
| Auth state detection | Read `data-username` from `#dta-sidebar` during `init()` | Already populated by PHP; synchronous, no extra API call; studio.php redirects unauthenticated users so this is reliable |
| Wrapper div for hiding | Added `#dta-auth-forms-container` wrapping tabs + both forms | Allows CSS to hide both forms and tabs with single `.dta-auth-logged-in` parent class |
| CSS `.dta-auth-logged-in` rules | `#dta-auth-section.dta-auth-logged-in #dta-auth-forms-container { display: none !important; }` and `#dta-auth-section:not(.dta-auth-logged-in) #dta-logout-btn { display: none; }` | Class-based toggling isolates JS from display logic; `!important` ensures CSS wins over any inline styles |
| Default tab flipped to Login | Removed `active` from Register button, added to Login button; removed `style="display:none;"` from Login form | Plan requirement: Login is now default for logged-out users (previously Register was default) |
| `_authState` object | `{ loggedIn: false, username: null }` replaces `_currentUsername` scalar | Consolidates all auth state into one object; used by `_updateAuthUI()` for all state-driven DOM updates |
| `_updateAuthUI()` function | Reads `_authState`, toggles `.dta-auth-logged-in` class, updates status/summary text | Consolidates duplicate auth UI update code from init/login/logout handlers into single function |
| Login success handler | Calls `_authState.loggedIn = true; _authState.username = data.username; _updateAuthUI();` then `loadDatasetList()` | Replaces ~8 lines of manual DOM updates with single `_updateAuthUI()` call |
| Logout success handler | Calls `_authState.loggedIn = false; _authState.username = null; _updateAuthUI();` then redirects | `_updateAuthUI()` runs before redirect timer; UI is correct even if redirect is delayed |
| Tab toggle functions preserved | `_showRegisterForm()` and `_showLoginForm()` unchanged | These still work for logged-out users; CSS hides them when `.dta-auth-logged-in` is present |
| Assumptions surfaced | (1) `data-username` reliably indicates auth state; (2) Login-as-default improves UX over Register; (3) hiding tabs entirely is cleanest UX | Logged in DECISIONS.md per session protocol |

## Unresolved Checkpoints

- [ ] DESIGN.md References must be completed before the first coding session
- [ ] Curated public dataset list not yet defined (required for Phase 1 library UI)
- [ ] Live API feed sources not yet selected
- [ ] ~~Art styles and rendering modes not yet enumerated~~ **RESOLVED** — seeded in schema.sql (Session 2): particle_field, geometric_grid, flowing_curves
- [ ] Normalized dataset schema not yet formally specified
- [ ] Phase 2 gallery and sharing feature scope not yet detailed
- [ ] `api/upload.php` user_id is hardcoded NULL — must require
  `api/auth/session.php` and pass `$currentUserId` once Phase 2
  auth is wired to the upload endpoint
- [ ] Animation stop/pause UI control not yet built — `Renderer._animationId`
  is tracked and ready; Session 7 UI layer must expose a stop button
  that calls `cancelAnimationFrame` via a future `renderer.stop()` public method

## Session 13 — Disable Public Registration (Single-Owner App) (2026-04-24)

| Choice | Decision | Rationale |
|--------|----------|-----------|
| Registration endpoint behavior | Always return `{ "success": false, "error": "Registration is disabled." }` | Single-owner positioning: no public self-service registration; only manually created DB users can log in |
| register.php implementation | Simplified to single JSON response, removed all validation/DB insert logic | Keeps file and response shape stable for graceful frontend error handling; prevents any user creation |
| Landing page CTA | Changed to "Login (owner only)" | Removes any implication of registration availability; clear visitor messaging |
| Landing modal footer | Changed to "Owner access only" | Removes "Don't have an account? Register" link; maintains clean UI |
| Studio auth sidebar | Removed Register tab and form entirely | Only Login form shown when logged out; aligns with single-owner positioning |
| Auth notice in sidebar | Added "Registration is disabled. Owner access only." | Provides explicit explanation for any legacy UI expectations |
| Login/logout/session endpoints | No changes to behavior or API contract | Existing users in DB continue to work; only registration path disabled |

---

## Session 14 — Portfolio Features: Artwork Metadata, Public/Featured Flags, Exhibit Views (2026-04-24)

| Choice | Decision | Rationale |
|--------|----------|-----------|
| DB Schema Extension | Added `is_featured TINYINT(1) DEFAULT 0` and `tags VARCHAR(255)` to artworks table | Supports metadata requirements: comma-separated tags (user confirmed VARCHAR over JSON), featured flag for homepage display. Index on `(is_public, is_featured, created_at)` for efficient public queries. |
| Tags storage format | VARCHAR(255) comma-separated | User explicitly confirmed this over JSON to match their stated preference in the prompt; simpler for display and querying |
| artwork.php PATCH | Added PATCH endpoint for metadata updates | Allows updating title, description, tags, is_public, is_featured on existing artworks without resubmitting full render state |
| Artwork save/load flow | Save Artwork (POST with full state) + Save Metadata (PATCH for updates) | Separates concerns: POST creates with all data, PATCH updates metadata only. Both use existing artwork.php endpoint |
| New endpoint strategy | Created `api/artworks.php` for public listing | Cleaner than extending artwork.php with filters; follows RESTful pattern: artwork.php for single CRUD, artworks.php for collections |
| Public listing behavior | `filter=featured` returns is_public=1 AND is_featured=1; `filter=public` returns all is_public=1 sorted by is_featured DESC | Homepage shows only featured; portfolio shows all public with featured first |
| Data cleaning layers | Both dataMapper.js (filter rows) + renderer.js (guard values) | Defense in depth: dataMapper.cleanData() filters rows with invalid numeric values before normalization; renderer sanitizes NaN/Infinity with clamping |
| Empty state handling | All pages show friendly messages when no artworks exist | "No featured pieces yet", "No public artworks yet", "Not found or not public" with navigation links |
| Exhibit embed code | Static iframe snippet with absolute URL | `https://dataart.creatrweb.com/exhibit.php?id=ARTWORK_ID` with width=800, height=600, frameborder=0; copy-paste ready |
| Thumbnail path handling | Updated to use `public/assets/thumbnails/` directory | Consistent with existing structure; APP_URL based URLs for public access |
| Featured limit | 3 artworks on homepage (PORTFOLIO_FEATURED_LIMIT) | Reasonable showcase without overwhelming; configurable via env.php |
| CSS organization | Added metadata panel styles to app.css; inline styles for portfolio/exhibit pages | Maintains dark atelier palette; page-specific styles minimize mutual interference |

### Unresolved Checkpoints
- [ ] Thumbnail generation not implemented (future enhancement; current code handles null thumbnails gracefully)

### Assumptions Surfaced (Session 14)
1. User will run ALTER TABLE manually via phpMyAdmin (Irreversible Decision - Stopped for confirmation)
2. Tags as VARCHAR(255) comma-separated (User confirmed)
3. Data cleaning at both dataMapper + renderer layers (User confirmed: "Both layers")
4. New api/artworks.php endpoint (User confirmed via "create a new portfolio.php endpoint")
5. Style IDs map: 1=particleField, 2=geometricGrid, 3=flowingCurves (Hardcoded for now; could be dynamic)

## Session 15 — Portfolio Features: Fixes and Integration (2026-04-24)

| Choice | Decision | Rationale |
|--------|----------|-----------|
| Thumbnail URL in JS | Added DTA_CONFIG.thumbnailUrl JS variable using ARTWORK_THUMBNAIL_URL PHP constant | Fixes hardcoded 'public/assets/thumbnails/' paths in index.php and portfolio.php JS that would break in production. Ensures thumbnails load correctly regardless of deployment path. |
| Data cleaning integration | Added DataMapper.cleanData() call in Controls.triggerRender() | Implements the "both layers" defense-in-depth approach: filters rows with invalid numeric values before rendering, complementing renderer's NaN/Infinity guards. Logs row removal count to console for debugging. |

## Session 16 — Restore Auth Panel + Controls (2026-04-24)

| Choice | Decision | Rationale |
|--------|----------|-----------|
| Studio auth state detection | Added data-authenticated="1" attribute to #dta-sidebar in studio.php | Ensures JS can reliably detect logged-in state even when username might be empty. Studio.php PHP check already guarantees authentication, so this attribute is always "1". |
| Robust username lookup | Use $_SESSION['username'] directly instead of get_current_username() in studio.php | Prevents null username from causing empty data-username attribute, which would fail JS truthy check. |
| Conditional login form display | Only call _showLoginForm() on non-studio pages | Studio.php redirects unauthenticated users, so login form should never be manually shown there. Prevents form flash before CSS hides it. |

### Assumptions Surfaced (Session 16)
1. get_current_username() can return null even for authenticated sessions if $_SESSION['username'] not set
2. Studio.php PHP redirect ensures only authenticated users see the page, but JS still needs reliable state
3. Controls/PalettePicker regression may be pre-existing or due to browser caching

### Unresolved Checkpoints
- [x] Thumbnail URL bug (Fixed in Session 15)
- [ ] Verify Controls/PalettePicker rendering in live environment (may require cache clear)

## Session 17 — Auth Panel Regression + Art Styles Repair (2026-04-24)

| Choice | Decision | Rationale |
|--------|----------|-----------|
| Auth detection strategy | Prioritize `data-authenticated === '1'` over non-empty `data-username` | studio.php hardcodes `data-authenticated="1"` for all auth'd users; `data-username` can be empty (login API sets `user_id` + `email`, not `username`). Detecting on authenticated attribute is reliable. |
| Username fallback | Use `data-email` or default to `'Owner'` if `data-username` is empty | Ensures logged-in UI always shows a meaningful display name, even when username wasn't set in session. |
| Style registration timing | Added `registerBuiltinStyles()` function in artStyles.js + delayed retry in app.js | Style modules attach to `window.DataToArt` after artStyles.js IIFE executes; explicit re-scan function + 150ms retry handles race condition gracefully. |
| CSS visibility rules | Verified correct — no changes needed | Existing `.dta-auth-logged-in` rules properly hide forms and show logout; only needed to ensure class was applied reliably. |

### Assumptions Surfaced (Session 17)
1. `$_SESSION['username']` is not reliably set during login — PHP login API sets `user_id` and `email`, not `username`
2. Style module IIFEs may execute after artStyles.js auto-registration runs — defensive retry handles this
3. `data-authenticated="1"` attribute is a reliable auth signal since studio.php only renders for authenticated users (PHP redirect)

### Files Modified
- `src/canvas/artStyles.js`: Added `registerBuiltinStyles()` function + exposed on ArtStyles API
- `src/app.js`: Fixed auth state detection (lines 841-860); added `populateStyles()` with 150ms retry (lines 880-916)
- `css/app.css`: Verified existing visibility rules at lines 398-405 — no changes needed

***

## Session 18 — Data-Loading Pipeline + Save Artwork Debug (2026-04-24)

| Choice | Decision | Rationale |
|--------|----------|-----------|
| `loadDataset()` premature check | Removed `var dataset = null; if (!dataset)` before search loop in app.js | The check always evaluated to true (dataset literally null), causing early return before the loop that actually finds the dataset could execute. |
| Duplicate `mapApiResponse` calls | Removed second call and redundant `if (!mapped)` checks in app.js | Code duplication from incremental fixes; single call with single check is correct. |
| Controls state exposure | Made `_currentDataset`, `_currentColumnMapping`, `_currentPaletteConfig`, `_currentRenderingConfig`, `_currentStyleKey` public properties on Controls object | App.js Export/Save handlers check `window.DataToArt.Controls._currentDataset` — these were private closure variables, not accessible externally. |
| PHP 8.1 compatibility | Removed `array_is_list()` dependency in artwork.php | Function only available in PHP 8.1+; replaced with simple `is_array()` check since JSON-decoded objects/arrays are both valid for MySQL JSON columns. |
| JSON encoding validation | Added checks for `json_encode()` success in artwork.php | Prevents boolean false (encoding failure) from being inserted into database. |
| Enhanced error reporting | Modified catch block to always return PDO error details | Previously generic message behind `APP_DEBUG` flag; now always shows actual error for faster debugging. |
| Schema migration guidance | Identified missing `is_featured` column as root cause of 500 error | User must run ALTER TABLE statements manually if database predates schema update. |

### Assumptions Surfaced (Session 18)
1. Private closure variables must be exposed as object properties for cross-module access (Controls state accessed by App handlers)
2. PHP version compatibility matters — array_is_list() was causing fatal errors on PHP < 8.1
3. Database schema ALTER statements must be applied retroactively for existing installations
4. Inline debug logs in app.js (console.log) caused premature reference to undefined dataset.id

### Files Modified
- `src/app.js`: Fixed `loadDataset()` function (removed premature check, removed duplicate calls, removed debug logs)
- `src/controls/controls.js`: Exposed internal state as public properties on Controls object
- `api/artwork.php`: Removed array_is_list() dependency, added JSON encoding validation, enhanced error reporting

### Unresolved Checkpoints
- [ ] User must run ALTER TABLE to add `is_featured` and `tags` columns to existing `artworks` table
