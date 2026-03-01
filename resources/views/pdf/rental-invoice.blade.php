@php use App\Helpers\Money; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rental Invoice #{{ $invoice->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 13px; color: #333; padding: 40px; }
        h1 { font-size: 22px; text-align: center; margin-bottom: 30px; border-bottom: 3px solid #1a3a6e; padding-bottom: 10px; }
        h2 { font-size: 15px; font-weight: bold; margin: 20px 0 8px; text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.details td { padding: 5px 10px; border: 1px solid #ccc; }
        table.details td.label { font-weight: bold; width: 40%; background-color: #f5f5f5; }
        table.details td.value { text-align: right; }
        table.rental td { padding: 6px 10px; border: 1px solid #ccc; }
        table.rental th { padding: 6px 10px; border: 1px solid #ccc; background-color: #f5f5f5; font-weight: bold; text-align: left; }
        table.rental td.amount { text-align: right; font-weight: bold; color: #c00; font-size: 15px; }
        a { color: #1a3a6e; }
        .watermark { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); font-size: 48px; color: #e0e0e0; font-weight: bold; z-index: -1; }
    </style>
</head>
<body>
    <div class="watermark">Page 1</div>

    <h1>Rental Apartment - Receipt of Payment</h1>

    <h2>Activities</h2>
    <table class="details">
        <tr>
            <td class="label">Date:</td>
            <td class="value">{{ $invoice->date->format('d-M-y') }}</td>
        </tr>
        <tr>
            <td class="label">Payment Made on</td>
            <td class="value">{{ $invoice->payment_made_on ? $invoice->payment_made_on->format('d-M-y') : '—' }}</td>
        </tr>
        <tr>
            <td class="label">Due Date:</td>
            <td class="value">{{ $invoice->due_date->format('d-M-y') }}</td>
        </tr>
        <tr>
            <td class="label">Tenant (s) Name:</td>
            <td class="value"><strong>{{ $invoice->tenant_name }}</strong></td>
        </tr>
    </table>

    <h2>Landlord Details</h2>
    <table class="details">
        <tr>
            <td class="label">Landlord Name</td>
            <td class="value"><strong>{{ $invoice->landlord_name }}</strong></td>
        </tr>
        <tr>
            <td class="label">Address</td>
            <td class="value"><strong>{{ $invoice->landlord_address }}</strong></td>
        </tr>
        <tr>
            <td class="label">Phone</td>
            <td class="value"><strong>{{ $invoice->landlord_phone }}</strong></td>
        </tr>
        <tr>
            <td class="label">Email</td>
            <td class="value"><strong><a href="mailto:{{ $invoice->landlord_email }}">{{ $invoice->landlord_email }}</a></strong></td>
        </tr>
    </table>

    <h2>Rental Details</h2>
    <table class="rental">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right;">Rent Payment</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $invoice->description }}</td>
                <td class="amount">{{ Money::format($invoice->rent_amount) }}</td>
            </tr>
            @if((float) $invoice->additional_charges > 0)
            <tr>
                <td>Additional Charges</td>
                <td class="amount">{{ Money::format($invoice->additional_charges) }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr style="border-top: 2px solid #333;">
                <th>Total</th>
                <td class="amount">{{ Money::format($invoice->total_amount) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($invoice->notes)
    <h2>Notes</h2>
    <p style="padding: 10px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
        {{ $invoice->notes }}
    </p>
    @endif
</body>
</html>
