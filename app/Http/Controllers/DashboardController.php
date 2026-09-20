<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $hoy = today(); // Usa America/Argentina/Buenos_Aires configurado en app.php

        $turnosTotales = Turno::whereDate('fecha_hora_inicio', $hoy)
            ->whereIn('estado', ['reservado', 'confirmado', 'completado', 'ausente'])
            ->count();
            
        $turnosCompletados = Turno::whereDate('fecha_hora_inicio', $hoy)
            ->where('estado', 'completado')
            ->count();
            
        $turnosPendientes = Turno::whereDate('fecha_hora_inicio', $hoy)
            ->whereIn('estado', ['reservado', 'confirmado'])
            ->count();

        $ingresosHoy = Turno::join('item_catalogos', 'turnos.item_catalogo_id', '=', 'item_catalogos.id')
            ->whereDate('turnos.fecha_hora_inicio', $hoy)
            ->where('turnos.estado', 'completado')
            ->sum('item_catalogos.precio');
            
        $pendienteCobro = Turno::join('item_catalogos', 'turnos.item_catalogo_id', '=', 'item_catalogos.id')
            ->whereDate('turnos.fecha_hora_inicio', $hoy)
            ->whereIn('turnos.estado', ['reservado', 'confirmado'])
            ->sum('item_catalogos.precio');

        $servicioMasSolicitado = Turno::join('item_catalogos', 'turnos.item_catalogo_id', '=', 'item_catalogos.id')
            ->whereDate('turnos.fecha_hora_inicio', $hoy)
            ->whereIn('turnos.estado', ['reservado', 'confirmado', 'completado', 'ausente'])
            ->select('item_catalogos.nombre', DB::raw('count(turnos.id) as cantidad'))
            ->groupBy('item_catalogos.nombre')
            ->orderByDesc('cantidad')
            ->first();

        return Inertia::render('Dashboard', [
            'turnosTotales' => $turnosTotales,
            'turnosCompletados' => $turnosCompletados,
            'turnosPendientes' => $turnosPendientes,
            'ingresosHoy' => (float) $ingresosHoy,
            'pendienteCobro' => (float) $pendienteCobro,
            'servicioMasSolicitado' => $servicioMasSolicitado ? $servicioMasSolicitado->nombre : null,
        ]);
    }
}
