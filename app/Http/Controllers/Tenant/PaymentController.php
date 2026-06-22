<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\Billing\CreateMidtransPayment;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller
{
    public function store(Invoice $invoice, CreateMidtransPayment $createMidtransPayment): RedirectResponse
    {
        $this->authorize('view', $invoice);

        $payment = $createMidtransPayment->handle($invoice);

        return redirect()->away((string) $payment->raw_payload['redirect_url']);
    }
}
