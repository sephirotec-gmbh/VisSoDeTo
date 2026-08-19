---
id: agent-operating-model
type: policy
status: active
applies_to: [visodeto]
---

# ViSoDeTo agent operating model

## Sources of truth

- Bot group chats are for discussion and proposals.
- `.visodeto/` contains confirmed product intent, requirements, decisions, and policies.
- Hermes Kanban board `visodeto` owns executable task lifecycle and handoffs.
- Git and GitHub own code history.
- CI and runtime checks own verification evidence.

A chat statement is not an approved requirement or task. Only `visodeto-lead` may convert confirmed group outcomes into `.visodeto` changes and Kanban cards. Material product, cost, permission, privacy, deployment, or destructive decisions escalate to `@user`.

## Roles

- `visodeto-lead` orchestrates; it does not implement product code.
- Every Kanban card has one assignee.
- Implementers work only inside the task workspace.
- `quality-security` independently gates code-changing work.
- A bot may propose follow-up cards but must not silently expand scope.

## Workspaces

- Code-changing cards use `worktree` or `--project visodeto`; never edit shared `main` concurrently.
- Shared `dir:` workspaces are reserved for explicitly coordinated documentation work.
- `scratch` is for disposable research only.
- Workers must not commit secrets, `.env`, tokens, credentials, raw PII, or generated caches.

## Completion evidence

An implementation claim is not proof. A handoff must include changed files, commands/tests run, results, known limitations, and relevant decisions. Builds, tests, acceptance criteria, security checks, and CI determine verified status.

## Autonomy boundary

The fleet operates at controlled implementation autonomy: it may specify, decompose, implement in isolated worktrees, test, and request review. Merging to protected branches, production deployment, destructive operations, billing changes, and security-boundary changes require explicit user approval.
