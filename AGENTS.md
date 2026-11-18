# Project: Verified Real Estate & Construction Hub (Abuja, Nigeria)

## Start of every session
1. Read docs/ai/PROJECT.md (scope and architecture rules).
2. Read docs/ai/PROGRESS.md (current focus, checklist, handoff notes).
3. Skim docs/ai/DECISIONS.md before changing anything architectural.

## End of every session
- Tick completed items and update "Current focus" and "Handoff notes" in PROGRESS.md.
- Append any new decision to DECISIONS.md with a one-line reason.
- Run the tests and note the result in the handoff notes.
- Do not start items beyond the current focus unless asked.

## Stack
Laravel (latest stable), Livewire, Alpine.js, Tailwind, PostgreSQL, Docker Compose,
GitHub Actions. Deploy target: single VPS (Docker Compose + Caddy), later AWS/GCP/K8s.

## Rules
- Stateless containers: file uploads go to S3-compatible storage (Cloudflare R2), never local disk.
- Secrets only via environment variables. Never commit .env.
- Roles: super_admin, company, professional (lawyer/architect/engineer), verifier, customer.
  Enforce with Laravel policies; add tests for every policy.
- Verified-listing workflow is the core feature: statuses pending, in_review, verified, rejected;
  per-listing checklist; record who verified and when; keep an audit log.
- Mobile-first, light pages, minimal JS, server-rendered for SEO.
- WhatsApp contact button (wa.me link with the listing pre-filled) on every listing and service page.
- Phase 1 scope only: listings + filters, partner profiles, consultation/inspection requests,
  fees and verification-process pages, blog. No chat, no payments yet.
- Write tests (Pest or PHPUnit) for each feature; run them before declaring done.

## Reporting rules
- After editing vite.config.js, package.json, or any CSS, run the Node 22 build and paste the result. A passing PHP test suite does not verify front-end assets.

<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>


