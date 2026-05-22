# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## Working Dynamic (READ FIRST)

This project follows a **local-first, validate-then-push** dynamic. Every Claude session in this repo (any terminal, any machine) must follow this flow without exception.

### The Flow

```
1. Edit locally  →  2. User validates in Live Server  →  3. Commit & push to feature branch  →  4. Open PR to main  →  5. After merge, GitHub Actions deploys to Hostinger
```

### Rules

1. **Never work directly on `main`** unless the user explicitly asks. Always create a feature branch from updated `main`:
   ```
   git checkout main
   git pull origin main
   git checkout -b feat/<descriptive-name>
   ```
   Naming convention: `feat/<feature>`, `fix/<bug>`, `chore/<task>`.

2. **Apply changes locally only.** All file edits happen in `C:\Users\Leandro Santos\rbb-automacao` (this working tree). Do NOT commit, do NOT push, do NOT open PRs unless the user explicitly approves.

3. **User validates each change in Live Server before any commit.** The user runs the project via VS Code Live Server extension (`Open with Live Server` on `index.html`, serves at `http://127.0.0.1:5500`). Wait for the user to confirm visual approval before staging files.

4. **Only commit when the user says so.** When the user says "agora sobe", "pode commitar", "manda pro git", or equivalent, then:
   - `git status` and `git diff --check` first
   - Stage explicit files (avoid `git add .` or `git add -A`)
   - Create a commit with a short, objective message in Portuguese
   - Push the branch with `git push origin <branch>`
   - Provide the PR URL: `https://github.com/oleandrs/rbb-automacao/pull/new/<branch>`

5. **PR merge triggers automatic deploy.** After the user merges the PR on GitHub, the workflow at `.github/workflows/deploy-hostinger.yml` runs on push to `main`, deploys via FTP to Hostinger's `public_html` using `lftp`. The site at `rbbautomacao.com.br` updates within minutes.

6. **If deploy fails:** Direct the user to GitHub Actions logs. Do NOT touch Hostinger files directly. Diagnose from the workflow output and propose a targeted fix.

### Hard No-Gos

- Do NOT commit `smtp-config.local.php` — it lives only on the Hostinger server with real credentials. It is in `.gitignore` and explicitly excluded from the deploy workflow.
- Do NOT put SMTP passwords, FTP credentials, or GitHub tokens in any committed file.
- Do NOT alter GitHub Actions secrets (`HOSTINGER_FTP_HOST`, `HOSTINGER_FTP_USERNAME`, `HOSTINGER_FTP_PASSWORD`) without explicit user authorization.
- Do NOT create a `public_html/` folder inside the repo — the deploy mirrors the repo root into Hostinger's `public_html`. Nesting would cause `public_html/public_html/`.
- Do NOT use `git reset --hard`, `git push --force` to `main`, or `git commit --amend` on shared history.
- Do NOT delete pages, images, or PHP form files without confirming with the user.

### Local Preview Setup (User Side)

The user previews changes with VS Code Live Server:

1. Open the folder `C:\Users\Leandro Santos\rbb-automacao` in VS Code
2. Install the **Live Server** extension (author: Ritwick Dey) if not installed
3. Right-click `index.html` → "Open with Live Server" → opens at `http://127.0.0.1:5500`
4. Saves auto-reload the page

Note: Live Server is static-only. PHP forms (`send-contact.php`, `send-careers.php`) won't execute locally — they only work after deploy. For local PHP testing, use `php -S localhost:8000` (see SMTP section below).

### Branch Status Check (Run Before Any Edit Session)

```
git status
git branch --show-current
git log --oneline -5
```

If on `main` and tree is clean, create a new feature branch before editing. If on an existing feature branch, confirm with the user whether to continue on it or start a new one.

---

## Project Overview

This is the institutional website for RBB Automacao, an industrial automation company based in Rio de Janeiro. The site is a static HTML website with PHP backend services deployed on traditional web hosting (no Node.js, no build tools).

- Stack: HTML5, CSS3, JavaScript (vanilla), PHP 7.4+
- Deployment: Traditional web hosting (files deployed to public_html)
- Key Purpose: Company landing page, service showcase, contact/careers forms with SMTP email delivery

## Architecture and Code Organization

### Directory Structure

The main files include:
- index.html - Home page (hero, about, services)
- fale-conosco.html - Contact form page
- trabalhe-aqui.html - Careers/job application page
- blog-*.html - Blog articles (CLP, data, dashboard, IHM, panels)
- projetos.html - Projects page (under maintenance)
- treinamentos.html - Training page (under maintenance)
- obrigado.html - Thank you/status confirmation page
- send-contact.php - Handles contact form submissions
- send-careers.php - Handles careers form submissions
- smtp-mailer.php - Shared SMTP client (low-level email implementation)
- smtp-config.local.example.php - Template for local SMTP config

The assets folder contains:
- assets/css/ - Four stylesheets (global.css, components.css, pages.css, responsive.css)
- assets/js/ - main.js (all interactivity)
- assets/img/ - Organized images (banners, brands, content, icons, logos, process, services, social)

No package.json, no build scripts, no Node.js dependencies.

### Key Architectural Components

#### 1. Client-Side Interactivity (assets/js/main.js)

All JavaScript runs in this single file. Key behaviors:

- Mobile Menu: Hamburger toggle with close-on-nav-click and Escape key support (breakpoint: 1180px)
- Scroll Behaviors: Header hides when scrolling down past 120px threshold (desktop only)
- Lazy Reveal: IntersectionObserver-based entrance animations for .reveal elements (threshold 0.15)
- Active Section Highlighting: Smooth scroll tracking updates .is-active class on nav links matching current section
- Form Validation: Client-side validation for all .contact-form fields; displays inline error messages; prevents submit on invalid data
- Dynamic WhatsApp Button: Floating action button appended to DOM if not already present; links to https://wa.me/5521993860628
- Status Page Logic: obrigado.html reads ?status=ok or ?status=erro query param to show success/error message

#### 2. SMTP Email Pipeline (PHP)

Two form handlers (send-contact.php, send-careers.php) share the same smtp-mailer.php utility:

Configuration Loading (smtp_load_config()):
- Reads from environment variables first (SMTP_HOST, SMTP_PORT, SMTP_USERNAME, SMTP_PASSWORD, SMTP_ENCRYPTION)
- Falls back to smtp-config.local.php if it exists (file-based config for local development)
- Can also fall back to smtp-config.local.example.php as last resort
- Supports encryption modes: tls (default), ssl, none

SMTP Implementation (smtp_send_mail()):
- Low-level socket-based SMTP client; does NOT use PHP mail() or SwiftMailer
- Manual protocol implementation: EHLO -> STARTTLS -> AUTH LOGIN -> MAIL FROM -> RCPT TO -> DATA
- Handles UTF-8 subject encoding and safe line-ending normalization
- Returns error message via reference parameter for logging
- No exceptions thrown to client; errors logged server-side with 6-char request ID

Form Processing (send-contact.php example):
- Validates all required fields via FILTER_VALIDATE_EMAIL for email
- Sanitizes with strip_tags() and trim()
- Checks POST method; redirects to obrigado.html?status=ok/erro on completion
- On SMTP error: returns 502 status + diagnostic code; does NOT redirect

#### 3. Styling System (CSS)

CSS variables defined in :root in global.css:

- Colors: --primary #b51212 (brand red), --primary-dark #7f0c0c, --bg, --surface, --panel, etc.
- Layout: --container 1260px, --header-height 160px
- Animation: --transition 0.24s ease (used for hover, state changes)
- Shadows: --shadow-soft (light), --shadow-card (prominent)

No CSS framework (no Bootstrap, Tailwind, etc.). All custom CSS. Responsive breakpoint cutoff at 1180px (matches nav collapse point).

### Contact Information (Hardcoded in Multiple Places)

These values appear throughout the codebase and should be updated consistently:

- Email: contato@rbbautomacao.com.br
- Phone: +55 21 99386-0628 (WhatsApp link: https://wa.me/5521993860628)
- LinkedIn: https://www.linkedin.com/in/leandro-santos-10831b141/
- Instagram: https://www.instagram.com/accounts/onetap/

Files to update: index.html, fale-conosco.html, trabalhe-aqui.html, send-contact.php, assets/js/main.js

## Development Workflow

### No Build or Test Commands

This is a static site. There is no npm install, npm run build, or test suite. Changes are made directly to HTML/CSS/JS files and verified by:

1. Opening the file in a browser locally
2. Testing form submissions (requires PHP + SMTP setup locally, see below)
3. Committing with git

### SMTP Configuration for Local Testing

To test contact/careers forms locally:

Option 1: Environment Variables (Recommended)
PowerShell:
$env:SMTP_HOST="smtp.gmail.com"
$env:SMTP_PORT="587"
$env:SMTP_USERNAME="your-email@gmail.com"
$env:SMTP_PASSWORD="your-app-password"
$env:SMTP_ENCRYPTION="tls"
$env:SMTP_FROM_EMAIL="your-email@gmail.com"
$env:SMTP_FROM_NAME="RBB Automacao"

Option 2: Local Config File
1. Copy smtp-config.local.example.php to smtp-config.local.php (already in .gitignore)
2. Fill in credentials
3. Start PHP server: php -S localhost:8000
4. Navigate to http://localhost:8000 and test forms

### Running a Local Server

Start PHP dev server (listens on localhost:8000):
php -S localhost:8000

Or use a different port:
php -S localhost:9000

Or use Docker if available:
docker run -d -p 8000:80 -v ${PWD}:/var/www/html php:8.0-apache

Visit http://localhost:8000/index.html in your browser.

### Common Development Tasks

Edit a page: Modify the corresponding .html file directly; reload browser.

Update CSS: Edit the relevant file in assets/css/. Most cross-page styles are in global.css and components.css.

Adjust JavaScript behavior: Edit assets/js/main.js. All DOM interactions live here (menu, scroll, forms, animations).

Add a new service/blog post: Create a new .html file following the structure of existing pages. Link it from index.html or blog.html.

Update company contact info: Search for the old phone/email across all .html files and assets/js/main.js, update consistently.

Test form submission:
1. Ensure SMTP config is set (env vars or local file)
2. Start PHP server: php -S localhost:8000
3. Fill out /fale-conosco.html or /trabalhe-aqui.html
4. Submit and verify redirect to obrigado.html?status=ok
5. Check email inbox for the received form data

Debug form errors:
- Client-side validation errors: Check browser console and form field error messages
- SMTP errors: Check PHP error logs or request ID in 502 error response
- SMTP diagnostics: Review server logs or enable error_log at /tmp/php-errors.log

## Deployment

See README.md for official deployment instructions. Summary:

1. Upload all files to public_html on hosting
2. Preserve assets/ folder structure
3. Ensure PHP is enabled
4. Configure SMTP environment variables or create smtp-config.local.php on server
5. Test forms with real SMTP credentials

## Important Notes

- No minification or bundling: Files are served as-is. No build step.
- Single JS file: All interactivity is in assets/js/main.js. Avoid splitting into multiple files unless necessary.
- Direct form validation: Forms validate client-side in JS, then server-side in PHP. Both are required.
- Email configuration: SMTP is required for form submissions. Without it, forms will return 502 errors.
- Patch file: alteracoes-site.patch exists in root; its purpose is unclear and may be for version control workflow.
- No linting: No eslint, prettier, or code style enforcement configured. Maintain consistency with existing code style.
