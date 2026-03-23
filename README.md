# Student management (Laravel)

## Overview

A web application for running student records, class enrollment, attendance, and grades in an academic setting. Staff and students sign in through the same entry point; the app routes each person to the right dashboard and data based on their role.

## How it works

The UI is built with **Laravel** and **Livewire**. Authentication uses **separate guards** (admin, student, instructor, program chair) so only one role is active per session. Middleware on each route decides which screens and actions are available. Optional **face verification** (a small local Python service) can strengthen attendance when enabled in configuration.

## Roles & access

| Role | Typical access |
|------|----------------|
| **Admin** | Institution-wide student directory, notifications, and student detail views. |
| **Student** | Personal portal: enrollments, published grades, adviser info, and attendance check-in (location / optional face checks per your settings). |
| **Instructor** | Students they advise or teach: classes, enrollments, grading workflow, and shared student detail views with admins. |
| **Program chair** | Oversight for their program(s): instructor roster, pending enrollments and grades that need approval within the program. |

Everyone with a role can open the **home dashboard** (scoped to what they are allowed to see) and **account settings**; everything else follows the table above.

## Key features

- Student profiles, sections, and programs  
- Subject enrollment and term-based grades (with program-chair approval where configured)  
- Attendance marking with geofencing and optional identity verification  
- Adviser assignments and instructor workflows  
- Notifications and bulk student import (e.g. CSV) for admins  
- Profile and password updates per role  

## Requirements

- PHP 8.2+, [Composer](https://getcomposer.org/)
- Node.js (for Vite / Tailwind)

## Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve
```

Configure `.env` for your database and mail. For optional face verification when marking attendance, run the service under `python-face-service/` and set `FACE_VERIFY_*` in `.env`.

## Scripts

- `scripts/tunnel-cloudflare.sh` — Cloudflare quick tunnel to a local URL (`LOCAL_URL`, default `http://127.0.0.1:8000`).
- `scripts/run-with-cloudflare.sh` — start the app and a tunnel (`RUN_COMMAND`, `LOCAL_URL`).
- `scripts/tunnel-ssh.sh` — SSH port forwarding (see env vars in the file).

Requires `cloudflared` or `ssh` on your `PATH` as appropriate.


## Jules :>