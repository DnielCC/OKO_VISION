<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'admin@okovision.com';
$password = '12345678';

$user = App\Models\User::where('email', $email)->first();
if (Illuminate\Support\Facades\Hash::check($password, $user->password)) {
    echo "FINAL_VERIFICATION: SUCCESS\n";
} else {
    echo "FINAL_VERIFICATION: FAILED\n";
}
