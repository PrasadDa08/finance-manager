# AI-Powered Finance Manager – Detailed Features Document

**Last Updated:** 2026-10-06  
**Status:** Features finalized for discussion. Name will be decided later.

This document contains the complete list of features with detailed explanations.

---

## Phase 1: Must-Have Features (MVP)

These features will be built first. The app should be useful for personal finance management with only these features.

### 1. User Authentication (Register / Login)

**What it means:**  
Users must create an account and log in to use the app.

**How it works:**
- User registers with Email + Password
- After registration, they can log in
- Secure token-based authentication using Laravel Sanctum
- Later we can add: Forgot Password, Google Login, Email Verification

**Why needed:**  
Financial data is sensitive. Without proper authentication, anyone could see or change the data.

---

### 2. Multiple Accounts

**What it means:**  
Users can create different “money places” (accounts) where their money is kept.

**Examples of accounts:**
- Cash (physical cash)
- SBI Savings Account
- HDFC Credit Card
- Paytm Wallet
- PhonePe Wallet
- Fixed Deposit

**What each account will store:**
- Account Name
- Account Type (Bank / Cash / Credit Card / Wallet / Other)
- Opening Balance
- Currency (₹ by default)

**Why needed:**  
Your total money is never in one place. To know the real financial position, the app must track all accounts.

---

### 3. Income & Expense Tracking

**What it means:**  
This is the core of the app — recording every money that comes in and goes out.

**Income examples:**
- Salary
- Freelance payment
- Interest received
- Money received from friend/family

**Expense examples:**
- Food & Dining
- Rent
- Fuel
- Shopping
- Mobile recharge
- Medical

**How it works:**
- User selects an Account
- Selects Type (Income or Expense)
- Enters Amount
- Selects Category
- Adds Date and optional Note
- Saves the transaction

**Why needed:**  
Without recording income and expenses, money management is impossible.

---

### 4. Categories

**What it means:**  
Categories help group similar transactions so users can understand spending patterns.

**Default Expense Categories:**
- Food & Dining
- Transport
- Shopping
- Bills & Utilities
- Entertainment
- Health
- Education
- Rent
- Others

**Default Income Categories:**
- Salary
- Freelance
- Business
- Interest
- Gift
- Others

**Additional capability:**
- Users can create **custom categories** (example: Gym, Pet Care, EMI, Subscriptions)

**Why needed:**  
Without categories, users only see total spending. With categories, they know exactly where the money is going.

---

### 5. Basic Budget

**What it means:**  
Users set a monthly spending limit for each category.

**Example:**
- Food → ₹8,000
- Transport → ₹3,000
- Entertainment → ₹2,000
- Shopping → ₹5,000

**How it works:**
- User sets monthly budget amount for categories
- App shows Spent vs Remaining amount
- Progress can be shown with simple progress bar

**Why needed:**  
Only tracking expenses is not enough. Budget helps users control and limit their spending.

---

### 6. Dashboard

**What it means:**  
The first screen after login. It gives a quick summary of the user’s financial health.

**What Dashboard will show:**
- Total Balance (sum of all accounts)
- This Month’s Total Income
- This Month’s Total Expense
- Budget Overview (how much budget is left)
- Recent Transactions (last 5–10)
- Simple visual summary (optional in MVP)

**Why needed:**  
Users should understand their money situation within a few seconds without opening multiple screens.

---

### 7. Transaction History

**What it means:**  
A complete list of all income and expense entries.

**Features inside Transaction History:**
- View all transactions
- Filter by:
  - Date range
  - Account
  - Category
  - Income / Expense
- Search by note or amount
- Edit any transaction
- Delete any transaction

**Why needed:**  
Users need to review past spending, correct mistakes, and analyze their financial behavior.

---

### 8. Basic Reports

**What it means:**  
Visual summary of income and expenses to make patterns easy to understand.

**What reports will include (MVP level):**
- Monthly spending by category (Pie chart or Bar chart)
- Income vs Expense comparison
- This month vs Last month comparison

**Why needed:**  
Raw numbers are hard to understand. Charts make it clear where money is going (example: “I spend too much on food”).

---

### 9. Profile Management

**What it means:**  
Users can manage their own account settings.

**What users can do:**
- Edit Name
- Change Email
- Change Password
- Logout
- (Later) Delete Account

**Why needed:**  
Basic user control and account security.

---

## Phase 2: Should-Have Features

These features will be built after the MVP is complete and working. They make the app significantly more useful.

### 1. Transfer between Accounts
Move money from one account to another (example: Withdraw cash from bank to wallet).  
This is not treated as expense or income.

### 2. Recurring Transactions
Automatically create regular entries such as:
- Salary (every month)
- Rent
- EMI
- Subscriptions (Netflix, etc.)

### 3. Attach Receipt / Image
Upload photo of bill or receipt while adding an expense.

### 4. Better Filters & Search
Advanced filtering and search options in Transaction History.

### 5. Budget Alerts / Notifications
Get warning when spending is close to or exceeds the budget limit.

### 6. Multiple Currencies (Basic)
Support for other currencies (useful for travel or foreign income).

### 7. Export Data
Download all transactions as CSV or Excel file for backup or analysis.

### 8. Dark Mode
Dark theme for better night-time usage and modern look.

### 9. Edit / Delete Categories
Full control to manage (edit or delete) categories.

### 10. Account Balance Adjustment
Manually correct the balance of any account when it does not match real life.

---

## Phase 3: Future Features (After Website is Complete)

These advanced features will be added later. Many of them can become part of the Paid plan.

| Feature                        | Description                                      | Likely Paid? |
|--------------------------------|--------------------------------------------------|--------------|
| AI Auto-Categorization         | AI automatically suggests category               | Yes         |
| AI Insights & Suggestions      | Smart tips based on spending patterns            | Yes         |
| Natural Language Chat          | Ask questions in normal English/Hindi            | Yes         |
| Family / Shared Access         | Share budget with spouse or family               | Yes         |
| Goals & Savings Targets        | Set and track financial goals                    | Yes         |
| Bill Reminders + Due Dates     | Advanced bill management                         | Medium      |
| Custom Reports                 | Create your own detailed reports                 | Yes         |
| Tags                           | Add multiple tags to transactions                | Medium      |
| Investment Tracking (Basic)    | Track mutual funds / stocks                      | Future      |
| Debt / Loan Tracker            | Track personal loans and EMIs                    | Yes         |
| Monthly Financial Health Score | Simple score based on spending habits            | Yes         |

---

## Notes

- Name of the app is not finalized yet. We will decide later.
- We will first complete the website with Must-have + Should-have features.
- Future (AI + Family + Advanced) features will be added after the core website is stable.
- Free vs Paid feature separation will be decided later.

---

**Document maintained as single source of truth for features.**
