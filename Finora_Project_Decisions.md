# Finance Manager Project – Decisions Log

**Last Updated:** 2026-10-06

This file tracks all finalized decisions as we discuss them one by one.

---

## 1. App Name
- Status: **Not finalized** (left for later)
- Working references used earlier: Finora, FinPulse, FinSense, etc. (all dropped due to conflicts)
- Final decision: Pending

---

## 2. Features (Finalized)

### Phase 1 – Must-Have (MVP)
Status: **Finalized**

1. User Authentication (Register / Login)
2. Multiple Accounts
3. Income & Expense Tracking
4. Categories (Default + Custom)
5. Basic Budget
6. Dashboard
7. Transaction History
8. Basic Reports
9. Profile Management

Detailed explanation available in: `Features_Detailed.md`

### Phase 2 – Should-Have
Status: **Finalized** (to be built after MVP)

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

### Phase 3 – Future Features
Status: **Finalized** (to be added only after website is complete)

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

## 3. Technical Stack (Proposed)

| Layer        | Technology              | Status     |
|--------------|-------------------------|------------|
| Backend      | Laravel + Sanctum       | Proposed   |
| Frontend     | React (Vite)            | Proposed   |
| Database     | PostgreSQL              | Proposed   |
| Auth         | Laravel Sanctum         | Proposed   |
| Mobile later | WebView (Android Studio / Electron) | Planned |

---

## 4. Topics Still Pending

- Database Design
- Authentication flow details
- Roles & Permissions
- Free vs Paid features
- Rate Limiting
- Emails & Notifications
- Customer Support
- Pricing
- Copyright / Legal

---

## 5. Notes

- We discuss one topic at a time and only mark it Final after confirmation.
- Detailed feature explanations are stored in `Features_Detailed.md`.
