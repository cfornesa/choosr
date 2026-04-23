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