<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $invoices = Invoice::with(['lease.boardingHouse', 'lease.room'])
            ->whereHas('lease', function ($query) use ($request) {
                $query->where('tenant_id', $request->user()->id);
            })
            ->latest()
            ->paginate(10);

        return view('tenant.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        return view('tenant.invoices.show', [
            'invoice' => $invoice->load(['lease.boardingHouse', 'lease.room', 'payments']),
        ]);
    }
}
