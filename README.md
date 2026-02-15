# AntonBudgetApp

A personal finance application for tracking credit cards and managing monthly budgets. Built with Laravel 12 and Filament v5, it combines a credit card transaction ledger with a full-featured budget module that tracks projected vs. live income and expenses.

---

## Features

### Credit Cards

- **Multi-card management** — Add and manage multiple credit cards with name, currency, credit limit, and open date.
- **Transaction ledger** — Record charges, fees, payments, and refunds per card with date, description, amount, type, and status.
- **Status tracking** — Transactions can be Pending (unconfirmed) or Posted (confirmed).
- **Balance calculations** — Per-card and portfolio-wide totals for:
  - **Posted balance** — Confirmed charges
  - **Pending charges** — Awaiting confirmation
  - **True balance** — What you owe (posted + pending)
  - **Available credit** — Remaining credit before limit
- **Dashboard quick-add** — Add transactions from the dashboard without navigating to a card.
- **Portfolio overview** — Aggregate totals across all cards on the dashboard.

### Budget Module

- **Monthly budgets** — One budget per calendar month with recurring and one-off expenses.
- **Expense templates** — Recurring templates (e.g. mortgage, groceries) that auto-populate new months.
- **Categories** — Color-coded categories (Housing, Family, Transport, etc.) for grouping expenses.
- **Projected vs. live tracking** — Two parallel views:
  - **Projected** — Planned income and budgeted expenses.
  - **Live** — Actual income and what you still owe.
- **Income buckets** — Multiple income sources per month:
  - **Projected income** — Planned (e.g. salary).
  - **Live income** — Actual cash (e.g. bank balance, cash in hand).
- **Expense management** — Per-month line items with:
  - Budgeted amount
  - Paid amount (updated as you pay)
  - Remainder (budgeted − paid)
  - Category badges
  - Pay button to record payments
- **Stats** — Six metrics per month:
  - Proj. Income, Proj. Expenses, Proj. Remainder
  - Live Income, Payment Due, Live Remainder
- **Dashboard widget** — Budget overview for the current month with quick link to full budget.
- **Sorting** — Sort expenses by unpaid amount or original order.
- **One-off expenses** — Add ad-hoc expenses that are not recurring.

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Framework | Laravel 12 |
| Admin UI | Filament v5 |
| Frontend | Livewire, Alpine.js, Tailwind CSS v4 |
| PHP | 8.2+ |
| Testing | Pest 4 |

---

## Project Structure

```
app/
├── Filament/
│   ├── Pages/
│   │   └── Dashboard.php
│   ├── Resources/
│   │   ├── Budget/           # Budget module resources
│   │   │   ├── BudgetCategoryResource.php
│   │   │   ├── BudgetExpenseTemplateResource.php
│   │   │   ├── BudgetMonthResource.php
│   │   │   ├── Pages/
│   │   │   ├── RelationManagers/
│   │   │   │   ├── LineItemsRelationManager.php
│   │   │   │   ├── ProjectedIncomeRelationManager.php
│   │   │   │   └── LiveIncomeRelationManager.php
│   │   │   ├── Schemas/
│   │   │   ├── Tables/
│   │   │   └── Widgets/
│   │   └── CreditCards/
│   │       ├── CreditCardResource.php
│   │       ├── RelationManagers/
│   │       │   └── TransactionsRelationManager.php
│   │       ├── Schemas/
│   │       └── Tables/
│   └── Widgets/
│       ├── AllCardsOverviewWidget.php
│       ├── BudgetOverviewWidget.php
│       └── CardSummaryTableWidget.php
├── Models/
│   ├── BudgetCategory.php
│   ├── BudgetExpenseTemplate.php
│   ├── BudgetMonth.php
│   ├── BudgetLineItem.php
│   ├── BudgetIncomeEntry.php
│   ├── CreditCard.php
│   └── CardTransaction.php
└── Helpers/
    └── Money.php
```

---

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js & npm
- SQLite (or MySQL/PostgreSQL)

### Installation

```bash
# Clone the repository
git clone <repository-url>
cd Credit_Card_Transaction_Ledger_App

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --force

# Optional: seed sample budget data
php artisan db:seed --class=BudgetSeeder

# Build assets
npm run build
```

### Development

```bash
# Run all dev services (server, queue, logs, vite)
composer run dev

# Or individually:
php artisan serve
npm run dev
```

With Laravel Herd, the app is available at `https://credit-card-transaction-ledger-app.test`.

---

## Key Concepts

### Currency

- **Credit cards** — Use the card's currency (e.g. USD).
- **Budget** — Uses **TTD** (Trinidad and Tobago Dollars).

### Budget Projected vs. Live

| Metric | Definition |
|--------|------------|
| **Projected Income** | Sum of planned income entries (`is_live = false`) |
| **Projected Expenses** | Sum of all `budgeted_amount` on expense line items |
| **Projected Remainder** | Projected Income − Projected Expenses |
| **Live Income** | Sum of actual income entries (`is_live = true`) |
| **Payment Due** | Sum of (budgeted − paid) per expense — what you still owe |
| **Live Remainder** | Live Income − Payment Due |

### Creating a New Month

1. Go to **Budget → Monthly Budgets**.
2. Click **New Month**.
3. Choose month/year.
4. The system creates the month and copies all active recurring templates.

---

## Testing

```bash
php artisan test --compact
```

---

## License

MIT License. See [LICENSE](LICENSE) for details.
