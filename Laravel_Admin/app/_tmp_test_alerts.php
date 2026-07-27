<?php

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Alert;
use Illuminate\Http\Request;

echo "=== Test 1: Query básico Alert ===\n";
try {
    $filters = [];
    $query = Alert::query()->applyFilters($filters)->latest('created_at');
    echo "query OK\n";

    $counts = (clone $query)
        ->selectRaw('alerts.severity, count(*) as total')
        ->groupBy('alerts.severity')
        ->pluck('total', 'alerts.severity')
        ->all();
    echo "counts: "; print_r($counts);

    $total = (clone $query)->count();
    echo "total = $total\n";

    $rows = (clone $query)->limit(2)->get();
    echo "rows count = " . $rows->count() . "\n";
    foreach ($rows as $a) {
        echo " - id=" . $a->id . " formatted=" . $a->formatted_id . " sev=" . $a->severity_label . " st=" . $a->status_label . "\n";
        echo "   camera=" . var_export($a->camera, true) . " vehicle=" . var_export($a->vehicle_plate, true) . "\n";
        echo "   agent_type=" . var_export($a->agent_type, true) . " agent_id=" . var_export($a->agent_id, true) . "\n";
        echo "   resolved_at=" . var_export(optional($a->resolved_at)?->format('Y-m-d H:i:s'), true) . "\n";
    }
} catch (\Throwable $e) {
    echo "ERROR 1: " . $e::class . " : " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "TRACE:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test 2: Simular exportarAlertasExcel template ===\n";
try {
    $request = Request::create('/alertas/export/excel', 'GET', []);
    $filters = $request->only(['severity', 'date_range', 'status', 'search']);
    echo "filters: " . json_encode($filters) . "\n";

    $query = Alert::query()->applyFilters($filters)->latest('created_at');
    $rows = (clone $query)->limit(2000)->get();
    echo "total rows to export: " . $rows->count() . "\n";

    $periodLabel = match (strtolower((string)$request->get('date_range'))) {
        '24h'   => 'Últimas 24 horas',
        '7d'    => 'Últimos 7 días',
        '30d'   => 'Últimos 30 días',
        'all'   => 'Todas',
        default => 'Últimas 24 horas',
    };
    echo "periodLabel = $periodLabel\n";

    ob_start();
    echo "\xEF\xBB\xBF";
    ?>
    <html>
    <head><meta charset="UTF-8"></head>
    <body>
        <p>OKO VISION TEST</p>
        <p>Generado: <?php echo now()->format('d/m/Y H:i:s'); ?></p>
        <p>Periodo: <?php echo $periodLabel; ?></p>
        <table>
            <tr><th>ID</th><th>Folio</th><th>Título</th></tr>
            <?php foreach ($rows as $a): ?>
            <tr>
                <td><?php echo $a->id; ?></td>
                <td><?php echo $a->formatted_id; ?></td>
                <td><?php echo e($a->title); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </body>
    </html>
    <?php
    $content = ob_get_clean();
    echo "Template generated OK, length=" . strlen($content) . "\n";
    echo "First 150 chars: " . substr($content, 0, 150) . "\n";
} catch (\Throwable $e) {
    echo "ERROR 2: " . $e::class . " : " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "TRACE:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test 3: Llamar directamente el Controller method ===\n";
try {
    $request = Request::create('/alertas/export/excel', 'GET', []);
    $controller = new \App\Http\Controllers\DashboardController(app(\App\Services\ApiService::class));
    $response = $controller->exportarAlertasExcel($request);
    echo "Response type: " . $response::class . "\n";
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response headers: " . json_encode($response->headers->all()) . "\n";
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "IT IS A REDIRECT! target = " . $response->getTargetUrl() . "\n";
        $session = $response->getSession();
        if ($session) {
            echo "Session error: " . var_export($session->get('error'), true) . "\n";
        }
    }
} catch (\Throwable $e) {
    echo "ERROR 3: " . $e::class . " : " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "TRACE:\n" . $e->getTraceAsString() . "\n";
}
