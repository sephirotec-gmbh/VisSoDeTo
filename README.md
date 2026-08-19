# ViSoDeTo

> **Build software visually. Let AI handle the code.**

ViSoDeTo is a visual planning and control layer for AI-assisted software development. It turns product ideas, requirements, architecture decisions, tasks, and implementation evidence into a structured project graph that humans can understand and AI coding agents can use.

## Product direction

ViSoDeTo is intended for people who want to build software with tools such as Claude Code, Codex, Hermes, or OpenClaw without coordinating the entire project through opaque chat and terminal histories.

The product will connect three responsibilities:

- **Humans** define goals, make decisions, and approve outcomes.
- **ViSoDeTo** structures context, visualizes dependencies, coordinates work, and verifies evidence.
- **AI agents** implement and test the code.

## Initial architecture

- Laravel 13 application and SaaS control plane
- React 19 + TypeScript + Inertia 3 frontend
- React Flow via `@xyflow/react`
- PostgreSQL for application and project data
- Redis for cache and queued work
- Laravel MCP for the product's agent interface
- Laravel Boost for agent-assisted development of ViSoDeTo
- Laravel Sail / Docker for local development

A separate local runner will later connect ViSoDeTo to repositories, Git, terminals, builds, tests, Claude Code, Codex, Hermes, and OpenClaw on a user's machine.

## Local development

### Requirements

- Docker Desktop
- Docker Compose

No host PHP, Composer, Node.js, PostgreSQL, or Redis installation is required.

### First-time setup

Install PHP dependencies without requiring host PHP or Composer:

```bash
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$PWD":/app \
  -w /app \
  composer:2 composer install --no-interaction --prefer-dist

cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm ci
```

### Start the application

```bash
./vendor/bin/sail up -d
./vendor/bin/sail npm run dev
```

Open <http://localhost>.

### Stop the application

```bash
./vendor/bin/sail down
```

### Quality checks

```bash
./vendor/bin/sail composer ci:check
./vendor/bin/sail npm run build
```

## Repository

<https://github.com/sephirotec-gmbh/VisSoDeTo>
