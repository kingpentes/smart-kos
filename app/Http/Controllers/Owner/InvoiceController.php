<?php

namespace App\Http\Controllers\Owner;

use App\Actions\Billing\MarkInvoicePaid;
use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Lease;
use App\Notifications\NewInvoiceNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        return view('owner.invoices.index', [
            'invoices' => Invoice::query()
                ->whereHas('lease', fn ($query) => $query->where('owner_id', $request->user()->id))
                ->with(['lease.boardingHouse', 'lease.room', 'lease.tenant'])
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'statuses' => InvoiceStatus::cases(),
            'selectedStatus' => $request->query('status'),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Invoice::class);

        $boardingHouses = $request->user()->boardingHouses()->orderBy('name')->get();
        $leases = Lease::query()
            ->where('owner_id', $request->user()->id)
            ->where('status', LeaseStatus::Active->value)
            ->with(['boardingHouse', 'tenant', 'room'])
            ->get();

        return view('owner.invoices.create', [
            'boardingHouses' => $boardingHouses,
            'leases' => $leases,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $validated = $request->validate([
            'target_type' => ['required', Rule::in(['all', 'specific'])],
            'boarding_house_id' => ['required_if:target_type,all', 'nullable', 'exists:boarding_houses,id'],
            'lease_id' => ['required_if:target_type,specific', 'nullable', 'exists:leases,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'integer', 'min:1'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $leases = collect();

            if ($validated['target_type'] === 'all') {
                $leases = Lease::query()
                    ->where('owner_id', $request->user()->id)
                    ->where('boarding_house_id', $validated['boarding_house_id'])
                    ->where('status', LeaseStatus::Active->value)
                    ->get();
            } else {
                $lease = Lease::query()
                    ->where('owner_id', $request->user()->id)
                    ->where('id', $validated['lease_id'])
                    ->firstOrFail();
                $leases->push($lease);
            }

            foreach ($leases as $lease) {
                // Generate invoice number
                $datePrefix = now()->format('Ymd');
                $count = Invoice::whereDate('created_at', now()->toDateString())->count() + 1;
                $number = 'INV-'.$datePrefix.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT).'-'.rand(100, 999);

                $invoice = Invoice::create([
                    'lease_id' => $lease->id,
                    'number' => $number,
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'period_start' => now(), // Can be null, but nullable is not set in DB
                    'period_end' => now(), // Using now() as default for custom invoices
                    'due_date' => $validated['due_date'],
                    'amount' => $validated['amount'],
                    'status' => InvoiceStatus::Unpaid,
                ]);

                // Kirim notifikasi
                $lease->tenant->notify(new NewInvoiceNotification($invoice));
            }
        });

        return redirect()
            ->route('owner.invoices.index')
            ->with('status', 'Tagihan berhasil dibuat.');
    }

    public function markPaid(Invoice $invoice, MarkInvoicePaid $markInvoicePaid): RedirectResponse
    {
        $this->authorize('markPaid', $invoice);

        $markInvoicePaid->handle($invoice);

        return redirect()
            ->route('owner.invoices.index')
            ->with('status', 'Tagihan ditandai lunas.');
    }
}
