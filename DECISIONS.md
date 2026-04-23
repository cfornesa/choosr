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

## Unresolved Checkpoints

- [ ] DESIGN.md References must be completed before the first coding session
- [ ] Curated public dataset list not yet defined (required for Phase 1 library UI)
- [ ] Live API feed sources not yet selected
- [ ] Art styles and rendering modes not yet enumerated (required before canvas renderer is built)
- [ ] Normalized dataset schema not yet formally specified
- [ ] Phase 2 gallery and sharing feature scope not yet detailed