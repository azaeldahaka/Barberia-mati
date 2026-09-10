<?php

namespace App\Http\Controllers;

use App\Actions\Turnos\CalculateTurnoEndTimeAction;
use App\Actions\Turnos\CheckTurnoOverlapAction;
use App\Actions\Turnos\ValidateTurnoBusinessHoursAction;
use App\Http\Requests\StorePublicTurnoRequest;
use App\Models\Barberia;
use App\Models\ItemCatalogo;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicTurnoController extends Controller
{
    public function create(Request $request): Response
    {
        // En esta v1 asumimos que hay una sola barbería
        $barberia = Barberia::firstOrFail();
        $date = $request->query('date', now()->toDateString());

        // Para evitar enviar todos los turnos, filtramos por la fecha seleccionada
        $turnosDelDia = Turno::where('barberia_id', $barberia->id)
            ->whereDate('fecha_hora_inicio', $date)
            ->whereIn('estado', ['reservado', 'completado']) // Solo los activos ocupan lugar
            ->get(['fecha_hora_inicio', 'fecha_hora_fin']);

        $itemCatalogos = ItemCatalogo::where('barberia_id', $barberia->id)->get();

        return Inertia::render('Turnos/PublicCreate', [
            'itemCatalogos' => $itemCatalogos,
            'turnosDelDia' => $turnosDelDia,
            'selectedDate' => $date,
            'has_client' => session()->has('client_id'),
            'pending_turno' => session('pending_turno', null),
            'horario_apertura' => $barberia->horario_apertura,
            'horario_cierre' => $barberia->horario_cierre,
        ]);
    }

    public function store(
        StorePublicTurnoRequest $request,
        CalculateTurnoEndTimeAction $calculateEndTimeAction,
        ValidateTurnoBusinessHoursAction $validateBusinessHoursAction,
        CheckTurnoOverlapAction $checkOverlapAction
    ) {
        $validated = $request->validated();
        $barberia = Barberia::firstOrFail();

        // Si no está registrado en la sesión, lo mandamos a registrarse
        if (! session()->has('client_id')) {
            session(['pending_turno' => $validated]);

            return redirect()->route('client.register')
                ->with('status', 'Para confirmar tu turno, por favor ingresá tus datos primero.');
        }

        $clientId = session('client_id');

        // Para v1: Asignamos el turno al usuario por defecto (Matías) de esta barbería
        // TODO: Refactorizar cuando se sumen múltiples barberos para que el cliente pueda elegir.
        $user = User::where('barberia_id', $barberia->id)->firstOrFail();

        $itemCatalogo = ItemCatalogo::findOrFail($validated['item_catalogo_id']);
        $fechaHoraInicio = Carbon::parse($validated['fecha_hora_inicio']);

        // Calcular fecha de fin
        $fechaHoraFin = $calculateEndTimeAction->execute($itemCatalogo, $fechaHoraInicio);

        // Validar horario comercial
        $isValidHours = $validateBusinessHoursAction->execute($barberia, $fechaHoraInicio, $fechaHoraFin);
        if (! $isValidHours) {
            return back()->withErrors(['fecha_hora_inicio' => 'El turno debe estar dentro del horario de atención de la barbería.'])->withInput();
        }

        // Verificar superposición
        $hasOverlap = $checkOverlapAction->execute($barberia->id, $fechaHoraInicio, $fechaHoraFin);
        if ($hasOverlap) {
            return back()->withErrors(['fecha_hora_inicio' => 'El horario seleccionado ya no está disponible.'])->withInput();
        }

        // Guardar turno
        Turno::create([
            'barberia_id' => $barberia->id,
            'cliente_id' => $clientId,
            'usuario_id' => $user->id,
            'item_catalogo_id' => $itemCatalogo->id,
            'fecha_hora_inicio' => $fechaHoraInicio,
            'fecha_hora_fin' => $fechaHoraFin,
            'estado' => 'reservado',
        ]);

        // Limpiar turno pendiente si lo hubiera
        session()->forget('pending_turno');

        return redirect()->route('public.turno.create')->with('status', 'Tu reserva ha sido confirmada exitosamente. ¡Te esperamos!');
    }
}
