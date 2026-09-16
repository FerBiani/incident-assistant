# Incident Assistant

AI-assisted incident management and investigation built with **Laravel AI SDK**.

This project was created as a hands-on environment for exploring AI agents and AI-assisted software development with Laravel.

It combines traditional incident management with AI-powered triage and a conversational assistant for technical investigations.

## Features

* Project and incident management
* Incident severity, status tracking, and investigation notes
* AI incident triage with summaries, probable causes, and recommended actions
* Incident reanalysis
* Conversational AI assistant with persistent conversation history
* Streaming responses with SSE
* AI tool calling for related incident search
* Human approval for AI tool actions

## Tech Stack

* PHP 8.3+
* Laravel 13
* Laravel AI SDK
* Laravel Sail
* Laravel Boost
* Vue 3
* Inertia.js
* TypeScript
* Tailwind CSS
* MySQL
* Pest

## Getting Started

Clone the repository and install the dependencies:

```bash
git clone https://github.com/FerBiani/incident-assistant.git
cd incident-assistant

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Start the application with Laravel Sail:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm run dev
```

Start the queue worker used by the AI analysis:

```bash
./vendor/bin/sail artisan queue:work
```

The project was tested using **Ollama** with **qwen3:4b-instruct**, but any provider and model supported by the Laravel AI SDK can be used.

## Development

The project follows a lightweight **Spec-Driven Development (SDD)** workflow. Each feature is defined through a specification, an implementation plan, and an ordered task list:

```text
.specs/
├── product.md
├── 001-projects-incidents/
│   ├── spec.md
│   ├── plan.md
│   └── tasks.md
└── 002-incident-assistant/
    ├── spec.md
    ├── plan.md
    └── tasks.md
```

**Laravel Boost** is used to provide project-specific context and development guidelines to AI coding agents.
