<?php

namespace Database\Seeders;

use App\Models\BudgetCategory;
use App\Models\BudgetExpenseTemplate;
use App\Models\BudgetLineItem;
use App\Models\BudgetMonth;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BudgetSeeder extends Seeder
{
    public function run(): void
    {
        // Guard: bail if data already exists
        if (BudgetCategory::exists()) {
            $this->command->warn('BudgetSeeder: data already exists. Skipping. Truncate budget tables first if you want to re-seed.');

            return;
        }

        $this->command->info('Step 1/5 — Seeding categories...');
        $categories = $this->seedCategories();

        $this->command->info('Step 2/5 — Seeding recurring expense templates...');
        $this->seedTemplates($categories);

        $this->command->info('Step 3/5 — Creating Feb 2026 budget month & patching paid amounts...');
        $month = $this->seedFebMonth();

        $this->command->info('Step 4/5 — Adding one-off line items...');
        $this->seedOneOffItems($month, $categories);

        $this->command->info('Done. Feb 2026 budget seeded successfully.');
        $this->command->line('  Categories:  '.BudgetCategory::count());
        $this->command->line('  Templates:  '.BudgetExpenseTemplate::count());
        $this->command->line('  Line items: '.$month->lineItems()->count());
        $this->command->line('  Income rows: '.$month->incomeEntries()->count());
    }

    // -------------------------------------------------------------------------
    // Step 1 — Categories
    // -------------------------------------------------------------------------

    private function seedCategories(): array
    {
        $data = [
            ['name' => 'Housing', 'color' => '#3B82F6', 'sort_order' => 10],
            ['name' => 'Family', 'color' => '#8B5CF6', 'sort_order' => 20],
            ['name' => 'Transport', 'color' => '#F59E0B', 'sort_order' => 30],
            ['name' => 'Personal', 'color' => '#10B981', 'sort_order' => 40],
            ['name' => 'Financial', 'color' => '#EF4444', 'sort_order' => 50],
            ['name' => 'Home Maintenance', 'color' => '#6B7280', 'sort_order' => 60],
            ['name' => 'Utilities', 'color' => '#06B6D4', 'sort_order' => 70],
            ['name' => 'Uncategorised', 'color' => '#D1D5DB', 'sort_order' => 80],
        ];

        $created = [];
        foreach ($data as $row) {
            $created[$row['name']] = BudgetCategory::create($row);
        }

        return $created;
    }

    // -------------------------------------------------------------------------
    // Step 2 — Recurring expense templates
    // -------------------------------------------------------------------------

    private function seedTemplates(array $categories): void
    {
        $templates = [
            // name                              amount    category          sort
            ['Scotia Bank – Mortgage', 4188.00, 'Housing', 10],
            ['Car Fuel', 800.00, 'Transport', 20],
            ['Grooming', 200.00, 'Personal', 30],
            ['Personal Spending', 200.00, 'Personal', 40],
            ['Phone PostPaid Plan', 300.00, 'Financial', 50],
            ["Akeem's Lessons", 300.00, 'Family', 60],
            ['Akeem Welfare', 1000.00, 'Family', 70],
            ['Shirrell Graham Payment', 1000.00, 'Financial', 80],
            ['My Groceries', 1500.00, 'Personal', 90],
            ["Syriah's Welfare", 1500.00, 'Family', 100],
            ["Syriah's Allowance", 200.00, 'Family', 110],
            ['Family – Sagicor Life Insurance', 200.00, 'Financial', 120],
            ['Retirement – Annuity Plan', 456.13, 'Financial', 130],
            ['Credit Card Payments', 1292.00, 'Financial', 140],
            ['SOU-SOU', 1000.00, 'Financial', 150],
        ];

        foreach ($templates as [$name, $amount, $categoryName, $sort]) {
            BudgetExpenseTemplate::create([
                'name' => $name,
                'category_id' => $categories[$categoryName]->id,
                'amount' => $amount,
                'frequency' => 'recurring',
                'is_active' => true,
                'sort_order' => $sort,
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Step 3 — Create Feb 2026 month and patch paid amounts
    //
    // createForMonth() stamps all 15 recurring templates as line items.
    // The paid amounts below come directly from the 'Paid' column in
    // the MybudgetApp.xlsx spreadsheet.
    // Items with paid = 0 (mortgage, AC repair, food, door, tanks) were
    // not paid in February per the spreadsheet — remainder left open.
    // -------------------------------------------------------------------------

    private function seedFebMonth(): BudgetMonth
    {
        $month = BudgetMonth::createForMonth(Carbon::parse('2026-02-01'));

        // Map of template name → paid amount for February
        // Source: MybudgetApp.xlsx, Feb 2026 sheet, 'Paid' column
        $paidAmounts = [
            'Scotia Bank – Mortgage' => 0.00,  // unpaid in Feb
            'Car Fuel' => 200.00,
            'Grooming' => 200.00,
            'Personal Spending' => 200.00,
            'Phone PostPaid Plan' => 300.00,
            "Akeem's Lessons" => 300.00,
            'Akeem Welfare' => 1000.00,
            'Shirrell Graham Payment' => 1000.00,
            'My Groceries' => 1500.00,
            "Syriah's Welfare" => 1500.00,
            "Syriah's Allowance" => 200.00,
            'Family – Sagicor Life Insurance' => 200.00,
            'Retirement – Annuity Plan' => 456.13,
            'Credit Card Payments' => 1292.00,
            'SOU-SOU' => 1000.00,
        ];

        foreach ($paidAmounts as $name => $paid) {
            $month->lineItems()
                ->whereHas('template', fn ($q) => $q->where('name', $name))
                ->update(['paid_amount' => $paid]);
        }

        // Projected income
        $month->incomeEntries()->create([
            'label' => 'Salary',
            'type' => 'salary',
            'amount' => 13200.00,
            'is_live' => false,
        ]);

        // Live income (cash position)
        $month->incomeEntries()->create([
            'label' => 'Bank',
            'type' => 'other',
            'amount' => 7634.00,
            'is_live' => true,
        ]);
        $month->incomeEntries()->create([
            'label' => 'Cash in Hand',
            'type' => 'other',
            'amount' => 822.00,
            'is_live' => true,
        ]);

        return $month;
    }

    // -------------------------------------------------------------------------
    // Step 4 — One-off line items (February only, no template)
    //
    // These items appeared in Feb 2026 but are not recurring.
    // template_id is null for all of them.
    // -------------------------------------------------------------------------

    private function seedOneOffItems(BudgetMonth $month, array $categories): void
    {
        $c = $categories; // shorthand

        $items = [
            // name                             budgeted  paid    category           sort
            ["Akeem's School Fees", 0, 0, 'Family', 160],
            ["Syriah's School Supplies", 0, 0, 'Family', 170],
            ['Tenant AC Repair', 600, 0, 'Home Maintenance', 180],
            ['Food for 2 weeks', 500, 0, 'Personal', 190],
            ['Door', 600, 0, 'Home Maintenance', 200],
            ['Tanks to clean', 500, 0, 'Home Maintenance', 210],
            ['Door handle', 185, 185, 'Home Maintenance', 220],
            ['Mother – Black Pants', 200, 200, 'Family', 230],
            ['Wife Gift', 1000, 1000, 'Family', 240],
            ['Tunapuna – KFC Family Treat', 300, 300, 'Family', 250],
            ['Plumbing Repair', 400, 400, 'Home Maintenance', 260],
            ['Pharmacy – Vitamins', 479, 479, 'Personal', 270],
            ["Repayment to Son's Account", 0, 0, 'Financial', 280],
            ['Landlord Surcharge', 0, 0, 'Housing', 290],
            ['House Insurance', 0, 0, 'Housing', 300],
            ['T&TEC', 0, 0, 'Utilities', 310],
            ['WASA', 0, 0, 'Utilities', 320],
            ['My Car Insurance', 0, 0, 'Transport', 330],
            ['Car Parts', 0, 0, 'Transport', 340],
            ['Pet Care', 0, 0, 'Personal', 350],
            ['Stove – Gas', 0, 0, 'Utilities', 360],
            ['Yard', 0, 0, 'Home Maintenance', 370],
            ['Car Wash', 0, 0, 'Transport', 380],
            ['20 Year Anniversary Event', 0, 0, 'Personal', 390],
            ['Akeem Grooming', 0, 0, 'Family', 400],
        ];

        foreach ($items as [$name, $budgeted, $paid, $categoryName, $sort]) {
            BudgetLineItem::create([
                'budget_month_id' => $month->id,
                'template_id' => null,
                'category_id' => $c[$categoryName]->id,
                'name' => $name,
                'budgeted_amount' => $budgeted,
                'paid_amount' => $paid,
                'sort_order' => $sort,
            ]);
        }
    }
}
