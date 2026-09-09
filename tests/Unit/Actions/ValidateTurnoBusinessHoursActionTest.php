<?php

namespace Tests\Unit\Actions;

use App\Actions\Turnos\ValidateTurnoBusinessHoursAction;
use App\Models\Barberia;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class ValidateTurnoBusinessHoursActionTest extends TestCase
{
    private ValidateTurnoBusinessHoursAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ValidateTurnoBusinessHoursAction;
    }

    public function test_turno_dentro_de_horario_es_valido()
    {
        $barberia = new Barberia([
            'horario_apertura' => '12:00',
            'horario_cierre' => '22:00',
        ]);

        $inicio = Carbon::parse('2026-09-10 14:00:00');
        $fin = Carbon::parse('2026-09-10 14:30:00');

        $this->assertTrue($this->action->execute($barberia, $inicio, $fin));
    }

    public function test_turno_que_empieza_antes_de_apertura_es_invalido()
    {
        $barberia = new Barberia([
            'horario_apertura' => '12:00',
            'horario_cierre' => '22:00',
        ]);

        $inicio = Carbon::parse('2026-09-10 11:30:00');
        $fin = Carbon::parse('2026-09-10 12:00:00');

        $this->assertFalse($this->action->execute($barberia, $inicio, $fin));
    }

    public function test_turno_que_termina_despues_del_cierre_es_invalido()
    {
        $barberia = new Barberia([
            'horario_apertura' => '12:00',
            'horario_cierre' => '22:00',
        ]);

        $inicio = Carbon::parse('2026-09-10 21:45:00');
        $fin = Carbon::parse('2026-09-10 22:15:00');

        $this->assertFalse($this->action->execute($barberia, $inicio, $fin));
    }

    public function test_turno_con_inicio_exacto_en_apertura_es_valido()
    {
        $barberia = new Barberia([
            'horario_apertura' => '12:00',
            'horario_cierre' => '22:00',
        ]);

        $inicio = Carbon::parse('2026-09-10 12:00:00');
        $fin = Carbon::parse('2026-09-10 12:45:00');

        $this->assertTrue($this->action->execute($barberia, $inicio, $fin));
    }

    public function test_turno_con_fin_exacto_en_cierre_es_valido()
    {
        $barberia = new Barberia([
            'horario_apertura' => '12:00',
            'horario_cierre' => '22:00',
        ]);

        $inicio = Carbon::parse('2026-09-10 21:15:00');
        $fin = Carbon::parse('2026-09-10 22:00:00');

        $this->assertTrue($this->action->execute($barberia, $inicio, $fin));
    }

    public function test_turno_que_cruza_medianoche_es_invalido()
    {
        $barberia = new Barberia([
            'horario_apertura' => '12:00',
            'horario_cierre' => '23:59',
        ]);

        $inicio = Carbon::parse('2026-09-10 23:30:00');
        $fin = Carbon::parse('2026-09-11 00:00:00');

        $this->assertFalse($this->action->execute($barberia, $inicio, $fin));
    }
}
