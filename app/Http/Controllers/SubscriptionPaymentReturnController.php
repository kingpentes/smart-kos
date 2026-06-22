<?php

namespace App\Http\Controllers;

use App\Actions\Subscriptions\UpdateSubscriptionPaymentStatus;
use App\Services\Payments\MidtransGateway;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionPaymentReturnController extends Controller
{
    public function __invoke(
        Request $request,
        MidtransGateway $midtransGateway,
        UpdateSubscriptionPaymentStatus $updateSubscriptionPaymentStatus,
    ): View {
        $orderId = $request->string('order_id')->toString();

        $request->user()
            ->subscriptionPayments()
            ->where('provider', 'midtrans')
            ->where('provider_reference', $orderId)
            ->firstOrFail();

        $statusPayload = $midtransGateway->getTransactionStatus($orderId);
        $payment = $updateSubscriptionPaymentStatus->handle($statusPayload, 'status_check');

        return view('subscriptions.success', [
            'payment' => $payment,
        ]);
    }
}
