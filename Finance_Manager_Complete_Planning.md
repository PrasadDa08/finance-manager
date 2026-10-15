# Finance Manager – Complete Planning Document

**Temporary Name:** Finance Manager  
**Last Updated:** 2026-10-08  
**Status:** Planning Phase Completed (MVP Ready for Development)

This document contains all finalized decisions from our discussions.

---

## 1. Project Overview

- **Goal:** Build an AI-powered personal finance manager
- **Stage 1:** Useful for personal use
- **Stage 2:** Share with friends and family
- **Stage 3:** Convert into a paid product (monthly/yearly)
- **Tech Stack:**
  - Backend: Laravel + Sanctum
  - Frontend: React (Vite)
  - Database: PostgreSQL
  - Later: WebView app (Android Studio / Electron)

---

## 2. Features

### Phase 1 – Must-Have Features (MVP)

1. User Authentication (Register / Login)
2. Multiple Accounts
3. Income & Expense Tracking
4. Categories (Default + Custom)
5. Basic Budget
6. Dashboard
7. Transaction History
8. Basic Reports
9. Profile Management

### Phase 2 – Should-Have Features (After MVP)

1. Transfer between Accounts
2. Recurring Transactions
3. Attach Receipt / Image
4. Better Filters & Search
5. Budget Alerts / Notifications
6. Multiple Currencies (Basic)
7. Export Data (CSV / Excel)
8. Dark Mode
9. Edit / Delete Categories
10. Account Balance Adjustment

### Phase 3 – Future Features (After Website is Complete)

- AI Auto-Categorization
- AI Insights & Suggestions
- Natural Language Chat
- Family / Shared Access
- Goals & Savings Targets
- Bill Reminders
- Custom Reports
- Tags
- Investment Tracking (Basic)
- Debt / Loan Tracker
- Monthly Financial Health Score

---

## 3. Database Design (MVP)

### Core Tables

1. **users**
2. **accounts**
3. **categories**
4. **transactions**
5. **budgets**
6. **roles** (for future expandability)

### Key Points

- Categories support both system defaults (`user_id = NULL`) and custom categories
- Current account balance is calculated (not stored permanently)
- Designed to be easily expandable for Transfers, Recurring, Family Sharing, AI, etc.

**Detailed structure available in:** `Database_Design.md`

---

## 4. Authentication

| Item                    | Decision                                      |
|-------------------------|-----------------------------------------------|
| Package                 | Laravel Sanctum                               |
| Style                   | Cookie-based SPA (more secure)                |
| Token Storage           | httpOnly Cookie                               |
| Register / Login        | Email + Password                              |
| Forgot Password         | Later                                         |
| Email Verification      | Later                                         |
| Rate Limiting on Login  | Yes (5 attempts per minute)                   |

**API Endpoints (MVP):**
- POST `/api/register`
- POST `/api/login`
- POST `/api/logout`
- GET  `/api/user`

---

## 5. Roles & Permissions

| Role     | Meaning                                      |
|----------|----------------------------------------------|
| owner    | Full control (default for every user)        |
| member   | Can add/edit transactions & budgets (future) |
| viewer   | Read-only access (future)                    |

- Separate `roles` table created
- Every new user gets `owner` role by default
- Ready for future Family Sharing

---

## 6. Free vs Paid Features

### Free Plan Limits
- Maximum 3 Accounts
- Transaction History → Last 3 months only
- Maximum 3 Recurring Transactions
- Receipt Uploads → 5 per month
- No AI features
- No Family Sharing
- No Export

### Paid Plan
- Unlimited Accounts
- Full History
- Unlimited Recurring + Receipts
- Export (CSV/Excel)
- All AI Features
- Family / Shared Access
- Goals, Debt Tracker, Health Score
- Priority Support

---

## 7. Rate Limiting & Security

**Rate Limits:**
- Login / Register → 5 attempts per minute
- General API → 60 requests per minute
- Heavy actions → 10 requests per minute

**Security (MVP):**
- bcrypt password hashing
- HTTPS only
- CSRF + CORS protection
- httpOnly cookies
- Proper input validation
- Secure file upload rules (for receipts)

---

## 8. Emails & Notifications

**MVP Scope:**
- Welcome Email (after registration)
- Budget Alert Emails (at 80% and 100%)
- In-App Notifications (Notification Bell + Budget alerts)

**Future:**
- SMS support
- Monthly summary emails
- Push notifications
- Password reset emails

---

## 9. Customer Support, Pricing & Legal (MVP)

| Item                  | Decision                                      |
|-----------------------|-----------------------------------------------|
| Customer Support      | Basic only (Contact form or support email)    |
| Pricing               | Not needed now (decide later)                 |
| Privacy Policy        | Simple version – Required                     |
| Terms of Service      | Simple version – Required                     |
| Full Legal Setup      | Later                                         |

---

## 10. Development Approach

- Temporary name for development: **Finance Manager**
- Final name and branding will be decided after proper MVP is ready
- Focus on building clean and expandable code
- First make it useful for personal use → then friends/family → then paid product

---

## Related Detailed Files

- `Features_Detailed.md`
- `Database_Design.md`
- `Authentication.md`
- `Roles_and_Permissions.md`
- `Free_vs_Paid_Features.md`
- `Rate_Limiting_and_Security.md`
- `Emails_and_Notifications.md`
- `Customer_Support_Pricing_Legal.md`
- `Finora_Project_Decisions.md` (original decisions log)

---

**Planning Phase Completed.**  
Ready to move to development when you are.
