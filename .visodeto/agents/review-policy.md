---
id: quality-security-review-policy
type: policy
status: active
reviewer: quality-security
---

# Review policy

## Default lifecycle

Code-changing cards use same-card review:

```text
ready → running → review → done
                    ↘ changes requested → ready
```

The implementer calls `kanban_request_review` with a concise summary and structured evidence. `quality-security` either calls `kanban_complete`, calls `kanban_request_changes` with actionable findings, or calls `kanban_block` only for a genuine external dependency or user decision.

Do not combine same-card review with a separate downstream review card unless the task graph explicitly chooses that alternative in advance.

## Required handoff

- acceptance criteria and result for each
- changed files
- tests, linters, type checks, and builds run
- exact pass/fail outcomes
- migration or compatibility impact
- security and authorization impact
- unresolved limitations
- branch, worktree, commit, or pull-request reference where applicable

## Review gates

- No hardcoded credentials, tokens, or private data.
- Workspace and tenant boundaries are tested where relevant.
- Inputs are validated and authorization is explicit.
- MCP and local-runtime mutations follow least privilege.
- Relevant automated tests pass.
- The implementation matches confirmed `.visodeto` intent.
- User-visible behavior has an inspectable preview or equivalent evidence when practical.

The reviewer must not approve its own implementation. Non-blocking suggestions are recorded separately from security or logic blockers.
