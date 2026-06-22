<?php

namespace App\Actions\Complaints;

use App\Enums\ComplaintStatus;
use App\Enums\LeaseStatus;
use App\Models\Complaint;
use App\Models\Lease;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateComplaint
{
    /**
     * @param  array{lease_id: int, category: string, description: string, photos?: array<int, UploadedFile>}  $data
     *
     * @throws ValidationException
     */
    public function handle(User $tenant, array $data): Complaint
    {
        return DB::transaction(function () use ($tenant, $data): Complaint {
            $lease = Lease::query()
                ->whereKey($data['lease_id'])
                ->where('tenant_id', $tenant->id)
                ->where('status', LeaseStatus::Active->value)
                ->with('boardingHouse')
                ->first();

            if (! $lease) {
                throw ValidationException::withMessages([
                    'lease_id' => 'Sewa aktif tidak ditemukan.',
                ]);
            }

            $complaint = Complaint::query()->create([
                'lease_id' => $lease->id,
                'tenant_id' => $tenant->id,
                'owner_id' => $lease->owner_id,
                'category' => $data['category'],
                'description' => $data['description'],
                'status' => ComplaintStatus::Open,
            ]);

            foreach ($data['photos'] ?? [] as $photo) {
                $complaint->photos()->create([
                    'path' => $photo->store('complaints', 'public'),
                ]);
            }

            return $complaint;
        });
    }
}
