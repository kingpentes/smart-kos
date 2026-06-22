<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\Billing\UpdateMidtransPaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\MidtransGateway;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MidtransPaymentReturnController extends Controller
{
    public function __invoke(
        Request $request,
        MidtransGateway $midtransGateway,
        UpdateMidtransPaymentStatus $updateMidtransPaymentStatus,
    ): View {
        $orderId = (string) $request->string('order_id');

        $payment = Payment::query()
            ->where('provider', 'midtrans')
            ->where('provider_reference', $orderId)
            ->with('invoice')
            ->firstOrFail();

        $this->authorize('view', $payment->invoice);

        $statusPayload = $midtransGateway->getTransactionStatus($orderId);
        $payment = $updateMidtransPaymentStatus->handle($statusPayload, 'status_check');

        return view('tenant.payments.success', [
            'payment' => $payment,
            'invoice' => $payment->invoice,
        ]);
    }
}
