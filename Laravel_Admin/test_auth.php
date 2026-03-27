<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'admin@okovision.com';
$password = 'admin123';

$user = User::where('email', $email)->first();
if (!$user) {
    echo "User not found\n";
    exit;
}

if (Hash::check($password, $user->password)) {
    echo "Password Check: SUCCESS\n";
} else {
    echo "Password Check: FAILED\n";
}

if (Auth::attempt(['email' => $email, 'password' => $password])) {
    echo "Auth::attempt: SUCCESS\n";
} else {
    echo "Auth::attempt: FAILED\n";
}
