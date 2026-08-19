---
id: feature-workspaces-projects
type: feature
status: planned
title: Workspaces and projects
priority: foundational
---

# Workspaces and projects

## Goal

Users organize their software work in workspaces. Every software project belongs to exactly one workspace.

## Initial domain model

```text
User
  └── Workspace membership
        └── Workspace
              └── Project
                    ├── Project graph
                    ├── Markdown knowledge
                    ├── Repository connections
                    ├── Agent runs
                    └── Verification evidence
```

## Initial requirements

- A user can create a workspace.
- A workspace can contain multiple projects.
- A project belongs to one workspace.
- Workspace membership controls access to its projects.
- A project may connect to a Git repository.
- A project has a visual graph and Markdown-backed project knowledge.
- Personal workspaces must be supported before team collaboration is required.
- The domain model must allow team workspaces and roles to be added without rewriting project ownership.

## Open decisions

- Whether every new user receives a personal workspace automatically.
- Workspace roles and permission boundaries.
- Whether a project may connect to multiple repositories.
- Local-only versus cloud-synchronized projects.
