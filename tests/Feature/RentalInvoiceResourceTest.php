<?php

use App\Enums\RentalInvoiceStatus;
use App\Filament\Resources\RentalInvoices\Pages\CreateRentalInvoice;
use App\Filament\Resources\RentalInvoices\Pages\EditRentalInvoice;
use App\Filament\Resources\RentalInvoices\Pages\ListRentalInvoices;
use App\Models\RentalInvoice;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can load the list page', function () {
    $invoices = RentalInvoice::factory()->count(3)->create();

    Livewire::test(ListRentalInvoices::class)
        ->assertOk()
        ->assertCanSeeTableRecords($invoices);
});

it('can create a rental invoice', function () {
    Livewire::test(CreateRentalInvoice::class)
        ->fillForm([
            'date' => '2026-03-01',
            'due_date' => '2026-03-01',
            'tenant_name' => 'Josiah Gosyne & Sashel Smith',
            'landlord_name' => 'Anton Graham',
            'landlord_address' => '#8 Nunes, San Juan',
            'landlord_phone' => '299-6232',
            'landlord_email' => 'anton.graham.2011@gmail.com',
            'description' => 'Monthly Rent for March 2026',
            'rent_amount' => 3500,
            'status' => RentalInvoiceStatus::Paid,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(RentalInvoice::class, [
        'tenant_name' => 'Josiah Gosyne & Sashel Smith',
        'description' => 'Monthly Rent for March 2026',
        'rent_amount' => '3500.00',
        'total_amount' => '3500.00',
    ]);
});

it('auto-calculates total_amount from rent + additional charges', function () {
    Livewire::test(CreateRentalInvoice::class)
        ->fillForm([
            'date' => '2026-03-01',
            'due_date' => '2026-03-01',
            'tenant_name' => 'Test Tenant',
            'landlord_name' => 'Anton Graham',
            'landlord_address' => '#8 Nunes, San Juan',
            'landlord_phone' => '299-6232',
            'landlord_email' => 'anton.graham.2011@gmail.com',
            'description' => 'Monthly Rent',
            'rent_amount' => 3500,
            'additional_charges' => 200,
            'status' => RentalInvoiceStatus::Paid,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(RentalInvoice::class, [
        'rent_amount' => '3500.00',
        'additional_charges' => '200.00',
        'total_amount' => '3700.00',
    ]);
});

it('can load the edit page', function () {
    $invoice = RentalInvoice::factory()->create();

    Livewire::test(EditRentalInvoice::class, ['record' => $invoice->getRouteKey()])
        ->assertOk();
});

it('can update a rental invoice', function () {
    $invoice = RentalInvoice::factory()->create();

    Livewire::test(EditRentalInvoice::class, ['record' => $invoice->getRouteKey()])
        ->fillForm([
            'tenant_name' => 'Updated Tenant Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(RentalInvoice::class, [
        'id' => $invoice->id,
        'tenant_name' => 'Updated Tenant Name',
    ]);
});

it('can delete a rental invoice', function () {
    $invoice = RentalInvoice::factory()->create();

    Livewire::test(EditRentalInvoice::class, ['record' => $invoice->getRouteKey()])
        ->callAction(DeleteAction::class);

    expect(RentalInvoice::find($invoice->id))->toBeNull();
});

it('validates required fields on create', function (array $data, array $errors) {
    $valid = RentalInvoice::factory()->make();

    Livewire::test(CreateRentalInvoice::class)
        ->fillForm([
            'date' => $valid->date,
            'due_date' => $valid->due_date,
            'tenant_name' => $valid->tenant_name,
            'landlord_name' => $valid->landlord_name,
            'landlord_address' => $valid->landlord_address,
            'landlord_phone' => $valid->landlord_phone,
            'landlord_email' => $valid->landlord_email,
            'description' => $valid->description,
            'rent_amount' => $valid->rent_amount,
            'status' => $valid->status,
            ...$data,
        ])
        ->call('create')
        ->assertHasFormErrors($errors);
})->with([
    '`tenant_name` is required' => [['tenant_name' => null], ['tenant_name' => 'required']],
    '`description` is required' => [['description' => null], ['description' => 'required']],
    '`rent_amount` is required' => [['rent_amount' => null], ['rent_amount' => 'required']],
    '`date` is required' => [['date' => null], ['date' => 'required']],
    '`due_date` is required' => [['due_date' => null], ['due_date' => 'required']],
    '`landlord_name` is required' => [['landlord_name' => null], ['landlord_name' => 'required']],
    '`landlord_email` is required' => [['landlord_email' => null], ['landlord_email' => 'required']],
    '`landlord_email` must be valid email' => [['landlord_email' => 'not-an-email'], ['landlord_email' => 'email']],
]);

it('can generate a PDF from the edit page', function () {
    $invoice = RentalInvoice::factory()->create();

    Livewire::test(EditRentalInvoice::class, ['record' => $invoice->getRouteKey()])
        ->callAction('generatePdf')
        ->assertFileDownloaded("rental-invoice-{$invoice->id}-{$invoice->date->format('Y-m-d')}.pdf");
});

it('can generate a PDF from the table row', function () {
    $invoice = RentalInvoice::factory()->create();

    Livewire::test(ListRentalInvoices::class)
        ->callAction(TestAction::make('generatePdf')->table($invoice))
        ->assertFileDownloaded("rental-invoice-{$invoice->id}-{$invoice->date->format('Y-m-d')}.pdf");
});
