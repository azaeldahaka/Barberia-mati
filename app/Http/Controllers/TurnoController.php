<?php

namespace App\Http\Controllers;

use App\Actions\CreateClientAction;
use App\Actions\Turnos\CalculateTurnoEndTimeAction;
use App\Actions\Turnos\CheckTurnoOverlapAction;
use App\Actions\Turnos\ValidateTurnoBusinessHoursAction;
use App\Http\Requests\StoreStaffTurnoRequest;
use App\Models\Client;
use App\Models\ItemCatalogo;
use App\Models\Turno;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TurnoController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $query = Turno::with(['cliente', 'itemCatalogo', 'usuario'])
            ->where('barberia_id', $user->barberia_id);

        $view = $request->input('view', 'day');

        $start = $request->input('start');
        $end = $request->input('end');

        if (! $start || ! $end) {
            if ($view === 'week') {
                $start = Carbon::today()->startOfWeek()->format('Y-m-d');
                $end = Carbon::today()->endOfWeek()->format('Y-m-d');
            } else {
                $start = Carbon::today()->format('Y-m-d');
                $end = Carbon::today()->format('Y-m-d');
            }
        }

        $query->where('fecha_hora_inicio', '>=', Carbon::parse($start)->startOfDay());
        $query->where('fecha_hora_inicio', '<=', Carbon::parse($end)->endOfDay());

        $turnos = $query->orderBy('fecha_hora_inicio', 'asc')->get();

        return Inertia::render('Turnos/Index', [
            'turnos' => $turnos,
            'filters' => [
                'start' => $start,
                'end' => $end,
                'view' => $view,
            ],
        ]);
    }

    public function create(): Response
    {
        $user = Auth::user();

        return Inertia::render('Turnos/Create', [
            'clients' => Client::orderBy('first_name')->get(),
            'itemCatalogos' => ItemCatalogo::where('barberia_id', $user->barberia_id)->get(),
        ]);
    }

    public function store(
        StoreStaffTurnoRequest $request,
        CreateClientAction $createClientAction,
        CalculateTurnoEndTimeAction $calculateEndTimeAction,
        ValidateTurnoBusinessHoursAction $validateBusinessHoursAction,
        CheckTurnoOverlapAction $checkOverlapAction
    ): RedirectResponse {
        $validated = $request->validated();
        $user = Auth::user();
        $barberiaId = $user->barberia_id;

        // 1. Obtener o crear cliente
        if (! empty($validated['client_id'])) {
            $clientId = $validated['client_id'];
        } else {
            $client = $createClientAction->execute([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
            ]);
            $clientId = $client->id;
        }

        $itemCatalogo = ItemCatalogo::findOrFail($validated['item_catalogo_id']);
        $fechaHoraInicio = Carbon::parse($validated['fecha_hora_inicio']);

        // 2. Calcular fecha de fin
        $fechaHoraFin = $calculateEndTimeAction->execute($itemCatalogo, $fechaHoraInicio);

        // 3. Validar horario comercial
        $isValidHours = $validateBusinessHoursAction->execute($user->barberia, $fechaHoraInicio, $fechaHoraFin);
        if (! $isValidHours) {
            return back()->withErrors(['fecha_hora_inicio' => 'El turno debe estar dentro del horario de atención de la barbería.'])->withInput();
        }

        // 4. Verificar superposición
        $hasOverlap = $checkOverlapAction->execute($barberiaId, $fechaHoraInicio, $fechaHoraFin);
        if ($hasOverlap) {
            return back()->withErrors(['fecha_hora_inicio' => 'El horario seleccionado se superpone con otro turno existente.'])->withInput();
        }

        // 5. Guardar turno
        Turno::create([
            'barberia_id' => $barberiaId,
            'cliente_id' => $clientId,
            'usuario_id' => $user->id,
            'item_catalogo_id' => $itemCatalogo->id,
            'fecha_hora_inicio' => $fechaHoraInicio,
            'fecha_hora_fin' => $fechaHoraFin,
            'estado' => 'reservado',
        ]);

        return redirect()->route('turnos.index')->with('status', 'Turno agendado exitosamente.');
    }
}
