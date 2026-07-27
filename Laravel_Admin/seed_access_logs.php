<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Seed bypass usando SQL directo (sin pasar por la FK que apunta a tabla users incorrecta).
// Si la FK existe y apunta a "users" (tabla Laravel en blanco), la borramos; nuestra tabla real es "usuarios".
try {
    $fkRow = DB::selectOne("SELECT conname FROM pg_constraint WHERE conname = 'access_logs_user_id_foreign'");
    if ($fkRow) {
        DB::statement("ALTER TABLE access_logs DROP CONSTRAINT access_logs_user_id_foreign");
        echo "DROP FK access_logs_user_id_foreign (apuntaba a tabla users incorrecta).\n";
    }
} catch (\Throwable $e) {
    echo "FK check: {$e->getMessage()}\n";
}

// Ahora seedear con la misma data del AccessLogSeeder
if (DB::table('access_logs')->count() > 0) {
    echo "access_logs ya tiene datos (count=" . DB::table('access_logs')->count() . ") - omitimos seeder.\n";
    exit(0);
}

$usuarios = DB::table('usuarios')
    ->join('personas', 'usuarios.id_persona', '=', 'personas.id')
    ->select('usuarios.id as usuario_id')
    ->get()
    ->pluck('usuario_id')
    ->all();

if (empty($usuarios)) {
    echo "No hay usuarios; abortando.\n";
    exit(1);
}

$placas = ['ABC-123', 'DEF-456', 'GHI-789', 'JKL-012', 'MNO-345', 'PQR-678', 'STU-901', 'VWX-234', 'YZA-567', 'BCD-890'];
$tipos = ['ENTRY', 'EXIT'];
$total = 120;

$hourWeights = [
    0=>1,1=>1,2=>1,3=>0,4=>1,5=>2,6=>4,
    7=>10,8=>14,9=>9,10=>7,11=>6,12=>7,
    13=>6,14=>7,15=>8,16=>9,17=>13,18=>11,
    19=>7,20=>5,21=>3,22=>2,23=>1,
];
$hours = [];
foreach ($hourWeights as $h => $w) for ($i=0;$i<$w;$i++) $hours[] = $h;
$sumHours = count($hours);

$rows = [];
$now = Carbon::now();

for ($i = 0; $i < $total; $i++) {
    $daysAgo = rand(0, 29);
    $hour = $hours[array_rand($hours)];
    $minute = rand(0, 59);
    $second = rand(0, 59);
    $time = (clone $now)->subDays($daysAgo)->setTime($hour, $minute, $second);
    $userId = $usuarios[array_rand($usuarios)];
    $tipo = $tipos[array_rand($tipos)];
    $placa = $placas[array_rand($placas)];
    $isAuth = (mt_rand(1,100) <= 87);
    $rows[] = [
        'user_id'       => $userId,
        'vehicle_plate' => $placa,
        'access_time'   => $time->format('Y-m-d H:i:s'),
        'access_type'   => $tipo,
        'is_authorized' => $isAuth ? 1 : 0,
    ];
}
usort($rows, fn($a,$b) => $a['access_time'] <=> $b['access_time']);

foreach (array_chunk($rows, 50) as $chunk) {
    DB::table('access_logs')->insert($chunk);
}

echo "Insertados " . DB::table('access_logs')->count() . " access_logs.\n";
