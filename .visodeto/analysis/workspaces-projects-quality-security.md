---
id: workspaces-projects-quality-security
kind: quality-security-analysis
feature: feature-workspaces-projects
status: decision-gate
owner: quality-security
---

# Workspaces and projects: quality and security gates

## Scope and review posture

This analysis defines gates for `.visodeto/nodes/features/workspaces-and-projects.md`. It does not approve or implement the feature. The source requirement confirms only that workspace membership controls access to projects, personal workspaces precede required team collaboration, and the model must permit future team roles without replacing project ownership. Role semantics, lifecycle semantics, repository cardinality, and deployment/storage boundaries remain undecided.

The implementation must fail closed: a missing workspace context, stale membership, unresolved policy, malformed identifier, or untrusted local-runner/MCP request must never fall back to broader access.

## Assets, actors, and trust boundaries

Protected assets:

- Workspace and project records, graphs, Markdown knowledge, repository metadata, agent runs, and verification evidence.
- Membership and future role assignments.
- Repository credentials, local paths, runner capabilities, and command results.
- Audit evidence used to assert who changed which resource and under which workspace context.

Actors:

- Authenticated user with current membership.
- Authenticated user without membership, including a former member with a live browser/session.
- Future workspace administrator/member roles.
- Web/Inertia client and browser history/cache.
- Laravel HTTP and policy layer.
- Local MCP client and separate local runner.
- Queue workers, concurrent requests, and delayed jobs using previously valid context.

Critical trust boundaries:

1. Browser to Laravel HTTP routes and Inertia responses.
2. Authenticated identity to current workspace membership and resource authorization.
3. Workspace to project and every project-owned descendant.
4. Cloud control plane to local MCP/runner execution.
5. Current authorization state to sessions, queues, caches, and long-running operations.
6. Mutating operation to durable audit evidence.

## Prioritized threat model

Priority meanings: P0 blocks release because exploitation can cross a security or destructive boundary; P1 blocks the affected coding card because it can expose data, corrupt state, or invalidate evidence; P2 is a material regression/operability risk that must be tested before feature completion.

| Priority | Threat | Failure mode / impact | Required control | Objective verification |
|---|---|---|---|---|
| P0 | Cross-workspace object access | User substitutes a workspace, project, graph, knowledge, repository, run, or evidence identifier belonging to another workspace and reads or mutates it. | Every query and policy must bind the child resource to the authorized workspace; route-model binding alone is insufficient unless scoped and policy-checked. | Automated matrix tests for every read/write/delete endpoint and MCP tool: own workspace succeeds; foreign workspace returns the chosen non-disclosing denial; mixed parent/child IDs fail; database is unchanged. Inspect controller/tool queries and policy calls, not only responses. |
| P0 | Authorization bypass through alternate entry points | HTTP policy exists but MCP, queue job, bulk endpoint, export, websocket, or local runner omits it. | One explicit authorization contract shared or equivalently enforced at every entry point; no client-side-only enforcement. | Inventory all routes, tools, jobs, events, and commands touching workspace-owned records. Negative tests invoke each entry point directly without the UI. Reviewer compares inventory to code registrations. |
| P0 | Stale membership/session access | Removed or downgraded member retains access via live session, cached Inertia props, queue payload, signed URL, token, or long-running runner operation. | Authorization re-evaluated at the point of each protected read/mutation and before irreversible local execution; membership changes invalidate or make caches/context unusable. | Two-client automated/integration test: client A is authorized, client B removes/downgrades A, then A retries reads, writes, queued work, MCP calls, and runner mutation. All fail without requiring logout. Inspect cache keys, queue payloads, and runner capability expiry/revocation. |
| P0 | Unauthorized destructive operation | Project/workspace deletion, disconnect, ownership transfer, bulk mutation, or force-delete affects data or local resources outside the authorized scope. | Explicit high-risk permission; confirmation/re-authentication policy as decided; transactionally scoped cascade; idempotency; local deletion is separate and least-privileged. | Negative authorization tests, cancellation/no-op test, repeated-request test, cascade/orphan assertions, failure-injection rollback test, and audit-event assertion. Manual UI check confirms resource name/scope and consequence text before confirmation. |
| P0 | Cloud-to-local capability escape | Project or repository metadata causes MCP/runner to access arbitrary local paths, execute unapproved commands, or act for a different workspace/project. | Project-scoped, short-lived, least-privilege capabilities; canonical path allowlist; no raw shell construction; explicit mutation approval; server-provided tenant context is not trusted without local binding verification. | Adversarial MCP/runner tests for `../`, symlink escape, absolute paths, shell metacharacters, capability replay, project-ID substitution, expired/revoked grants, and read-to-write escalation. Inspect command invocation and path canonicalization. |
| P1 | Identifier enumeration and existence oracle | Sequential or guessable IDs, status/body/timing differences, autocomplete, logs, or error payloads reveal foreign workspaces/projects. | Opaque public identifiers where appropriate and consistent non-disclosing denial behavior; validation must not disclose existence before authorization. | Automated enumeration test compares status, response schema/body, redirects, and bounded timing for unknown versus foreign IDs across HTTP and MCP. Manual browser/network check confirms foreign names/IDs never appear in payloads. |
| P1 | Membership race / time-of-check-to-time-of-use | Membership is removed after authorization but before commit, queue execution, or local side effect. | Transaction/locking or versioned authorization snapshot with a final authorization check immediately before side effect; define ordering with membership changes. | Deterministic concurrency test using barriers: pause mutation after initial check, revoke membership, resume, then assert no protected DB/local side effect. Repeat for destructive and runner operations. |
| P1 | Confused-deputy parent/child mismatch | Authorized workspace ID is combined with a project or repository connection from another workspace. | Composite/scoped lookup and database constraints that preserve the workspace relationship; never authorize only the URL parent. | Property/data-driven tests generate mismatched parent-child tuples for all nested resources. Inspect foreign keys, unique constraints, and lookup predicates. |
| P1 | Frontend context leakage | Shared Inertia props, prefetch, client cache, React state, browser history, error telemetry, or optimistic updates retain data after switching workspace or losing membership. | Minimal authorized props; cache/state keys include workspace identity; sensitive project state is cleared on switch, logout, 403/404, and membership revocation; no secrets/local paths in props or telemetry. | Browser automation switches A → B and performs back/forward, reload, failed optimistic mutation, and simulated revocation; DOM, React state exposed through behavior, network responses, local/session storage, and console contain no A data while in B. Reviewer inspects serialized Inertia payloads. |
| P1 | Audit evidence forgery or incompleteness | Mutations lack actor/workspace/project/action/outcome correlation, log only success, can be client-supplied, or are deleted with the project. | Server-generated immutable/append-only audit record with authenticated actor/service, target, workspace, action, outcome, timestamp, request/correlation ID, and safe metadata; retention policy must be decided. | Tests assert one correlated event for success and denied/high-risk attempts as required, no event for rolled-back false success, and actor/tenant fields cannot be overridden by input. Reviewer queries stored evidence and traces it to request/job/runner logs. |
| P1 | Unsafe repository metadata exposure | Credentials, remote URLs with embedded tokens, private local paths, or command output leak through API, Inertia, logs, audits, exports, or errors. | Store references/secrets separately; redact credentials and sensitive paths; whitelist returned fields. | Seed canary secrets/tokens/path fragments, exercise success and error paths, then scan HTTP payloads, Inertia HTML/JSON, logs, audits, queue payloads, and exports. Any canary occurrence fails. |
| P1 | Concurrent creation/update/deletion corruption | Duplicate personal workspaces, duplicate membership, lost updates, project created during workspace deletion, or repository connected twice. | Database uniqueness/foreign-key constraints, transactions, optimistic versioning or locking, and idempotency keys where retries are expected. | Parallel tests with independent DB connections and synchronization barriers; assert one invariant-preserving result, deterministic conflict response, no orphans, and retry safety. Inspect migration constraints rather than accepting application checks alone. |
| P2 | Regression in authentication/settings flows | Workspace bootstrapping or global middleware breaks registration, login, verification, password/passkey/2FA, profile deletion, or dashboard access. | Workspace setup must have explicit failure semantics and avoid changing unrelated authentication guarantees. | Existing full backend suite plus focused registration/login/verification/profile-delete tests with workspace setup success and injected failure. Verify unauthenticated and unverified route behavior. |
| P2 | Performance-driven authorization bypass | N+1 fixes, caches, eager loading, search, or pagination accidentally remove tenant predicates. | Tenant-aware cache keys and scoped query builders; performance work cannot bypass policy checks. | Query/cache tests with identical project names across tenants; pagination/search never mix tenants. Review query log for scope and establish bounded query-count tests where useful. |
| P2 | UI race and stale optimistic state | Rapid workspace switching or overlapping requests renders/mutates the prior project in the new context. | Abort/ignore stale responses using workspace-scoped request identity; mutations carry explicit project/workspace identity checked server-side. | Browser automation delays A response, switches to B, releases A response, and asserts A is neither rendered nor used by subsequent mutation. |

## Critical-boundary verification matrix

| Boundary | Automated evidence required | Manual/inspection evidence required |
|---|---|---|
| Identity → membership | Policy/feature tests for member, non-member, removed member, downgraded member, unauthenticated, unverified, and malformed/unknown identifier. | Reviewer traces each protected route/tool/job to a server-side authorization decision and confirms deny-by-default behavior. |
| Workspace → project descendants | Factory matrix covering two users, two workspaces, same-named projects, and each descendant type; mixed-ID negative cases. | Reviewer inspects schema constraints, scoped bindings/query predicates, serialization, and exception handlers for existence leaks. |
| Membership mutation → active operations | Multi-client stale-session tests and barrier-controlled TOCTOU tests. | Reviewer inspects session/cache invalidation, queued-job reauthorization, and capability revocation design. |
| SaaS → MCP/local runner | Contract tests for authenticated context, capability scope/expiry/revocation, path traversal, symlink, replay, and mutation approval. | Reviewer inspects registered MCP tools, their input schemas, local path canonicalization, subprocess argument construction, and audit correlation. No implementer screenshot or claim substitutes for this inspection. |
| Backend → Inertia/browser | Browser tests for switch, back/forward, reload, prefetch, revocation, stale response, and error path. | Network/storage/console inspection with canary data; inspect shared props and cache keys. |
| Mutation → database/local side effect | Transactional success/failure/idempotency/concurrency tests and invariant queries. | Reviewer inspects migration constraints and independently checks database state after forced failures. |
| Mutation → audit evidence | Tests for authoritative actor/tenant/target/action/outcome/correlation fields and redaction. | Reviewer queries raw audit records and correlates at least one HTTP or MCP mutation end-to-end. |

## Objective acceptance evidence for eventual coding cards

Every coding card must state which threat rows and boundary rows it affects. A card is not reviewable without all applicable evidence below.

### Required implementer handoff

- Acceptance-criterion table with a concrete result for each criterion.
- Exact changed files and migration/compatibility impact.
- Exact commands and exit results for focused tests and the repository gates: `composer test`, `npm run lint:check`, `npm run format:check`, `npm run types:check`, and `npm run build`, executed in the card's isolated worktree/runtime.
- Focused authorization test names and counts, including negative cross-workspace cases.
- Database engine used for concurrency and constraint tests; SQLite-only evidence is insufficient where production locking/constraint behavior differs.
- For frontend changes: inspectable preview plus browser/network evidence for workspace switching and leakage checks.
- For MCP/runner changes: registered tool/input-schema inventory, adversarial test results, capability/path boundary evidence, and redacted audit correlation.
- For migrations/destructive changes: forward migration, constraint introspection, rollback or documented irreversible path, seeded-data preservation/cascade evidence, and retry/failure-injection results.
- Explicit unresolved limitations. A passing summary without raw command output or inspectable artifacts is not evidence.

### Evidence the independent reviewer must produce or inspect

The quality-security reviewer must independently:

1. Read the actual diff and relevant callers, policies, route/tool registrations, migrations, serializers, shared Inertia props, queue jobs, and local-runner boundary code.
2. Re-run applicable focused negative tests and at least the non-mutating repository checks; independently run the full gates before approval where the card changes shared domain/auth/middleware/schema behavior.
3. Create or inspect a two-workspace fixture and directly attempt one authorized, one foreign, one stale-membership, one mismatched parent/child, and one malformed/unknown-ID path for every changed entry-point class.
4. Inspect database state after denied, concurrent, retried, and failure-injected mutations; response status alone does not prove safety.
5. Inspect serialized Inertia/network payloads and browser storage/state for frontend context leakage when frontend context changes.
6. Inspect raw audit rows/log correlation and redaction using canary values; screenshots or implementer assertions do not suffice.
7. For MCP/local execution, invoke adversarial inputs and inspect path/argument/capability enforcement. Never approve based only on mocked controller tests.
8. Distinguish blocking security/logic defects from non-blocking suggestions and fail closed when evidence is missing or ambiguous.

No approval may rely only on coverage percentage, snapshots, happy-path tests, a pull-request description, generated reports without source artifacts, or the implementer's claim that a policy is applied.

## Same-card independent review requirement

Every code-changing card must use this lifecycle on the same card:

1. Implementer works in a dedicated worktree with isolated database, cache, queue, ports, and local-runner resources.
2. Implementer completes verification and calls `kanban_request_review`, naming `quality-security` and attaching the required handoff evidence.
3. The independent `quality-security` run reviews the same task and actual worktree/commit. It must not edit or approve its own implementation.
4. Reviewer calls `kanban_complete` only after independently reproducing applicable evidence; calls `kanban_request_changes` for correctable defects; calls `kanban_block` only for a genuine external dependency or user decision.
5. Re-review confirms requested corrections and reruns regression/security checks. Detached QA/review cards must not be created for these coding cards because they weaken provenance and can let the implementation card appear complete before review.

Card acceptance criteria must explicitly include the applicable rows from this document, not merely link to "security review" in general.

## Blocking ambiguities requiring user decisions

These decisions must be recorded before implementation cards whose tests depend on them are created. Recommended defaults are proposals, not confirmations.

1. **Personal workspace provisioning** — Is it created atomically at registration, lazily on first authenticated use, or only by explicit user action? Recommended: explicit provisioning semantics with atomic registration only if registration must never succeed without the workspace. Tests need the expected outcome when workspace creation fails or is retried.
2. **Initial and future roles** — Which roles exist now, and who may create/read/update/delete workspaces, projects, memberships, repository connections, graphs, knowledge, runs, and evidence? Recommended: define a permission matrix now even if the first personal workspace has one owner role; deny unspecified actions.
3. **Last-owner and membership-change rules** — May the last owner leave or be removed? Do demotions/removals revoke active sessions, queued work, and local capabilities immediately? Recommended: prevent ownerless workspaces and require point-of-use revocation.
4. **Workspace/project lifecycle** — Are rename, archive, soft delete, restore, hard delete, transfer, and bulk delete in scope? What are retention and cascade semantics for graph, Markdown, connections, runs, evidence, and audit records? Recommended: exclude unspecified destructive operations; never infer cascade behavior.
5. **Denial semantics and public identifiers** — Should foreign and nonexistent resources both return 404, and are opaque public IDs required? Recommended: uniform non-disclosing 404 for object access while separately auditing denial, subject to product/API conventions.
6. **Repository cardinality and ownership** — Can one project connect to one or many repositories, and can the same repository connect to multiple projects/workspaces? Recommended: decide before schema work; tests and uniqueness constraints depend on it.
7. **Local-only versus cloud-synchronized data** — Which fields/artifacts live in SaaS, which remain local, and what synchronization/conflict model applies? Recommended: classify each asset and prohibit cloud receipt of repository credentials, raw local paths, or content not explicitly synchronized.
8. **Local runner/MCP authorization model** — How is a SaaS project bound to a local repository, who approves read/write/execute capabilities, how long do grants live, and how are they revoked? Recommended: explicit local approval with project-scoped, short-lived capabilities and separate read/write/execute grants.
9. **Concurrent update contract** — Should conflicting edits be rejected via versioning, serialized, or last-write-wins? Recommended: reject stale writes for graph/knowledge/membership/destructive changes; tests need a deterministic expected result.
10. **Audit scope, visibility, and retention** — Which reads, denials, membership changes, repository/local actions, and destructive attempts are recorded; who can view them; how long are they retained; can workspace deletion remove them? Recommended: immutable server-generated records for membership, authorization denials at high-risk boundaries, repository/local mutations, and destructive operations, retained outside ordinary project cascades.
11. **Sensitive metadata classification** — Are repository URLs, branch names, local paths, commit metadata, agent prompts/results, and verification logs considered workspace-visible, admin-only, local-only, or secret? Recommended: classify before serializers/audits are designed; credentials are never workspace payload data.
12. **Membership feature scope** — The source says personal workspaces precede required team collaboration but also states membership controls access. Must the first implementation expose multi-user membership changes, or only model a single-owner membership for future use? Recommended: explicitly bound the first delivery while still testing authorization through membership records rather than direct project ownership.

Until these are confirmed, quality-security can define gates but cannot produce reliable expected outcomes for the affected behavior. Implementation must not silently choose defaults.

## Regression baseline and release gate

Before the first coding card, capture the clean baseline for backend tests, PHP static analysis/style, frontend lint/format/types, and production build in the prescribed isolated environment. Each coding card must show no new failures against that baseline. The final feature gate additionally requires:

- Full backend and frontend repository checks pass.
- All P0 and applicable P1 threat tests pass on the production-equivalent database/runtime.
- Route/tool/job inventory has no unreviewed workspace-owned entry point.
- Two-workspace browser smoke test and stale-membership test pass.
- MCP/local-runner adversarial suite passes if those boundaries are introduced.
- Migration and rollback/restore evidence matches the confirmed lifecycle contract.
- Audit and redaction evidence is independently inspected.
- No open-blocking decision remains and the delivered scope matches the confirmed specification.
