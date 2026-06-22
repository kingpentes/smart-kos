<?php

namespace App\Http\Controllers\Owner;

use App\Actions\Complaints\ReplyToComplaint;
use App\Actions\Complaints\UpdateComplaintStatus;
use App\Enums\ComplaintStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Complaints\ReplyComplaintRequest;
use App\Models\Complaint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Complaint::class);

        return view('owner.complaints.index', [
            'complaints' => $request->user()
                ->ownedComplaints()
                ->with(['lease.boardingHouse', 'lease.room', 'tenant'])
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'statuses' => ComplaintStatus::cases(),
            'selectedStatus' => $request->query('status'),
        ]);
    }

    public function show(Complaint $complaint): View
    {
        $this->authorize('view', $complaint);

        return view('owner.complaints.show', [
            'complaint' => $complaint->load(['lease.boardingHouse', 'lease.room', 'tenant', 'photos', 'replies.user']),
            'statuses' => ComplaintStatus::cases(),
        ]);
    }

    public function reply(
        ReplyComplaintRequest $request,
        Complaint $complaint,
        ReplyToComplaint $replyToComplaint,
        UpdateComplaintStatus $updateComplaintStatus
    ): RedirectResponse {
        $this->authorize('reply', $complaint);

        $replyToComplaint->handle($complaint, $request->user(), $request->validated('message'));

        if ($request->filled('status')) {
            $this->authorize('updateStatus', $complaint);
            $updateComplaintStatus->handle($complaint, ComplaintStatus::from($request->validated('status')));
        }

        return redirect()
            ->route('owner.complaints.show', $complaint)
            ->with('status', 'Keluhan diperbarui.');
    }

    public function updateStatus(
        Request $request,
        Complaint $complaint,
        UpdateComplaintStatus $updateComplaintStatus
    ): RedirectResponse {
        $this->authorize('updateStatus', $complaint);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(ComplaintStatus::class)],
        ]);

        $updateComplaintStatus->handle($complaint, ComplaintStatus::from($validated['status']));

        return redirect()
            ->route('owner.complaints.show', $complaint)
            ->with('status', 'Status keluhan diperbarui.');
    }
}
