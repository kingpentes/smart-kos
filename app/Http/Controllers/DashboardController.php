<?php

namespace App\Http\Controllers;

use App\Enums\BoardingHouseStatus;
use App\Enums\BookingStatus;
use App\Enums\ComplaintStatus;
use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Enums\UserRole;
use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        return match ($request->user()->role) {
            UserRole::Admin => redirect()->route('admin.dashboard'),
            UserRole::Owner => $request->user()->activeSubscription() !== null 
                                ? redirect()->route('owner.dashboard') 
                                : redirect()->route('owner.listings.index'),
            UserRole::Tenant => redirect()->route('tenant.dashboard'),
        };
    }

    public function tenant(Request $request): View
    {
        return view('dashboards.tenant', [
            'activeLease' => $request->user()
                ->leases()
                ->where('status', LeaseStatus::Active->value)
                ->with(['boardingHouse', 'room', 'review'])
                ->latest()
                ->first(),
            'bookings' => $request->user()
                ->bookings()
                ->with([
                    'boardingHouse',
                    'room',
                    'lease.invoices' => fn ($query) => $query->latest(),
                ])
                ->latest()
                ->limit(5)
                ->get(),
            'unpaidInvoices' => $request->user()
                ->tenantInvoices()
                ->whereIn('invoices.status', [InvoiceStatus::Unpaid->value, InvoiceStatus::Overdue->value])
                ->with(['lease.boardingHouse', 'lease.room'])
                ->latest('invoices.created_at')
                ->limit(5)
                ->get(),
            'latestComplaints' => $request->user()
                ->complaints()
                ->with(['lease.boardingHouse', 'lease.room'])
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    public function owner(Request $request): View
    {
        $owner = $request->user();

        return view('dashboards.owner', [
            'totalListings' => $owner->boardingHouses()->count(),
            'publishedListings' => $owner->boardingHouses()
                ->where('status', BoardingHouseStatus::Published->value)
                ->count(),
            'pendingListings' => $owner->boardingHouses()
                ->where('status', BoardingHouseStatus::Pending->value)
                ->count(),
            'pendingBookings' => Booking::query()
                ->whereHas('boardingHouse', fn ($query) => $query->where('owner_id', $owner->id))
                ->where('status', BookingStatus::Pending->value)
                ->count(),
            'activeLeases' => $owner->ownedLeases()
                ->where('status', LeaseStatus::Active->value)
                ->count(),
            'unpaidInvoices' => $owner->ownerInvoices()
                ->whereIn('invoices.status', [InvoiceStatus::Unpaid->value, InvoiceStatus::Overdue->value])
                ->count(),
            'paidInvoices' => $owner->ownerInvoices()
                ->where('invoices.status', InvoiceStatus::Paid->value)
                ->count(),
            'openComplaints' => $owner->ownedComplaints()
                ->whereIn('status', [ComplaintStatus::Open->value, ComplaintStatus::InProgress->value])
                ->count(),
            'latestBookings' => Booking::query()
                ->whereHas('boardingHouse', fn ($query) => $query->where('owner_id', $owner->id))
                ->with(['boardingHouse', 'room', 'tenant'])
                ->latest()
                ->limit(5)
                ->get(),
            'latestComplaints' => $owner->ownedComplaints()
                ->with(['lease.boardingHouse', 'lease.room', 'tenant'])
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    public function admin(): View
    {
        return view('dashboards.admin', [
            'totalUsers' => User::query()->count(),
            'pendingListings' => BoardingHouse::query()
                ->where('status', BoardingHouseStatus::Pending->value)
                ->count(),
            'publishedListings' => BoardingHouse::query()
                ->where('status', BoardingHouseStatus::Published->value)
                ->count(),
            'latestPendingListings' => BoardingHouse::query()
                ->where('status', BoardingHouseStatus::Pending->value)
                ->with(['owner'])
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
