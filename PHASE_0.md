# Finance Manager — PHASE 0 (MVP)

**Status:** Complete and deployed  
**Phase name:** PHASE 0 — MVP  
**Goal:** Personal finance tracker usable by the developer and friends; web-first, shareable, expandable.

---

## 1. Product summary

A simple money-tracking web app where users can:

- Register with email OTP verification
- Manage accounts (bank, cash, credit card, wallet)
- Use system + custom categories (income / expense)
- Record transactions
- View a filtered dashboard (balance, income, expense, recent activity)
- Send in-app feedback

**Temporary product name:** Finance Manager (FM)  
**Positioning:** Private, no ads, no loan spam, mobile-friendly.

---

## 2. Tech stack

### Backend
| Item | Choice |
|------|--------|
| Language | PHP 8.4 |
| Framework | Laravel 13.x |
| Auth | Laravel Sanctum (SPA session cookies + token support) |
| Database | PostgreSQL |
| Mail | SMTP via Resend |
| API style | JSON REST |

### Frontend
| Item | Choice |
|------|--------|
| Library | React (Vite) |
| Language | TypeScript |
| Routing | React Router |
| Forms | React Hook Form + Zod |
| State | Zustand (auth + toast) |
| HTTP | Axios (`withCredentials`, CSRF) |
| Styling | Tailwind CSS |

### Hosting / infra (production)
| Part | Host |
|------|------|
| Frontend | Vercel |
| Backend | Render (Docker) |
| Database | Render PostgreSQL |
| Domains | Custom (`fm.*` / `fmapi.*` pattern) |
| Email | Resend (free tier: ~100/day, ~3000/month) |

### Local development
- Windows + Laravel Herd / PHP CLI
- Node.js + npm
- PostgreSQL + pgAdmin
- GitHub repos (API + Web)

---

## 3. Architecture

```
Browser (fm.domain.com)
    │  HTTPS + cookies (SameSite as configured for domain layout)
    ▼
React SPA (Vercel)
    │  Axios → VITE_API_URL
    ▼
Laravel API (fmapi.domain.com on Render)
    │
    ├── PostgreSQL (Render)
    └── Resend SMTP (OTP emails)
```

### Auth model (production)
- SPA cookie session + CSRF (`/sanctum/csrf-cookie`)
- Sanctum stateful domains include frontend host
- Login/register also return Sanctum personal access token (available for future app/WebView use)
- Password hashed with bcrypt (`Hash::make`)

---

## 4. Features delivered in PHASE 0

### 4.1 Public
- Landing page (headline, how it works, features, CTA)
- Login
- Register (2-step: details → email OTP)

### 4.2 Authenticated app shell
- Responsive layout
  - Desktop: sidebar nav
  - Mobile: top bar + bottom nav (+ center create)
- More page (Categories, Accounts shortcuts, Logout)
- Feedback button + modal (header)
- Toast notifications (custom Zustand, no extra toast library)

### 4.3 Accounts
- List (active/inactive visual difference)
- Create / Edit / Delete
- Types: `bank`, `cash`, `credit_card`, `wallet`
- Fields: name, type, opening_balance, currency, is_active
- Info modal: what an account is
- Field-level info (`i`) on create/edit

### 4.4 Categories
- List system (default) + user categories
- Create / Edit / Delete **user** categories only
- System categories protected (no edit/delete)
- Type: `income` | `expense`
- Color badge (dot + first letter, contrast-safe text)
- Default color by type if not provided
- Icon stored as `default` (picker deferred)
- Info modal: what a category is

### 4.5 Transactions
- List with category color badge, account, date, note, amount
- Actions via `⋮` menu (Edit / Delete)
- Create / Edit
- Type filters category dropdown
- Account dropdown shows `Name (Type)`
- Empty states with CTAs (add account / add transaction)
- Field-level info on forms

### 4.6 Dashboard
- Greeting
- Collapsible filters (account, period, from/to date)
- Summary: Balance, Income, Expense
- Recent transactions
- Empty / no-account CTAs
- Theme: Teal + Purple

### 4.7 Feedback
- Authenticated submit only
- Types: feedback | suggestion | bug
- Stores message + optional page path
- Admin review via DB (no admin UI yet)

### 4.8 Defaults
- Seeded system categories (income + expense) with colors

### Explicitly deferred (post PHASE 0)
- Mobile native / Electron packaging
- AI features / salary daily budget
- Phone number / SMS OTP
- Admin panel for feedback
- Budgets, reports export, multi-currency FX
- Paid plans / billing
- Final commercial brand name

---

## 5. Database schema

### 5.1 `users`
| Column | Type | Notes |
|--------|------|--------|
| id | bigint PK | |
| name | string | |
| email | string unique | |
| email_verified_at | timestamp nullable | not fully used as Laravel verification; OTP used at register |
| password | string | hashed |
| remember_token | string nullable | |
| created_at / updated_at | timestamps | |

**Roles table:** prepared in discussions; not required for PHASE 0 runtime paths.

### 5.2 `registration_otps`
Temporary signup store (user created only after OTP verify).

| Column | Type | Notes |
|--------|------|--------|
| id | bigint PK | |
| name | string | |
| email | string indexed | |
| password | string | **already hashed** |
| otp | string(6) | |
| expires_at | timestamp | 10 minutes |
| created_at / updated_at | timestamps | |

### 5.3 `accounts`
| Column | Type | Notes |
|--------|------|--------|
| id | bigint PK | |
| user_id | FK → users | |
| name | string | |
| type | string | bank, cash, credit_card, wallet |
| opening_balance | decimal | |
| currency | string | default INR |
| is_active | boolean | |
| created_at / updated_at | timestamps | |

### 5.4 `categories`
| Column | Type | Notes |
|--------|------|--------|
| id | bigint PK | |
| user_id | FK nullable | null = system/default |
| name | string | |
| type | string | income, expense |
| icon | string nullable | `default` in MVP |
| color | string nullable | hex |
| is_default | boolean | system flag |
| created_at / updated_at | timestamps | |

### 5.5 `transactions`
| Column | Type | Notes |
|--------|------|--------|
| id | bigint PK | |
| user_id | FK → users | |
| account_id | FK → accounts | |
| category_id | FK → categories | |
| type | string | income, expense |
| amount | decimal | |
| note | string nullable | |
| transaction_date | date | |
| created_at / updated_at | timestamps | |

### 5.6 `feedback`
| Column | Type | Notes |
|--------|------|--------|
| id | bigint PK | |
| user_id | FK → users | |
| type | string | feedback, bug, suggestion |
| message | text | |
| page | string nullable | frontend path |
| created_at / updated_at | timestamps | |

### 5.7 Sanctum
- `personal_access_tokens` (package default)

### 5.8 Relationships
```
User 1─* Account
User 1─* Category (custom)
User 1─* Transaction
User 1─* Feedback
Account 1─* Transaction
Category 1─* Transaction
```

System categories: `categories.user_id IS NULL` (and/or `is_default = true`).

---

## 6. API reference

Base URL (prod): `https://fmapi.<domain>`  
Auth: Sanctum SPA (cookie + CSRF) for web.

### 6.1 Auth

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/sanctum/csrf-cookie` | No | Issue CSRF/session cookies |
| POST | `/api/register/request-otp` | No | Validate input, store OTP, send email |
| POST | `/api/register/verify-otp` | No | Verify OTP, create user, login |
| POST | `/api/login` | No | Session login + token in response |
| POST | `/api/logout` | Yes | Invalidate session / current token |

#### `POST /api/register/request-otp`
**Body**
```json
{
  "name": "string",
  "email": "string",
  "password": "string"
}
```
**Validation**
- name: required, string, max 100
- email: required, email:rfc,dns, unique:users,email
- password: required, min 6
- Blocked disposable/dummy domains (e.g. test.com, example.com, mailinator.com, …)

**Behavior**
- Deletes prior OTPs for email
- Saves hashed password + 6-digit OTP (10 min expiry)
- Sends HTML (or text) OTP mail

#### `POST /api/register/verify-otp`
**Body**
```json
{
  "email": "string",
  "otp": "string(6)"
}
```
**Behavior**
- Validates OTP + expiry
- Creates user (password already hashed — do not re-hash)
- Deletes OTP row
- Logs in (session) + returns token

#### `POST /api/login`
**Body**
```json
{
  "email": "string",
  "password": "string"
}
```
**Validation:** email required|email, password required  
**Response:** user + token; session regenerated when applicable

---

### 6.2 Accounts (auth)

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/accounts` | List current user accounts |
| POST | `/api/accounts` | Create |
| GET | `/api/accounts/{account}` | Show (ownership checked in controller patterns) |
| PUT/PATCH | `/api/accounts/{account}` | Update |
| DELETE | `/api/accounts/{account}` | Delete |

**Store validation**
- name: required|string|max:100
- type: required|in:bank,cash,credit_card,wallet
- opening_balance: nullable|numeric|min:0
- currency: nullable|string|max:10
- is_active: nullable|boolean

**Update validation:** same fields with `sometimes` where used  
**Ownership:** user may only mutate own accounts

---

### 6.3 Categories (auth)

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/categories` | System defaults + current user categories |
| POST | `/api/categories` | Create user category |
| PUT/PATCH | `/api/categories/{category}` | Update (non-default only) |
| DELETE | `/api/categories/{category}` | Delete (non-default only) |

**Store validation**
- name: required|string|max:100
- type: required|in:income,expense
- icon: nullable|string|max:50
- color: nullable|string
- is_default: not user-settable to hijack system (forced false for user creates)

**Rules**
- System categories (`is_default` / null user_id): cannot edit/delete

---

### 6.4 Transactions (auth)

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/transactions` | List user transactions (with account, category) |
| POST | `/api/transactions` | Create |
| GET | `/api/transactions/{transaction}` | Show |
| PUT/PATCH | `/api/transactions/{transaction}` | Update |
| DELETE | `/api/transactions/{transaction}` | Delete |

**Store / update validation**
- account_id: required|exists:accounts,id
- category_id: required|exists:categories,id
- type: required|in:income,expense
- amount: required|numeric|min:0.01
- note: nullable|string|max:255
- transaction_date: required|date

**Business rules**
- Account must belong to user
- Category must be user’s or system default
- Ordered by transaction_date desc, id desc on index

---

### 6.5 Dashboard (auth)

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/dashboard` | Summary + recent transactions |

**Query params**
- `period`: all | this_week | this_month | this_year
- `account_id`: optional
- `from_date`, `to_date`: optional (custom range; takes priority when set)

**Response (shape)**
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_balance": 0,
      "total_income": 0,
      "total_expense": 0
    },
    "recent_transactions": []
  }
}
```

---

### 6.6 Feedback (auth)

| Method | Path | Description |
|--------|------|-------------|
| POST | `/api/feedback` | Store feedback |

**Validation**
- type: required|in:feedback,bug,suggestion
- message: required|string|min:5|max:2000
- page: nullable|string|max:255

---

## 7. Frontend routes

### Public
| Path | Page |
|------|------|
| `/` | Landing |
| `/login` | Login |
| `/register` | Register (OTP steps) |

### Protected (app layout)
| Path | Page |
|------|------|
| `/dashboard` | Dashboard |
| `/accounts` | Accounts list |
| `/accounts/create` | Create account |
| `/accounts/:id/edit` | Edit account |
| `/categories` | Categories list |
| `/categories/create` | Create category |
| `/categories/:id/edit` | Edit category |
| `/transactions` | Transactions list |
| `/transactions/create` | Create transaction |
| `/transactions/:id/edit` | Edit transaction |
| `/more` | More menu |

---

## 8. Validations summary

### Backend (primary)
- Form Request / controller `$request->validate([...])`
- Ownership checks return 403
- System category protection
- OTP expiry and match
- Email domain blocklist + `email:rfc,dns`

### Frontend
- Zod schemas via `@hookform/resolvers/zod`
- Mirror critical rules (email format, min lengths, enums)
- UX: disable submit while loading; show API error messages

---

## 9. Mail

### Provider
- Resend SMTP
- Free tier guidance: ~100 emails/day, ~3000/month

### From address
- Dev/test: provider test sender (e.g. `onboarding@resend.dev`)
- Production goal: `noreply@yourdomain.com` after domain DNS (SPF/DKIM)

### Templates
- `RegistrationOtpMail` mailable
- Blade view: branded OTP code, 10-minute expiry note
- Subject: verification code for Finance Manager

### Flow
1. `request-otp` generates code  
2. Mail sent to user  
3. `verify-otp` consumes code  

---

## 10. Packages / libraries

### Backend (notable)
- `laravel/framework`
- `laravel/sanctum`
- PostgreSQL PDO
- Composer-managed dependencies only (no extra AI SDKs in PHASE 0)

### Frontend (notable)
- `react`, `react-dom`, `react-router-dom`
- `typescript`
- `axios`
- `zustand`
- `react-hook-form`
- `zod` + `@hookform/resolvers`
- `tailwindcss`

**Explicitly avoided:** extra toast libraries, heavy icon packs (inline SVGs instead).

---

## 11. UI / UX decisions

### Theme (Teal + Purple)
| Role | Value |
|------|--------|
| Page background | `#EEFCF8` |
| Primary | `#7C3AED` |
| Accent | `#0D9488` |
| Text | `#134E4A` |
| Muted | `#5B7A76` |
| Income | `#0F766E` |
| Expense | `#E11D48` |
| Borders | soft teal (`#99F6E4` family) |

### Patterns
- Compact list rows in one card container
- Dashboard filters behind icon button
- Info modals for first-time concepts
- Field help via `i` (not label-wrapped buttons)
- Transaction actions in `⋮` menu
- Empty states with clear CTAs

---

## 12. Security decisions (PHASE 0)

- Passwords hashed (bcrypt)
- OTP registration reduces throwaway signups
- Disposable domain blocklist
- Ownership checks on resources
- System categories immutable for users
- SPA auth via Sanctum cookies + CSRF (preferred over localStorage tokens)
- CORS allowlist exact frontend origins; credentials enabled
- Production: `APP_DEBUG=false`, HTTPS

**Known constraints**
- Cross-site cookies (different parent domains) need careful `SameSite` / `Secure` / domain layout
- Preferred production layout: `fm.` + `fmapi.` under **same root domain**
- Render free DB expires (~30 days) — backup/upgrade required
- Free API may cold-start slowly

---

## 13. Default data

System categories (examples seeded):

**Expense:** Food, Transport, Shopping, Rent, Bills, Health, Education, Entertainment, Personal, Other Expense  

**Income:** Salary, Business, Freelance, Investment, Gift, Other Income  

Each has a hex color; `user_id = null`, `is_default = true`.

---

## 14. Deployment notes

### API (Render + Docker)
- PHP 8.4 image (required by locked deps)
- `composer install --no-dev`
- Boot: `config:clear`, `migrate --force`, `artisan serve` on `$PORT`
- Env: `APP_KEY`, DB_*, mail, Sanctum/session, `FRONTEND_URL`

### Frontend (Vercel)
- Env: `VITE_API_URL=https://fmapi...`
- Axios uses env base URL + credentials

### Domains
- Frontend: `fm.<domain>`
- API: `fmapi.<domain>`

---

## 15. PHASE 0 acceptance checklist

- [x] Register with OTP email
- [x] Login / logout
- [x] CRUD accounts
- [x] CRUD categories (user) + system defaults visible
- [x] CRUD transactions
- [x] Dashboard filters + summary
- [x] Landing page
- [x] Feedback submit
- [x] Mobile-friendly shell
- [x] Deployed on custom domains
- [x] End-to-end test passed (OTP, login, add/edit)

---

## 16. Git / process for next phase

- Tag or document this state as **PHASE 0**
- Next work on a **new branch**, e.g.:
  ```bash
  git checkout -b phase-1/<feature-area>
  ```
- Keep `main` (or production branch) stable for friends using the live app

---

## 17. Suggested PHASE 1 topics (discussion only)

Not committed scope — for later planning:

1. Budgets / monthly limits  
2. Salary daily spend helper + notifications  
3. Reports / export (CSV)  
4. Recurring transactions  
5. Admin feedback inbox UI  
6. Hardening mail domain + deliverability  
7. Electron / PWA packaging  
8. Performance (queue mail, better PHP runtime than `artisan serve`)  
9. Final product name + logo  
10. Billing later if productized  

---

## 18. Document control

| Field | Value |
|-------|--------|
| Document | PHASE_0.md |
| Phase | 0 — MVP |
| Audience | Developer notes for continuity |
| Next step | Discuss PHASE 1 features; branch and implement |

--- 

*End of PHASE 0 documentation.*
