<?php

namespace App\Http\Controllers;

use App\Actions\Billing\HandleMidtransNotification;
use App\Models\SubscriptionPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MidtransWebhookController extends Controller
{
    public function __invoke(Request $request, HandleMidtransNotification $handleMidtransNotification): JsonResponse
    {
        try {
            $payment = $handleMidtransNotification->handle($request->all());
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'payment_id' => $payment->id,
            'payment_type' => $payment instanceof SubscriptionPayment ? 'subscription' : 'invoice',
            'status' => $payment->status->value,
        ]);
    }
}
