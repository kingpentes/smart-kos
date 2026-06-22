<?php

namespace App\Actions\Complaints;

use App\Models\Complaint;
use App\Models\ComplaintReply;
use App\Models\User;

class ReplyToComplaint
{
    public function handle(Complaint $complaint, User $user, string $message): ComplaintReply
    {
        return $complaint->replies()->create([
            'user_id' => $user->id,
            'message' => $message,
        ]);
    }
}
