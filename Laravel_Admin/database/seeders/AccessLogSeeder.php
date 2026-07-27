<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccessLogSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('access_logs')->count() > 0) {
            return;
        }

        // Recolectar usuarios reales + sus nombres (para asignar user_id válido)
        $usuarios = DB::table('usuarios')
            ->join('personas', 'usuarios.id_persona', '=', 'personas.id')
            ->select('usuarios.id as usuario_id', 'usuarios.id_rol', 'personas.nombre', 'personas.apellidos')
            ->get()
            ->keyBy('usuario_id');

        if ($usuarios->isEmpty()) {
            return;
        }

        $placas = ['ABC-123', 'DEF-456', 'GHI-789', 'JKL-012', 'MNO-345', 'PQR-678', 'STU-901', 'VWX-234', 'YZA-567', 'BCD-890'];
        $tipos = ['ENTRY', 'EXIT'];
        $total = 120;

        // Distribución ponderada por hora (picos 7-9 y 17-19, valle 0-5)
        $hourWeights = [
            0=>1,1=>1,2=>1,3=>0,4=>1,5=>2,6=>4,
            7=>10,8=>14,9=>9,10=>7,11=>6,12=>7,
            13=>6,14=>7,15=>8,16=>9,17=>13,18=>11,
            19=>7,20=>5,21=>3,22=>2,23=>1,
        ];
        $sumWeights = array_sum($hourWeights);
        $hours = [];
        foreach ($hourWeights as $h => $w) {
            for ($i=0;$i<$w;$i++) $hours[] = $h;
        }

        $rows = [];
        $now = Carbon::now();

        for ($i = 0; $i < $total; $i++) {
            $daysAgo = rand(0, 29); // últimos 30 días
            $hour = $hours[array_rand($hours)];
            $minute = rand(0, 59);
            $second = rand(0, 59);
            $time = (clone $now)->subDays($daysAgo)->setTime($hour, $minute, $second);

            $userId = $usuarios->keys()->random();

            $tipo = $tipos[array_rand($tipos)];
            $placa = $placas[array_rand($placas)];

            // Autorizados ~87%, Denegados ~13%
            $isAuth = (mt_rand(1,100) <= 87);

            $rows[] = [
                'user_id'       => $userId,
                'vehicle_plate' => $placa,
                'access_time'   => $time->format('Y-m-d H:i:s'),
                'access_type'   => $tipo,
                'is_authorized' => $isAuth ? 1 : 0,
            ];
        }

        // Ordenar por tiempo ascendente para que tengan coherencia
        usort($rows, fn($a,$b) => $a['access_time'] <=> $b['access_time']);

        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('access_logs')->insert($chunk);
        }
    }
}
