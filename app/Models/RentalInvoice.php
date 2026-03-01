<?php

namespace App\Models;

use App\Enums\RentalInvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalInvoice extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'date',
        'payment_made_on',
        'due_date',
        'tenant_name',
        'tenant_address',
        'tenant_phone',
        'tenant_email',
        'description',
        'rent_amount',
        'additional_charges',
        'total_amount',
        'landlord_name',
        'landlord_address',
        'landlord_phone',
        'landlord_email',
        'notes',
        'status',
        'pdf_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'payment_made_on' => 'date',
            'due_date' => 'date',
            'rent_amount' => 'decimal:2',
            'additional_charges' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'status' => RentalInvoiceStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (RentalInvoice $invoice): void {
            $invoice->total_amount = bcadd(
                (string) ($invoice->rent_amount ?? '0'),
                (string) ($invoice->additional_charges ?? '0'),
                2,
            );
        });
    }
}
