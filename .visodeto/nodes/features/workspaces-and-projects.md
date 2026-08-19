---
id: feature-workspaces-projects
type: feature
status: planned
title: Workspaces and projects
priority: foundational
decision_gate: blocked
---

# Workspaces and projects

## Authority and status vocabulary

This document is the authoritative decision-ready specification for the feature. It reconciles the source requirement with the Product/UX, Domain Architecture, Laravel Backend, React Flow Frontend, and Quality/Security analyses. It does not authorize implementation while `decision_gate` is `blocked`.

- **confirmed** — explicit product intent in this document's original requirements or a later recorded user confirmation.
- **proposed** — a recommended design needed to make confirmed intent coherent, secure, or testable; not approved product intent.
- **open-blocking** — must be confirmed or explicitly excluded before coding cards are created.
- **open-deferrable** — may remain unsupported in this delivery if that exclusion is confirmed; the design must not accidentally add it.

Trace labels:

- `SRC`: original feature requirement in this file.
- `P`: `.visodeto/project.md`.
- `PUX`: Product/UX analysis, Kanban task `t_5b2e7367`.
- `DOM`: domain analysis, Kanban task `t_ce2ac11c`.
- `BE`: Laravel analysis, Kanban task `t_4ae14633`.
- `FE`: React/Inertia/React Flow analysis, Kanban task `t_2f388aa2`.
- `QS`: `.visodeto/analysis/workspaces-projects-quality-security.md`, task `t_ed48dd27`.
- `USER`: an explicit user confirmation recorded after this decision gate.

## Goal — confirmed

Users organize their software work in workspaces. Every software project belongs to exactly one workspace.

Trace: `SRC`.

## Terminology

- **Workspace:** the tenant/access boundary and container for projects. It is not synonymous with organization or team.
- **Membership:** the relationship through which a user receives access to a workspace and therefore its projects.
- **Project:** one software initiative contained by exactly one workspace. A project is not a repository.
- **Project graph:** semantic project knowledge and typed relationships. The visual canvas/layout is a projection, not tenancy or semantic truth.
- **Markdown knowledge:** human/AI-authored project intent and decisions. Repository-derived facts remain linked implementation facts rather than duplicated manual truth.
- **Repository connection:** an optional project-scoped binding to a Git repository. Credentials and local paths are not project graph or Markdown content.
- **Agent run:** an execution record. Completion is a claim and does not equal verification.
- **Verification evidence:** CI/runtime or other objective evidence associated with project work.
- **Active context:** the committed `{workspaceId, projectId}` currently being viewed. Both human-readable names must remain visible before consequential project actions.

Trace: `SRC`, `P`, `PUX`, `DOM`, `FE`.

## Confirmed requirements

1. A user can create a workspace. (`SRC`)
2. A workspace can contain zero or more projects; it is not required to contain a project immediately. (`SRC`, reconciled by `DOM`/`PUX`)
3. Every project belongs to exactly one workspace. (`SRC`)
4. Workspace membership controls access to all projects in that workspace. (`SRC`)
5. A project may connect to Git but remains a project without a repository connection. (`SRC`, `PUX`)
6. A project has a visual graph and Markdown-backed project knowledge. (`SRC`)
7. Personal workspaces must be supported before team collaboration is required. This does not itself require invitations or team administration in this delivery. (`SRC`, `PUX`, `BE`)
8. The domain must accommodate future team memberships and roles without rewriting project containment/ownership. (`SRC`)
9. Graphs, Markdown knowledge, repository connections, agent runs, and verification evidence are project-scoped concepts. (`SRC`)
10. Laravel is the SaaS backend/MCP control plane, React/TypeScript/React Flow is the visual frontend, and a separate local runner owns repository/Git/terminal/build/test/local-agent access. (`P`)
11. Objective evidence, not an agent completion claim, determines verified completion. (`P`)

The source does **not** yet confirm project creation behavior, role names, multi-user collaboration scope, lifecycle/destructive controls, project transfer, default navigation, storage/synchronization authority, repository cardinality, audit policy, or local capability grants.

## Reconciled semantic model

```text
User
  └── WorkspaceMembership ──> Workspace
                               └── Project
                                   ├── ProjectGraph
                                   ├── MarkdownKnowledge
                                   ├── RepositoryConnection(s)
                                   ├── AgentRun(s)
                                   └── VerificationEvidence
```

### Proposed invariants

These proposals are common to all five analyses but remain proposals until the decision register is confirmed.

1. `WorkspaceMembership` is a first-class, unique User–Workspace association; do not model directly user-owned projects or use `workspace.user_id` as the access shortcut. (`DOM`, `BE`, `QS`)
2. Workspace creation and creator membership creation are atomic. (`DOM`, `BE`, `QS`)
3. A project's workspace association is required and immutable through ordinary project updates. (`DOM`, `BE`)
4. Every project descendant and project-owned edge resolves to the same project and workspace. Cross-project or cross-workspace descendants/edges are invalid. (`DOM`, `BE`, `QS`)
5. Stable opaque identities are independent of names, slugs, email, Markdown paths, canvas positions, repository URLs, and lifecycle changes. (`DOM`, `BE`, `FE`, `QS`)
6. Every HTTP, Inertia, MCP, queue, callback, export, search, bulk, and local-runner entry point authorizes the current principal against the actual workspace/project at point of use and fails closed. (`BE`, `QS`)
7. `{workspaceId, projectId}` is the inseparable frontend context/cache key. Graph state, drafts, undo/redo, clipboard, selection, viewport, subscriptions, and pending requests cannot cross it. (`FE`, `QS`)
8. Semantic graph content is separate from layout and transient runtime state. (`P`, `DOM`, `FE`)
9. Stale responses/revisions are rejected; old graph state becomes inert while context changes. (`FE`, `QS`)
10. Repository credentials, bearer tokens, raw secrets, and unapproved local paths are excluded from Markdown, graph properties, Inertia payloads, normal audit metadata, and agent-visible context. (`DOM`, `BE`, `QS`)
11. Verification status derives from objective evidence, not `AgentRun.completed` or canvas state. (`P`, `DOM`)

## Required user journeys and visible states

The exact branching behavior is gated by the decisions below; these state distinctions are proposed quality requirements rather than added features.

1. **First entry:** authentication → workspace resolution → no-workspace state, authorized workspace destination, explicit chooser, or retryable/access failure.
2. **Workspace creation:** form → submitting → validation failure, uncertain/service failure, authorization failure, or created workspace with creator membership.
3. **Workspace context:** project summaries loading → true empty state, populated list, retryable failure, revoked/forbidden, or missing context.
4. **Project creation if confirmed:** form inside a visibly identified workspace → submitting → validation/failure or created project and canonical destination.
5. **Project navigation:** intent → unsaved-work handling → authorization/context resolution → graph loading → editable, read-only, conflict, unavailable, revoked, or retryable failure.
6. **Context switching:** workspace change resets/re-resolves project context; no project is carried across workspace boundaries. Browser Back/Forward and deep links use the same authorization and unsaved-work policy as switchers.
7. **Access change:** revocation/downgrade stops queued mutations, makes stale content inert, clears sensitive caches, and provides the confirmed recovery behavior without silently replaying edits.
8. **Repository, agent, and verification status:** displayed as separate facts; none implies another.

Every journey distinguishes loading, empty, ready, in-progress, success, validation error, access denied/revoked, not found, conflict, source/service failure, and unknown outcome. Recoverable forms preserve input. Error messages identify the affected workspace/project and a safe remedy without exposing raw internals. (`PUX`, `FE`, `QS`)

## Minimum implementation-neutral contracts — proposed

These are contract responsibilities, not endpoint names or database schemas.

1. **Authorized context summary:** stable workspace/project identities and names, canonical destination, current effective action capabilities, and lifecycle/version data needed by the client.
2. **Graph envelope:** workspace ID, project ID, semantic revision, semantic nodes/edges, and separately identified layout/version data.
3. **Mutation envelope:** full context, operation identity/idempotency where retries are possible, expected/base revision, accepted revision, and structured conflict outcome.
4. **Creation result:** field-safe validation errors or stable created identity plus canonical destination and honest provisioning status.
5. **Failure taxonomy:** unauthenticated, non-disclosing forbidden/missing as confirmed, validation, conflict, revoked/gone, source unavailable, and unexpected failure with a safe support correlation identifier.
6. **Revalidation:** changed revision, capability/membership change, deletion/lifecycle change, and connectivity/source status; transport remains deferrable.
7. **Collection contract:** explicit empty/partial state, stable ordering, and future search/pagination capability without implying all accessible objects were loaded.

Trace: `BE`, `FE`, `QS`.

## Quality and security acceptance baseline

Every later code-changing card must identify applicable threat/boundary rows from `.visodeto/analysis/workspaces-projects-quality-security.md`, use a dedicated worktree and isolated database/cache/queue/ports/runner resources, and receive independent same-card review by `quality-security`.

At minimum, applicable cards require objective evidence for:

- positive and negative two-workspace authorization, including guessed IDs and mismatched parent/child IDs;
- stale membership, point-of-use revocation, and deterministic TOCTOU/concurrency behavior;
- database constraints/transactions, idempotency, rollback/failure injection, and production-equivalent locking where relevant;
- frontend A → B → A race isolation, Back/Forward, revocation, payload/storage/cache leakage, accessibility, and error recovery;
- MCP/local-runner capability, expiry/revocation, replay, path traversal/symlink, and argument-construction boundaries when introduced;
- audit correlation and canary-based secret/metadata redaction when audit is introduced;
- exact lint, format, types, tests, and production build results from the isolated runtime.

An implementer summary, screenshot, coverage percentage, or passing happy path is not sufficient evidence. Trace: `QS`, `.visodeto/agents/review-policy.md`.

## Decision register

### Confirmed

| ID | Decision | Trace |
|---|---|---|
| C1 | Workspace is the tenant/access boundary and project container. | `SRC` |
| C2 | Workspace access is membership-derived. | `SRC` |
| C3 | Every project belongs to exactly one workspace. | `SRC` |
| C4 | Project identity is separate from optional repository connection. | `SRC`, `PUX` |
| C5 | Project knowledge consists of a visual graph and Markdown-backed knowledge. | `SRC` |
| C6 | Personal workspaces precede required team collaboration, while the model remains future-team/role compatible. | `SRC` |
| C7 | Agent completion does not replace verification evidence. | `P` |

### Proposed

| ID | Proposal | Why | Trace |
|---|---|---|---|
| P1 | First-class membership and workspace-owned projects, with no direct user-owned project shortcut. | Preserves the confirmed access boundary and future team compatibility. | `DOM`, `BE`, `QS` |
| P2 | Stable opaque public IDs; names are mutable labels and slugs, if any, are decorative aliases. | Prevents identity churn/enumeration and permits duplicate human names. | `DOM`, `BE`, `FE`, `QS` |
| P3 | Capability-oriented backend/client contract even if the UI presents simple named roles. | Avoids hard-coding unsettled role semantics into clients. | `PUX`, `BE`, `FE` |
| P4 | Reject stale writes with explicit review/reload rather than silent last-writer-wins. | Prevents hidden graph/knowledge/membership data loss. | `BE`, `FE`, `QS` |
| P5 | No cross-workspace transfer endpoint/UI unless separately confirmed; any future transfer is an atomic aggregate migration. | A foreign-key retarget can leak descendants, evidence, credentials, and jobs. | `DOM`, `BE`, `PUX`, `QS` |

### Open-blocking — user confirmation required

#### B1 — Delivery scope and membership surface

Choose whether this delivery is: (A) personal workspace/project flow with one active owner membership and no invitations/member UI; (B) personal flow plus read-only membership visibility; or (C) full multi-user invitations and role administration. Recommendation: **A**, while persisting/authorizing through membership records so future team support does not rewrite project ownership. This is a delivery-scope choice, not an MVP assumption. (`SRC`, `PUX`, `BE`, `QS`)

#### B2 — Personal workspace provisioning and multiplicity

Choose: (A) atomic automatic creation at registration; (B) lazy automatic creation on first authenticated use; (C) explained one-click creation; or (D) fully explicit normal workspace creation. Also decide whether a user may create multiple workspaces and whether “personal” is an immutable type or only an initial one-member state. Recommendation: **C**, allow multiple workspaces, and treat personal as an initial membership state rather than an immutable tenant type. (`SRC`, `PUX`, `DOM`, `BE`, `FE`, `QS`)

#### B3 — Initial authority, owner invariant, and revocation

Define which capabilities the initial personal member has, whether one or multiple owners may exist, who can transfer authority, and what happens on self-removal/account deletion. Recommendation: one presented `Owner` role backed by explicit capabilities; at least one active owner; atomic transfer; last owner cannot leave/delete the account without transfer or an explicit workspace closure flow; membership downgrade/removal revokes browser, queue, MCP, and runner authority at point of use. (`PUX`, `DOM`, `BE`, `FE`, `QS`)

#### B4 — Workspace/project creation and naming contract

Confirm that projects can be created explicitly without Git and choose required fields and duplicate-name behavior. Recommendation: explicit create actions; required trimmed human-readable `name`; optional `description`; repository connection is a later separate action; duplicate workspace names per user and duplicate project names per workspace are allowed but disambiguated by stable IDs/context. (`PUX`, `DOM`, `BE`, `FE`, `QS`)

#### B5 — Canonical storage and synchronization authority

Choose: (A) SaaS/cloud canonical project graph and Markdown; (B) local canonical; or (C) explicit per-project/hybrid authority. For the chosen model, classify which graph/Markdown/repository metadata, local paths, prompts/results, logs, and verification evidence may enter SaaS. Recommendation: no inferred default; this is a product, privacy, deployment, and conflict-model decision requiring explicit confirmation. Credentials remain secret and local paths local-only under every option unless separately approved. (`SRC`, `DOM`, `BE`, `PUX`, `FE`, `QS`)

#### B6 — Public identity, routing, and denial semantics

Choose canonical URL identity and foreign-object denial. Recommendation: immutable opaque public ID, optionally with decorative slug; nested workspace/project routes; foreign and nonexistent object access both return a uniform non-disclosing not-found response, while high-risk denials are audited separately. (`DOM`, `BE`, `FE`, `QS`)

#### B7 — Navigation, unsaved work, revisions, and recovery

Choose default landing, context switching with unsaved edits, stale-write handling, and post-revocation local recovery. Recommendation: remember the last accessible context and visibly identify it; never silently fall back from an inaccessible deep link; attempt/await save before switch, then offer Stay/Retry/Discard when save fails; reject stale revisions for review/reload; after revocation keep no readable tenant cache and permit export/recovery only if the confirmed storage/privacy model can do so without disclosing protected content. (`PUX`, `BE`, `FE`, `QS`)

#### B8 — Repository delivery scope and cardinality

Choose: (A) exclude repository connection implementation from this delivery while preserving the project-scoped relation; (B) zero-or-one repository per project; or (C) multiple project-scoped connections with an explicit primary/role. Also decide whether the same repository may bind to multiple projects/workspaces. Recommendation: **A** until the local/cloud and runner capability decisions are settled; do not expose a singular API field that prevents later multiplicity. (`SRC`, `PUX`, `DOM`, `BE`, `QS`)

#### B9 — Local runner/MCP authorization model

If repository or local execution is in this delivery, decide user-delegated versus service principal, local binding approval, read/write/execute capability separation, expiry, revocation, and queued work after membership changes. Recommendation: explicit local approval with project-scoped, short-lived, separately revocable read/write/execute grants; no grant may select arbitrary tenant IDs or paths. If local execution is excluded, record this as deferred. (`P`, `BE`, `QS`)

#### B10 — Lifecycle and destructive operations

Choose whether rename, archive, restore, leave, transfer, soft delete, hard delete, and bulk actions are in this delivery. If any are included, retention, cascade, restore, active-job, credential, evidence, audit, and sole-owner semantics must be specified. Recommendation: allow non-destructive rename only; exclude archive/delete/leave/transfer/bulk actions from this delivery. No hidden cascade behavior. (`PUX`, `DOM`, `BE`, `QS`)

#### B11 — Audit, sensitive metadata, visibility, and retention

Choose audited events, viewers/export, retention, legal deletion relationship, and classification of repository URLs, branches, local paths, commits, prompts/results, and logs. Recommendation: append-only server-generated audit for membership/authority changes, high-risk denials, repository/local mutations, and destructive operations; ordinary project cascades cannot remove it; credentials/raw content/local paths are excluded. Retention duration and user visibility require explicit product/privacy/cost confirmation. (`BE`, `QS`)

#### B12 — Accessible graph and mobile capability

Choose whether the equivalent non-spatial graph representation is a synchronized full editor or an inspect/navigation view, and whether mobile supports full editing, selected operations, or inspect-only. Recommendation: keyboard-accessible synchronized structured view with full semantic operation parity; mobile inspect/navigation only until full editing can meet the same accessibility and data-loss guarantees. (`FE`, `PUX`)

### Open-deferrable — confirm explicit exclusions

| ID | Decision | Recommended exclusion for this delivery | Trace |
|---|---|---|---|
| D1 | Team invitations/member administration | Exclude under B1-A; retain membership-ready domain. | `SRC`, `PUX`, `BE` |
| D2 | Project transfer between workspaces | No endpoint/UI; preserve future atomic aggregate transfer. | `PUX`, `DOM`, `BE`, `FE`, `QS` |
| D3 | Destructive lifecycle | Exclude under B10 except non-destructive rename. | `PUX`, `DOM`, `BE`, `QS` |
| D4 | Repository connection implementation | Exclude under B8-A while preserving a separate project-scoped concept. | `SRC`, `PUX`, `DOM`, `BE` |
| D5 | Collection thresholds, viewport persistence, and revalidation transport | Keep contracts extensible; decide from measured usage and later scope. | `FE` |
| D6 | Reference-template instantiation/update semantics | Exclude; not required by this feature. | `DOM` |

## Decision and execution gate

No implementation card may be created or started while any B1–B12 decision relevant to the confirmed delivery remains open. A user response must be recorded in this register with `USER` trace and must explicitly accept or change the proposed deferrals D1–D6.

After confirmation, `visodeto-lead` will:

1. change `decision_gate` to `confirmed` and record the confirmation source;
2. replace each applicable open item with a confirmed decision without erasing alternatives/trade-offs;
3. create bounded implementation cards with one specialist owner, explicit inputs/outputs and measurable criteria;
4. link actual dependencies to maximize safe parallelism;
5. require a dedicated worktree/project runtime and same-card independent `quality-security` review/evidence on every coding card.
