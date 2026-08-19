---
id: visodeto
type: product
status: active
title: ViSoDeTo
claim: Build software visually. Let AI handle the code.
repository: https://github.com/sephirotec-gmbh/VisSoDeTo
---

# ViSoDeTo

## Problem

Software development through AI chats and CLI terminals becomes difficult to understand and control as a project grows. Requirements, decisions, implementation work, and verification evidence are scattered across transient conversations and command output.

## Product vision

ViSoDeTo provides a visual, structured, and verifiable foundation for AI-assisted software development. People plan and control software graphically while their selected AI coding agents implement the code.

## Target users

People and teams using Claude, ChatGPT, Claude Code, Codex, Hermes, OpenClaw, or compatible agents who do not want to coordinate complete software projects through source code and terminal histories alone.

## Confirmed principles

- ViSoDeTo is agent- and model-independent.
- Project knowledge is Markdown-centered and machine-readable.
- Graph layout and runtime state use structured storage.
- AI agents report completion; ViSoDeTo verifies evidence before work is considered complete.
- The product contains workspaces, and each workspace contains projects.
- Laravel 13 is the SaaS backend and MCP control plane.
- React, TypeScript, and React Flow power the visual frontend.
- A separate local runner will provide repository, Git, terminal, build, test, and local-agent access.
