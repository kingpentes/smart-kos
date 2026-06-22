<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\Complaints\CreateComplaint;
use App\Actions\Complaints\ReplyToComplaint;
use App\Enums\LeaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Complaints\ReplyComplaintRequest;
use App\Http\Requests\Complaints\StoreComplaintRequest;
use App\Models\Complaint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Complaint::class);

        return view('tenant.complaints.index', [
            'complaints' => $request->user()
                ->complaints()
                ->with(['lease.boardingHouse', 'lease.room'])
                ->latest()
                ->paginate(10),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Complaint::class);

        return view('tenant.complaints.create', [
            'leases' => $request->user()
                ->leases()
                ->with(['boardingHouse', 'room'])
                ->where('status', LeaseStatus::Active->value)
                ->latest()
                ->get(),
        ]);
    }

    public function store(StoreComplaintRequest $request, CreateComplaint $createComplaint): RedirectResponse
    {
        $this->authorize('create', Complaint::class);

        $complaint = $createComplaint->handle($request->user(), $request->validated());

        return redirect()
            ->route('tenant.complaints.show', $complaint)
            ->with('status', 'Keluhan berhasil dikirim.');
    }

    public function show(Complaint $complaint): View
    {
        $this->authorize('view', $complaint);

        return view('tenant.complaints.show', [
            'complaint' => $complaint->load(['lease.boardingHouse', 'lease.room', 'photos', 'replies.user']),
        ]);
    }

    public function reply(
        ReplyComplaintRequest $request,
        Complaint $complaint,
        ReplyToComplaint $replyToComplaint
    ): RedirectResponse {
        $this->authorize('reply', $complaint);

        $replyToComplaint->handle($complaint, $request->user(), $request->validated('message'));

        return redirect()
            ->route('tenant.complaints.show', $complaint)
            ->with('status', 'Balasan terkirim.');
    }
}
