<?php

namespace App\Actions\Complaints;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;

class UpdateComplaintStatus
{
    public function handle(Complaint $complaint, ComplaintStatus $status): Complaint
    {
        $complaint->update([
            'status' => $status,
        ]);

        return $complaint->refresh();
    }
}
