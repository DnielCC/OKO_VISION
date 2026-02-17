@extends('layouts.app')

@section('content')
<!-- Header Section -->
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-white">Reportes y Estadísticas</h2>
        <p class="text-gray-400 mt-1">Análisis detallado del sistema de control de acceso</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="exportReport('pdf')" class="btn-secondary">
            <i class="fas fa-file-pdf mr-2"></i>
            Exportar PDF
        </button>
        <button onclick="exportReport('excel')" class="btn-secondary">
            <i class="fas fa-file-excel mr-2"></i>
            Exportar Excel
        </button>
    </div>
</div>

<!-- Date Range Filter -->
<div class="card mb-6">
    <div class="flex flex-wrap items-center gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Período</label>
            <select id="periodSelect" class="input-field" style="width: auto;">
                <option value="today">Hoy</option>
                <option value="week" selected>Última Semana</option>
                <option value="month">Último Mes</option>
                <option value="quarter">Último Trimestre</option>
                <option value="year">Último Año</option>
                <option value="custom">Personalizado</option>
            </select>
        </div>
        
        <div id="customDateRange" class="hidden">
            <label class="block text-sm font-medium text-gray-300 mb-2">Rango de Fechas</label>
            <div class="flex items-center gap-2">
                <input type="date" id="startDate" class="input-field">
                <span class="text-gray-400">a</span>
                <input type="date" id="endDate" class="input-field">
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Tipo de Reporte</label>
            <select class="input-field" style="width: auto;">
                <option>Todos los datos</option>
                <option>Accesos</option>
                <option>Alertas</option>
                <option>Usuarios</option>
                <option>Vehículos</option>
            </select>
        </div>
        
        <button onclick="refreshData()" class="btn-primary mt-6">
            <i class="fas fa-sync-alt mr-2"></i>
            Actualizar
        </button>
    </div>
</div>

<!-- Key Metrics -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Total Accesos</p>
                <p class="text-2xl font-bold text-white">8,456</p>
                <p class="text-green-400 text-xs mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>15.3% vs período anterior
                </p>
            </div>
            <div class="w-12 h-12 bg-cyan-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-door-open text-cyan-400 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Usuarios Activos</p>
                <p class="text-2xl font-bold text-white">1,247</p>
                <p class="text-green-400 text-xs mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>8.2% crecimiento
                </p>
            </div>
            <div class="w-12 h-12 bg-green-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-green-400 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Alertas Críticas</p>
                <p class="text-2xl font-bold text-red-400">47</p>
                <p class="text-red-400 text-xs mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>23% más que el mes pasado
                </p>
            </div>
            <div class="w-12 h-12 bg-red-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Tasa Detección IA</p>
                <p class="text-2xl font-bold text-white">98.7%</p>
                <p class="text-green-400 text-xs mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>0.3% mejora
                </p>
            </div>
            <div class="w-12 h-12 bg-purple-400/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-brain text-purple-400 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Access Trends Chart -->
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Tendencia de Accesos</h3>
            <div class="flex items-center gap-2">
                <button class="text-gray-400 hover:text-white transition-colors">
                    <i class="fas fa-expand"></i>
                </button>
            </div>
        </div>
        
        <div class="relative" style="height: 300px;">
            <!-- Line Chart Placeholder -->
            <div class="absolute inset-0 flex items-center justify-center bg-gray-800/50 rounded-lg">
                <div class="text-center">
                    <i class="fas fa-chart-line text-4xl text-cyan-400 mb-3"></i>
                    <p class="text-gray-400">Gráfico de Líneas - Tendencia de Accesos</p>
                    <p class="text-gray-500 text-sm mt-2">Últimos 7 días</p>
                </div>
            </div>
            
            <!-- Simulated Chart Data -->
            <div class="absolute bottom-0 left-0 right-0 h-64 flex items-end justify-around px-4">
                <div class="bg-cyan-400/30 w-8 rounded-t" style="height: 60%"></div>
                <div class="bg-cyan-400/30 w-8 rounded-t" style="height: 75%"></div>
                <div class="bg-cyan-400/30 w-8 rounded-t" style="height: 45%"></div>
                <div class="bg-cyan-400/30 w-8 rounded-t" style="height: 80%"></div>
                <div class="bg-cyan-400/30 w-8 rounded-t" style="height: 90%"></div>
                <div class="bg-cyan-400/30 w-8 rounded-t" style="height: 70%"></div>
                <div class="bg-cyan-400/30 w-8 rounded-t" style="height: 85%"></div>
            </div>
        </div>
        
        <!-- Chart Legend -->
        <div class="flex items-center justify-center gap-6 mt-4">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-cyan-400 rounded-full"></div>
                <span class="text-gray-400 text-sm">Entradas</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                <span class="text-gray-400 text-sm">Salidas</span>
            </div>
        </div>
    </div>
    
    <!-- Access Distribution Chart -->
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Distribución por Tipo</h3>
            <div class="flex items-center gap-2">
                <button class="text-gray-400 hover:text-white transition-colors">
                    <i class="fas fa-expand"></i>
                </button>
            </div>
        </div>
        
        <div class="relative" style="height: 300px;">
            <!-- Pie Chart Placeholder -->
            <div class="absolute inset-0 flex items-center justify-center bg-gray-800/50 rounded-lg">
                <div class="text-center">
                    <i class="fas fa-chart-pie text-4xl text-cyan-400 mb-3"></i>
                    <p class="text-gray-400">Gráfico Circular - Distribución</p>
                    <p class="text-gray-500 text-sm mt-2">Por tipo de acceso</p>
                </div>
            </div>
            
            <!-- Simulated Pie Chart -->
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="relative w-48 h-48">
                    <div class="absolute inset-0 rounded-full bg-gradient-to-tr from-cyan-400 to-blue-500"></div>
                    <div class="absolute inset-4 rounded-full bg-gray-900"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-white">100%</p>
                            <p class="text-gray-400 text-sm">Total</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Chart Legend -->
        <div class="grid grid-cols-2 gap-2 mt-4">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-cyan-400 rounded-full"></div>
                <span class="text-gray-400 text-sm">Autorizados (65%)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                <span class="text-gray-400 text-sm">Pendientes (20%)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                <span class="text-gray-400 text-sm">Denegados (10%)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                <span class="text-gray-400 text-sm">Visitantes (5%)</span>
            </div>
        </div>
    </div>
</div>

<!-- Hourly Activity Chart -->
<div class="card mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-white">Actividad por Hora del Día</h3>
        <div class="flex items-center gap-2">
            <button class="text-gray-400 hover:text-white transition-colors">
                <i class="fas fa-expand"></i>
            </button>
        </div>
    </div>
    
    <div class="relative" style="height: 250px;">
        <!-- Bar Chart Placeholder -->
        <div class="absolute inset-0 flex items-center justify-center bg-gray-800/50 rounded-lg">
            <div class="text-center">
                <i class="fas fa-chart-bar text-4xl text-cyan-400 mb-3"></i>
                <p class="text-gray-400">Gráfico de Barras - Actividad Horaria</p>
                <p class="text-gray-500 text-sm mt-2">Patrones de acceso por hora</p>
            </div>
        </div>
        
        <!-- Simulated Bar Chart -->
        <div class="absolute bottom-0 left-0 right-0 h-48 flex items-end justify-around px-4">
            <div class="bg-cyan-400/50 w-6 rounded-t" style="height: 20%"></div>
            <div class="bg-cyan-400/50 w-6 rounded-t" style="height: 15%"></div>
            <div class="bg-cyan-400/50 w-6 rounded-t" style="height: 25%"></div>
            <div class="bg-cyan-400/50 w-6 rounded-t" style="height: 40%"></div>
            <div class="bg-cyan-400/50 w-6 rounded-t" style="height: 60%"></div>
            <div class="bg-cyan-400/50 w-6 rounded-t" style="height: 80%"></div>
            <div class="bg-cyan-400/50 w-6 rounded-t" style="height: 90%"></div>
            <div class="bg-cyan-400/50 w-6 rounded-t" style="height: 85%"></div>
            <div class="bg-cyan-400/50 w-6 rounded-t" style="height: 75%"></div>
            <div class="bg-cyan-400/50 w-6 rounded-t" style="height: 65%"></div>
            <div class="bg-cyan-400/50 w-6 rounded-t" style="height: 45%"></div>
            <div class="bg-cyan-400/50 w-6 rounded-t" style="height: 30%"></div>
        </div>
        
        <!-- Hour Labels -->
        <div class="absolute bottom-0 left-0 right-0 flex justify-around px-4 text-xs text-gray-500">
            <span>6</span><span>8</span><span>10</span><span>12</span><span>14</span><span>16</span><span>18</span><span>20</span><span>22</span><span>0</span><span>2</span><span>4</span>
        </div>
    </div>
</div>

<!-- Detailed Tables -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Top Users Table -->
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Usuarios Más Activos</h3>
            <button class="text-cyan-400 hover:text-cyan-300 text-sm">
                Ver todos
            </button>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Usuario</th>
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Accesos</th>
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Último</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">
                            <div class="flex items-center space-x-2">
                                <img src="https://via.placeholder.com/24x24/0D1B35/E0E6ED?text=JD" 
                                     alt="User" 
                                     class="w-6 h-6 rounded-full">
                                <span class="text-white text-sm">Juan Díaz</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-white text-sm">247</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-gray-400 text-sm">Hoy 14:30</span>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">
                            <div class="flex items-center space-x-2">
                                <img src="https://via.placeholder.com/24x24/0D1B35/E0E6ED?text=MG" 
                                     alt="User" 
                                     class="w-6 h-6 rounded-full">
                                <span class="text-white text-sm">María García</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-white text-sm">189</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-gray-400 text-sm">Hoy 13:15</span>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">
                            <div class="flex items-center space-x-2">
                                <img src="https://via.placeholder.com/24x24/0D1B35/E0E6ED?text=CR" 
                                     alt="User" 
                                     class="w-6 h-6 rounded-full">
                                <span class="text-white text-sm">Carlos Rodríguez</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-white text-sm">156</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-gray-400 text-sm">Hoy 11:45</span>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">
                            <div class="flex items-center space-x-2">
                                <img src="https://via.placeholder.com/24x24/0D1B35/E0E6ED?text=AL" 
                                     alt="User" 
                                     class="w-6 h-6 rounded-full">
                                <span class="text-white text-sm">Ana López</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-white text-sm">134</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-gray-400 text-sm">Ayer 18:20</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3">
                            <div class="flex items-center space-x-2">
                                <img src="https://via.placeholder.com/24x24/0D1B35/E0E6ED?text=RS" 
                                     alt="User" 
                                     class="w-6 h-6 rounded-full">
                                <span class="text-white text-sm">Roberto Silva</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-white text-sm">98</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-gray-400 text-sm">Ayer 16:10</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Alert Summary Table -->
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Resumen de Alertas</h3>
            <button class="text-cyan-400 hover:text-cyan-300 text-sm">
                Ver detalles
            </button>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Tipo</th>
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Total</th>
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Resueltas</th>
                        <th class="text-left py-2 px-3 text-cyan-400 font-medium text-sm">Pendientes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-red-400 rounded-full"></div>
                                <span class="text-white text-sm">Críticas</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-white text-sm">47</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-green-400 text-sm">35</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-red-400 text-sm">12</span>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-yellow-400 rounded-full"></div>
                                <span class="text-white text-sm">Altas</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-white text-sm">89</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-green-400 text-sm">67</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-yellow-400 text-sm">22</span>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
                                <span class="text-white text-sm">Medias</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-white text-sm">156</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-green-400 text-sm">142</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-yellow-400 text-sm">14</span>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-800">
                        <td class="py-2 px-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                <span class="text-white text-sm">Bajas</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-white text-sm">234</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-green-400 text-sm">230</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-gray-400 text-sm">4</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 font-semibold">
                            <span class="text-cyan-400 text-sm">Total</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-white text-sm font-semibold">526</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-green-400 text-sm font-semibold">474</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-yellow-400 text-sm font-semibold">52</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-gray-900 rounded-lg max-w-md w-full">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white">Exportar Reporte</h3>
                <button onclick="closeExportModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Formato</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="format" value="pdf" class="mr-2" checked>
                            <span class="text-gray-300">PDF (Recomendado para impresión)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="format" value="excel" class="mr-2">
                            <span class="text-gray-300">Excel (Para análisis de datos)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="format" value="csv" class="mr-2">
                            <span class="text-gray-300">CSV (Datos brutos)</span>
                        </label>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Incluir</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" class="mr-2" checked>
                            <span class="text-gray-300">Gráficos y visualizaciones</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="mr-2" checked>
                            <span class="text-gray-300">Tablas detalladas</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="mr-2" checked>
                            <span class="text-gray-300">Resumen ejecutivo</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center justify-end space-x-3 mt-6">
                <button onclick="closeExportModal()" class="btn-secondary">
                    Cancelar
                </button>
                <button onclick="confirmExport()" class="btn-primary">
                    <i class="fas fa-download mr-2"></i>
                    Exportar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function exportReport(format) {
    document.getElementById('exportModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Set the format
    document.querySelector(`input[name="format"][value="${format}"]`).checked = true;
}

function closeExportModal() {
    document.getElementById('exportModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function confirmExport() {
    const format = document.querySelector('input[name="format"]:checked').value;
    
    // Simulate export
    showNotification(`Generando reporte en formato ${format.toUpperCase()}...`, 'info');
    
    setTimeout(() => {
        showNotification(`Reporte ${format.toUpperCase()} generado exitosamente`, 'success');
        closeExportModal();
        
        // In a real app, this would trigger a file download
        console.log(`Exporting report as ${format}`);
    }, 2000);
}

function refreshData() {
    showNotification('Actualizando datos...', 'info');
    
    // Simulate data refresh
    setTimeout(() => {
        showNotification('Datos actualizados exitosamente', 'success');
        // In a real app, this would reload the charts and tables
    }, 1500);
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform transition-all duration-300 ${
        type === 'success' ? 'bg-green-500' : 
        type === 'warning' ? 'bg-yellow-500' : 
        type === 'danger' ? 'bg-red-500' : 'bg-blue-500'
    } text-white`;
    notification.innerHTML = `
        <div class="flex items-center space-x-3">
            <i class="fas fa-${
                type === 'success' ? 'check-circle' : 
                type === 'warning' ? 'exclamation-triangle' : 
                type === 'danger' ? 'times-circle' : 'info-circle'
            }"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Period selection handler
document.getElementById('periodSelect').addEventListener('change', function(e) {
    const customDateRange = document.getElementById('customDateRange');
    if (e.target.value === 'custom') {
        customDateRange.classList.remove('hidden');
    } else {
        customDateRange.classList.add('hidden');
    }
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeExportModal();
    }
});

// Auto-refresh data every 5 minutes
setInterval(() => {
    console.log('Auto-refreshing report data...');
    // In a real app, this would refresh the data
}, 300000);
</script>
@endsection
