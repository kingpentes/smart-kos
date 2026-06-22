<?php

use App\Models\User;

$user = User::where('email', 'admin@admin.com')->first();
if ($user) {
    $user->password = bcrypt('password123');
    $user->save();
    echo "Password updated successfully.\n";
} else {
    echo "User not found.\n";
}
