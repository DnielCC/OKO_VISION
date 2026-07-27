<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Alert;
use Carbon\Carbon;

class AlertSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $ubicaciones = [
            ['location' => 'Entrada Principal', 'camera' => 'Cámara 1'],
            ['location' => 'Salida Secundaria', 'camera' => 'Cámara 3'],
            ['location' => 'Zona de Carga',  'camera' => 'Cámara 2'],
            ['location' => 'Jardines',          'camera' => 'Cámara 4'],
            ['location' => 'Estacionamiento A', 'camera' => 'Cámara 5'],
            ['location' => 'Estacionamiento B', 'camera' => 'Cámara 6'],
            ['location' => 'Recepción',         'camera' => 'Cámara 7'],
        ];

        $alertas = [
            // Críticas - Pendientes
            [
                'title'         => 'Acceso no autorizado detectado',
                'description'   => 'Vehículo intentó ingresar sin permiso al complejo',
                'severity'      => 'CRITICAL',
                'status'        => Alert::STATUS_PENDING,
                'is_resolved'   => false,
                'vehicle_plate' => 'XYZ-999',
                'vehicle_owner' => 'No registrado',
                'created_diff'  => '-2 hours',
            ],
            [
                'title'         => 'Intento de acceso forzado',
                'description'   => 'Se detectó manipulación en la barrera física de entrada',
                'severity'      => 'CRITICAL',
                'status'        => Alert::STATUS_RESOLVED,
                'is_resolved'   => true,
                'vehicle_plate' => 'GHI-789',
                'vehicle_owner' => 'Desconocido',
                'created_diff'  => '-6 hours',
                'resolved_diff' => '-5 hours',
            ],
            [
                'title'         => 'Presencia no autorizada en zona restringida',
                'description'   => 'Persona detectada caminando por Zona de Carga fuera de horario',
                'severity'      => 'CRITICAL',
                'status'        => Alert::STATUS_REVIEW,
                'is_resolved'   => false,
                'vehicle_plate' => null,
                'vehicle_owner' => null,
                'created_diff'  => '-45 minutes',
            ],

            // Altas
            [
                'title'         => 'Detección de placa falsificada',
                'description'   => 'IA detectó inconsistencias en la placa durante el reconocimiento',
                'severity'      => 'HIGH',
                'status'        => Alert::STATUS_REVIEW,
                'is_resolved'   => false,
                'vehicle_plate' => 'ABC-123',
                'vehicle_owner' => 'Juan Pérez',
                'created_diff'  => '-3 hours',
            ],
            [
                'title'         => 'Velocidad excedida en interior',
                'description'   => 'Vehículo circulando a más de 30 km/h en avenida interna',
                'severity'      => 'HIGH',
                'status'        => Alert::STATUS_VALIDATED,
                'is_resolved'   => true,
                'vehicle_plate' => 'MNO-345',
                'vehicle_owner' => 'Carlos Ramírez',
                'created_diff'  => '-1 day',
                'resolved_diff' => '-22 hours',
            ],
            [
                'title'         => 'Acceso peatonal sin credencial',
                'description'   => 'Ingreso de persona sin lectura de tarjeta en acceso 2',
                'severity'      => 'HIGH',
                'status'        => Alert::STATUS_REPORTED,
                'is_resolved'   => false,
                'vehicle_plate' => null,
                'vehicle_owner' => null,
                'created_diff'  => '-5 hours',
            ],

            // Medias
            [
                'title'         => 'Vehículo estacionado en zona prohibida',
                'description'   => 'Superó el tiempo permitido en zona de carga y descarga',
                'severity'      => 'MEDIUM',
                'status'        => Alert::STATUS_RESOLVED,
                'is_resolved'   => true,
                'vehicle_plate' => 'DEF-456',
                'vehicle_owner' => 'María López',
                'created_diff'  => '-5 hours',
                'resolved_diff' => '-4 hours',
            ],
            [
                'title'         => 'Conteo masivo de personas en recepción',
                'description'   => 'Se detectó aglomeración mayor a 15 personas en recepción',
                'severity'      => 'MEDIUM',
                'status'        => Alert::STATUS_PENDING,
                'is_resolved'   => false,
                'vehicle_plate' => null,
                'vehicle_owner' => null,
                'created_diff'  => '-30 minutes',
            ],
            [
                'title'         => 'Objeto abandonado detectado',
                'description'   => 'Caja de cartón en pasillo principal sin propietario aparente',
                'severity'      => 'MEDIUM',
                'status'        => Alert::STATUS_VALIDATED,
                'is_resolved'   => true,
                'vehicle_plate' => null,
                'vehicle_owner' => null,
                'created_diff'  => '-2 days',
                'resolved_diff' => '-2 days',
            ],

            // Bajas
            [
                'title'         => 'Detección de animal en vía',
                'description'   => 'Gato detectado cerca de la entrada principal, sin riesgo',
                'severity'      => 'LOW',
                'status'        => Alert::STATUS_RESOLVED,
                'is_resolved'   => true,
                'vehicle_plate' => null,
                'vehicle_owner' => 'Animal',
                'created_diff'  => '-7 hours',
                'resolved_diff' => '-7 hours',
            ],
            [
                'title'         => 'Fallo temporal en cámara',
                'description'   => 'Cámara 6 mostró señal degradada por 4 minutos, luego se restableció',
                'severity'      => 'LOW',
                'status'        => Alert::STATUS_RESOLVED,
                'is_resolved'   => true,
                'vehicle_plate' => null,
                'vehicle_owner' => null,
                'created_diff'  => '-1 day',
                'resolved_diff' => '-1 day',
            ],
            [
                'title'         => 'Reconocimiento facial con baja confianza',
                'description'   => 'Coincidencia del 62%, usuario accedió con tarjeta física',
                'severity'      => 'LOW',
                'status'        => Alert::STATUS_PENDING,
                'is_resolved'   => false,
                'vehicle_plate' => null,
                'vehicle_owner' => null,
                'created_diff'  => '-15 minutes',
            ],
            [
                'title'         => 'Modelo de IA actualizado exitosamente',
                'description'   => 'Nuevo peso yolov8n-26jul desplegado en backend, métricas OK',
                'severity'      => 'LOW',
                'status'        => Alert::STATUS_RESOLVED,
                'is_resolved'   => true,
                'vehicle_plate' => null,
                'vehicle_owner' => null,
                'created_diff'  => '-3 days',
                'resolved_diff' => '-3 days',
            ],
        ];

        foreach ($alertas as $i => $a) {
            $ubi = $ubicaciones[$i % count($ubicaciones)];
            $createdAt = $now->copy()->modify($a['created_diff']);
            $resolvedAt = isset($a['resolved_diff'])
                ? $now->copy()->modify($a['resolved_diff'])
                : null;

            Alert::create([
                'title'         => $a['title'],
                'description'   => $a['description'],
                'severity'      => $a['severity'],
                'status'        => $a['status'],
                'is_resolved'   => $a['is_resolved'],
                'vehicle_plate' => $a['vehicle_plate'],
                'vehicle_owner' => $a['vehicle_owner'],
                'location'      => $ubi['location'],
                'camera'        => $ubi['camera'],
                'created_at'    => $createdAt,
                'resolved_at'   => $resolvedAt,
                'image_url'     => null,
                'notes'         => null,
            ]);
        }
    }
}
