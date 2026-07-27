<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== TODAS LAS TABLAS DE LA BD ===\n";
$tabs = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema='public' ORDER BY table_name;");
foreach ($tabs as $t) echo "  - {$t->table_name}\n";

echo "\n=== COUNT AccessLog / Alert / User / Vehicle ===\n";
echo "access_logs: " . DB::table('access_logs')->count() . "\n";
echo "alerts: " . DB::table('alerts')->count() . "\n";
echo "usuarios: " . DB::table('usuarios')->count() . "\n";
try { echo "vehicles: " . DB::table('vehicles')->count() . "\n"; } catch(\Throwable $e) { echo "vehicles: TABLA NO EXISTE ({$e->getMessage()})\n"; }

if (DB::table('access_logs')->count() > 0) {
    echo "\n=== Muestra access_logs (últimos 5) ===\n";
    foreach (DB::table('access_logs')->orderByDesc('access_time')->limit(5)->get() as $a) {
        echo "  id={$a->id} user_id=" . ($a->user_id??'null') . " placa={$a->vehicle_plate} tipo={$a->access_type} auth=" . ($a->is_authorized?'SI':'NO') . " time={$a->access_time}\n";
    }
} else {
    echo "\n=== access_logs VACÍA: ¿Seedear con 80 registros? (Aplicación lo haré en el controlador si está vacío)\n";
}

echo "\n=== COLUMAS access_logs ===\n";
$cols = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema='public' AND table_name='access_logs' ORDER BY ordinal_position;");
foreach ($cols as $c) echo "  {$c->column_name} ({$c->data_type})\n";
