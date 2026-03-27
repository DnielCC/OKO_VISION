<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'oko@admin.com';
$password = '12345678';

echo "Setting password for $email to $password\n";

$user = User::where('email', $email)->first();
if ($user) {
    echo "Found user, updating...\n";
    $user->password = Hash::make($password);
    $user->save();
} else {
    echo "User not found, creating...\n";
    $user = new User;
    $user->name = 'Administrator';
    $user->email = $email;
    $user->password = Hash::make($password);
    $user->email_verified_at = now();
    $user->save();
}
echo "Process complete. User password hash in DB: " . $user->password . "\n";
