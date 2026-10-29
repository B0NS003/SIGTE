<?php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\CajaAnotacion;
use App\Models\ConsumoServicio;
use App\Models\ElementoInventario;
use App\Models\Entrega;
use App\Models\EntregaCaja;
use App\Models\Insumo;
use App\Models\MovimientoInsumo;
use App\Models\Personal;
use App\Models\RegistroAuditoria;
use App\Models\Rol;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Único seeder del proyecto.
 * Carga roles, cuentas, nómina, inventario base y ~2 semanas de flujo (~130 cajas).
 *
 * php artisan db:seed
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->sembrarRoles();
        $this->sembrarCuentasDemo();
        $this->sembrarNomina();
        $this->sembrarInventario();
        $this->sembrarInsumosSecretaria();
        $this->limpiarFlujo();
        $this->simularDosSemanas();
    }

    private function sembrarRoles(): void
    {
        foreach ([
            [Rol::ADMINISTRADORA, 'Gestión completa del sistema, incluida administración de usuarios.'],
            [Rol::ENFERMERA, 'Supervisión de turno y operación; sin gestión de usuarios.'],
            [Rol::OPERADOR, 'Operación diaria del ciclo de esterilización.'],
            [Rol::SECRETARIA, 'Gestión de datos de consumo, producción, insumos y reportes estadísticos.'],
        ] as [$nombre, $descripcion]) {
            Rol::query()->updateOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => $descripcion]
            );
        }
    }

    private function sembrarCuentasDemo(): void
    {
        foreach ([
            ['admin@sigte.local', 'Administradora Demo', Rol::ADMINISTRADORA],
            ['enfermera@sigte.local', 'Enfermera de Turno Demo', Rol::ENFERMERA],
            ['operador@sigte.local', 'Operador Demo', Rol::OPERADOR],
            ['secretaria@sigte.local', 'Secretaria Demo', Rol::SECRETARIA],
        ] as [$email, $name, $rol]) {
            User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => 'password',
                    'rol_id' => Rol::query()->where('nombre', $rol)->value('id'),
                    'role' => $rol,
                    'activo' => true,
                ]
            );
        }

        User::query()->updateOrCreate(
            ['email' => 'judith.herrada@sigte.local'],
            [
                'name' => 'Judith Herrada Valladares',
                'password' => 'password',
                'rol_id' => Rol::query()->where('nombre', Rol::ADMINISTRADORA)->value('id'),
                'role' => Rol::ADMINISTRADORA,
                'activo' => true,
            ]
        );
    }

    private function sembrarNomina(): void
    {
        $grupos = [
            1 => [
                'CYNTHIA MABEL TRANGOLAO TRANGOLAO',
                'LISSETTE ANDREA LARA DÍAZ',
                'GIULIANA CONSTANZA BARRERA MATTA',
                'FELIPE IGNACIO GONZÁLEZ GAGLIANO',
                'FELIPE ANDRES SAGREDO LISPERGUER',
                'KATHERINE ROSALBA PONCE QUIROZ',
            ],
            2 => [
                'CAMILA FERNANDA MARIPANGUE DONOSO',
                'LADY ROMANETT REYES ANTIÑANCO',
                'BARBARA HERRADA AMPUERO',
                'JAVIERA BEATRIZ GÓMEZ SALDAÑA',
                'KATHERINE  MAGALY VALDIVIA  POBLETE',
                'MACARENA TAMARA ARAYA ARAYA',
                'GABRIELA DE LOS ANGELES CERDA CERDA',
                'GINA MARGOT FLORES SILVA',
            ],
            3 => [
                'ALEJANDRA ANDREA ARROYO MALDONADO',
                'ANA HERMINIA FUENTES ARTIGAS',
                'CLAUDIO ANDRES FRIAS VALDOVINOS',
                'FERNANDA CAROLINA QUIROZ TAPIA',
                'GABRIELA MARION RIQUELME GARCIA',
                'GERALDINE YAHAIRA RAMIREZ ARAVENA',
                'JEANETTE DEL CARMEN CONTRERAS VALDENEGRO',
                'KEITHY CAROLINA PADILLA MALDONADO',
                'LEONOR DEL PILAR HINOJOSA VERA',
                'MARIA SANTIBAÑEZ GARATE',
                'MARTA DE JESUS ALARCON ARAYA',
                'PATRICIO ALEJANDRO TAPIA SILVA',
                'SOLANGE ALEJANDRA ORMEÑO FLORES',
            ],
            4 => [
                'VANESSA BETZABÉ MANRÍQUEZ CONTRERAS',
                'HELGA ABIGAIL GUTIERREZ HANNUS',
                'CLAUDIA PATRICA RIVERA JORQUERA',
                'FLAVIA MACARENA HERMOSILLA ESPARZA',
                'MATÍAS NICOLAS PAVEZ BRAVO',
                'MATÍAS ALEXANDER DONOSO SOTO',
                'NATALIA IGNACIA GÓMEZ SALDAÑA',
                'INES ALEJANDRA JEREZ GONZALEZ',
            ],
            5 => [
                'ALEJANDRA DEL CARMEN PALACIOS VILLEGAS',
            ],
        ];

        $rolOperadora = Rol::query()->where('nombre', Rol::OPERADOR)->value('id');
        $usados = [];
        foreach ($grupos as $grupo => $nombres) {
            foreach ($nombres as $crudo) {
                $nombre = $this->nombrePropio($crudo);
                $email = $this->correo($nombre, $usados);
                $usados[] = $email;
                Personal::query()->updateOrCreate(
                    ['email' => $email],
                    ['nombre' => $nombre, 'grupo' => $grupo, 'activo' => true]
                );
                User::query()->updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $nombre,
                        'password' => 'password',
                        'rol_id' => $rolOperadora,
                        'role' => Rol::OPERADOR,
                        'activo' => true,
                    ]
                );
            }
        }
    }

    private function sembrarInventario(): void
    {
        foreach ([
            ['sala' => 'lavado', 'codigo' => 'DET-ENZ', 'nombre' => 'Detergente enzimatico', 'ubicacion' => 'Area sucia / remojo', 'stock' => 8, 'minimo' => 5, 'en_proceso' => 0],
            ['sala' => 'lavado', 'codigo' => 'EPP-KIT', 'nombre' => 'Kit EPP (pechera/gorro/guantes)', 'ubicacion' => 'Bodega area sucia', 'stock' => 22, 'minimo' => 15, 'en_proceso' => 0],
            ['sala' => 'lavado', 'codigo' => 'ESP-SEC', 'nombre' => 'Esponjas / material secado', 'ubicacion' => 'Puesto lavado', 'stock' => 4, 'minimo' => 6, 'en_proceso' => 0],
            ['sala' => 'armado', 'codigo' => 'WRAP-SMS', 'nombre' => 'Envoltorio SMS 60x60', 'ubicacion' => 'Mesas de armado', 'stock' => 35, 'minimo' => 25, 'en_proceso' => 0],
            ['sala' => 'armado', 'codigo' => 'IND-CLASE5', 'nombre' => 'Indicadores clase 5', 'ubicacion' => 'Estante armado', 'stock' => 12, 'minimo' => 20, 'en_proceso' => 0],
            ['sala' => 'armado', 'codigo' => 'CNT-EST-01', 'nombre' => 'Contenedor rigido 1/1', 'ubicacion' => 'Area preparacion', 'stock' => 6, 'minimo' => 4, 'en_proceso' => 2],
            ['sala' => 'esteril', 'codigo' => 'SET-LAP-01', 'nombre' => 'Set laparoscopia basico', 'ubicacion' => 'Almacen esteril A1', 'stock' => 4, 'minimo' => 3, 'en_proceso' => 2],
            ['sala' => 'esteril', 'codigo' => 'SET-CES-02', 'nombre' => 'Set cesarea', 'ubicacion' => 'Almacen esteril A2', 'stock' => 2, 'minimo' => 3, 'en_proceso' => 1],
            ['sala' => 'esteril', 'codigo' => 'SET-UCI-03', 'nombre' => 'Set via aerea UCI', 'ubicacion' => 'Almacen esteril A3', 'stock' => 1, 'minimo' => 2, 'en_proceso' => 1],
            ['sala' => 'esteril', 'codigo' => 'CAJ-CUR-10', 'nombre' => 'Caja curacion general', 'ubicacion' => 'Almacen esteril B1', 'stock' => 8, 'minimo' => 5, 'en_proceso' => 3],
        ] as $datos) {
            ElementoInventario::query()->updateOrCreate(
                ['sala' => $datos['sala'], 'codigo' => $datos['codigo']],
                $datos
            );
        }
    }

    private function sembrarInsumosSecretaria(): void
    {
        $secretariaId = User::query()->where('email', 'secretaria@sigte.local')->value('id');

        foreach ([
            ['codigo' => 'DET-ENZ', 'nombre' => 'Detergente enzimatico', 'ubicacion' => 'Bodega area sucia', 'stock' => 8, 'minimo' => 5],
            ['codigo' => 'IND-CLASE5', 'nombre' => 'Indicadores clase 5', 'ubicacion' => 'Estante armado', 'stock' => 12, 'minimo' => 20],
            ['codigo' => 'WRAP-SMS', 'nombre' => 'Envoltorio SMS 60x60', 'ubicacion' => 'Mesas de armado', 'stock' => 35, 'minimo' => 25],
            ['codigo' => 'ESP-SEC', 'nombre' => 'Esponjas / material secado', 'ubicacion' => 'Puesto lavado', 'stock' => 4, 'minimo' => 6],
        ] as $datos) {
            Insumo::query()->updateOrCreate(['codigo' => $datos['codigo']], $datos);
        }

        $detergente = Insumo::query()->where('codigo', 'DET-ENZ')->first();
        if ($detergente !== null) {
            MovimientoInsumo::query()->firstOrCreate(
                ['insumo_id' => $detergente->id, 'observacion' => 'Carga inicial de bodega'],
                ['tipo' => MovimientoInsumo::ENTRADA, 'cantidad' => 10, 'user_id' => $secretariaId]
            );
        }

        foreach ([
            ['servicio' => 'Pabellon', 'periodo' => '2026-09-01', 'consumo' => 42, 'litros' => 520.00, 'observacion' => 'Cierre septiembre'],
            ['servicio' => 'Dental', 'periodo' => '2026-09-01', 'consumo' => 18, 'litros' => 95.50, 'observacion' => null],
            ['servicio' => 'Maternidad', 'periodo' => '2026-09-01', 'consumo' => 27, 'litros' => 310.00, 'observacion' => null],
            ['servicio' => 'Urgencia', 'periodo' => '2026-09-01', 'consumo' => 15, 'litros' => 140.00, 'observacion' => null],
            ['servicio' => 'Pabellon', 'periodo' => '2026-10-01', 'consumo' => 6, 'litros' => 72.00, 'observacion' => 'Avance de octubre'],
            ['servicio' => 'Dental', 'periodo' => '2026-10-01', 'consumo' => 3, 'litros' => 18.00, 'observacion' => null],
        ] as $demo) {
            ConsumoServicio::query()->updateOrCreate(
                ['servicio' => $demo['servicio'], 'periodo' => $demo['periodo']],
                [
                    'consumo' => $demo['consumo'],
                    'litros' => $demo['litros'],
                    'observacion' => $demo['observacion'],
                    'user_id' => $secretariaId,
                ]
            );
        }
    }

    private function limpiarFlujo(): void
    {
        DB::table('entrega_cajas')->delete();
        DB::table('entregas')->delete();
        DB::table('caja_anotaciones')->delete();
        DB::table('caja_retrocesos')->delete();
        DB::table('registros_auditoria')->delete();
        DB::table('reposiciones')->delete();
        DB::table('cajas')->delete();
    }

    private function simularDosSemanas(): void
    {
        $equipo = Personal::query()->where('activo', true)->orderBy('id')->get()->values();
        $users = User::query()->whereIn('email', $equipo->pluck('email'))->get()->keyBy('email');
        $judith = User::query()->where('email', 'judith.herrada@sigte.local')->first()
            ?? User::query()->where('email', 'admin@sigte.local')->first();
        $enfermera = User::query()->where('email', 'enfermera@sigte.local')->first();
        $inicio = now()->timezone('America/Santiago')->subDays(13)->startOfDay();
        $procesosActivos = 0;
        $pendientesEntrega = [];
        $receptores = [
            'Pabellon' => ['Daniela Soto', 'Enfermera de pabellón'],
            'Dental' => ['Carla Núñez', 'Enfermera de dental'],
            'UCI' => ['Alejandra Riquelme', 'Enfermera de turno'],
            'Urgencia' => ['Patricia Vega', 'Enfermera de urgencia'],
            'Maternidad' => ['Natalia Sánchez', 'Jefatura de maternidad'],
            'Curaciones' => ['Daniela Soto', 'Enfermera de curaciones'],
        ];

        foreach ($this->plantillas() as $i => $plantilla) {
            $persona = $equipo[$i % max(1, $equipo->count())] ?? null;
            $user = $persona !== null ? $users->get($persona->email) : null;
            $diaIngreso = $inicio->copy()->addDays($plantilla['dia']);
            $minutoIngreso = 8 + (($i % 8) * 7);

            $caja = Caja::query()->create([
                'codigo' => $plantilla['codigo'],
                'servicio' => $plantilla['servicio'],
                'etapa' => Caja::ETAPA_RECEPCION,
                'ubicacion' => 'Area sucia',
                'estado' => 'Ingreso desde '.$plantilla['servicio'],
                'actividad' => null,
                'urgente' => $plantilla['urgente'] ?? false,
                'responsable_id' => $user?->id,
                'personal_id' => $persona?->id,
                'etapa_desde' => $diaIngreso->copy()->setTime(7, $minutoIngreso),
                'proceso_desde' => null,
                'proceso_hasta' => null,
                'entregado_at' => null,
                'created_at' => $diaIngreso->copy()->setTime(7, $minutoIngreso),
                'updated_at' => $diaIngreso->copy()->setTime(7, $minutoIngreso),
            ]);

            $this->registrarEn($diaIngreso->copy()->setTime(7, $minutoIngreso), $user, 'Registró recepción', 'etapa', $caja->codigo, 'Desde '.$plantilla['servicio']);
            $this->anotar($caja, $user, $persona, 'Ingreso en recepción', $diaIngreso->copy()->setTime(7, min(59, $minutoIngreso + 2)));

            $etapaActual = Caja::ETAPA_RECEPCION;
            $reloj = $diaIngreso->copy()->setTime(8, ($i % 50));

            foreach ($plantilla['pasos'] as $paso) {
                $reloj = $reloj->copy()->addHours($paso['horas'] ?? 2);
                if ($reloj->greaterThan(now())) {
                    break;
                }

                if (($paso['tipo'] ?? 'avance') === 'anotacion') {
                    $this->anotar($caja, $user, $persona, $paso['texto'], $reloj);
                    $caja->actividad = mb_substr($paso['texto'], 0, 80);
                    $caja->ubicacion = $paso['ubicacion'] ?? $caja->ubicacion;
                    $caja->estado = $paso['estado'] ?? $caja->estado;
                    $caja->save();
                    $this->registrarEn($reloj, $user, 'Anotó en etapa', 'etapa', $caja->codigo, Caja::etiquetaEtapa($etapaActual).' · '.$paso['texto']);
                    continue;
                }

                $siguiente = $paso['etapa'];
                $desde = Caja::etiquetaEtapa($etapaActual);
                $hacia = Caja::etiquetaEtapa($siguiente);
                $caja->etapa = $siguiente;
                $caja->estado = 'En '.$hacia;
                $caja->ubicacion = $paso['ubicacion'] ?? $caja->ubicacion;
                $caja->actividad = null;
                $caja->etapa_desde = $reloj;
                $caja->responsable_id = $user?->id;
                $caja->personal_id = $persona?->id;
                $caja->save();
                $this->anotar($caja, $user, $persona, 'Pasó a '.$hacia, $reloj);
                $this->registrarEn($reloj, $user, 'Avanzó etapa', 'etapa', $caja->codigo, $desde.' → '.$hacia);
                $etapaActual = $siguiente;
            }

            if (! empty($plantilla['entregar']) && $etapaActual === Caja::ETAPA_ENTREGA) {
                $momentoEntrega = $reloj->copy()->addHours(1);
                if ($momentoEntrega->lessThanOrEqualTo(now())) {
                    $caja->entregado_at = $momentoEntrega;
                    $caja->estado = 'Entregada a '.$plantilla['servicio'];
                    $caja->ubicacion = 'Ventanilla de entrega';
                    $caja->save();
                    $pendientesEntrega[] = [
                        'caja' => $caja,
                        'user' => $user,
                        'cuando' => $momentoEntrega,
                        'servicio' => $plantilla['servicio'],
                        'material' => ($i % 5 === 0) ? 'Paquete de ropa' : (($i % 7 === 0) ? 'Material de curación' : null),
                    ];
                }
            } elseif (($plantilla['proceso'] ?? false) && $procesosActivos < 6) {
                if ($etapaActual === Caja::ETAPA_LAVADO) {
                    $caja->fill([
                        'proceso_desde' => now()->subMinutes(25 + $procesosActivos),
                        'proceso_hasta' => now()->addMinutes(35),
                        'actividad' => 'Lavadora',
                        'ubicacion' => 'Lavadora '.(($procesosActivos % 2) + 1),
                        'estado' => 'En lavadora',
                    ])->save();
                    $procesosActivos++;
                } elseif ($etapaActual === Caja::ETAPA_ESTERILIZACION) {
                    $caja->fill([
                        'proceso_desde' => now()->subMinutes(8 + $procesosActivos),
                        'proceso_hasta' => now()->addMinutes(12),
                        'actividad' => 'Espera para abrir',
                        'ubicacion' => 'Autoclave '.(($procesosActivos % 2) + 1),
                        'estado' => 'Espera para abrir la puerta',
                    ])->save();
                    $procesosActivos++;
                } elseif ($etapaActual === Caja::ETAPA_ALMACEN) {
                    $caja->fill([
                        'proceso_desde' => now()->subMinutes(15),
                        'proceso_hasta' => now()->addMinutes(20),
                        'actividad' => 'Material chico',
                        'ubicacion' => 'Almacen esteril A'.(($procesosActivos % 3) + 1),
                        'estado' => 'Enfriando material chico',
                    ])->save();
                    $procesosActivos++;
                }
            }
        }

        $this->registrarEntregasSembradas($pendientesEntrega, $receptores);

        if ($judith !== null) {
            $this->registrarEn(now()->subDays(2)->setTime(11, 20), $judith, 'Asignó turno', 'turnos', 'Alejandra Andrea Arroyo Maldonado', 'Turno Mañana');
            $this->registrarEn(now()->subDays(5)->setTime(15, 10), $judith, 'Actualizó cuenta', 'usuarios', 'Operador Demo', 'Datos de la cuenta del puesto');
        }
        if ($enfermera !== null) {
            $this->registrarEn(now()->subDays(1)->setTime(10, 5), $enfermera, 'Registró reposición', 'inventario', 'SET-CES-02', '+3 unidades · stock actualizado');
        }
    }

    /**
     * Agrupa cajas entregadas del mismo día/servicio/operadora en una salida.
     *
     * @param  list<array{caja: Caja, user: ?User, cuando: Carbon, servicio: string, material: ?string}>  $pendientes
     * @param  array<string, list{string, string}>  $receptores
     */
    private function registrarEntregasSembradas(array $pendientes, array $receptores): void
    {
        $grupos = [];
        foreach ($pendientes as $item) {
            $clave = implode('|', [
                $item['cuando']->timezone('America/Santiago')->toDateString(),
                $item['servicio'],
                (string) ($item['user']?->id ?? 0),
                $item['cuando']->format('H'),
            ]);
            $grupos[$clave][] = $item;
        }

        foreach ($grupos as $lote) {
            $primero = $lote[0];
            $servicio = $primero['servicio'];
            $receptor = $receptores[$servicio] ?? ['Enf. de turno', 'Personal del servicio'];
            $material = collect($lote)->pluck('material')->filter()->first();
            $cuando = $primero['cuando'];
            $entrega = Entrega::query()->create([
                'codigo' => Entrega::siguienteCodigo($cuando->copy()->timezone('America/Santiago')),
                'servicio' => $servicio,
                'recibe' => $receptor[0],
                'recibe_rol' => $receptor[1],
                'material' => $material,
                'observacion' => null,
                'user_id' => $primero['user']?->id,
                'entregado_at' => $cuando,
                'created_at' => $cuando,
                'updated_at' => $cuando,
            ]);

            foreach ($lote as $item) {
                /** @var Caja $caja */
                $caja = $item['caja'];
                EntregaCaja::query()->create([
                    'entrega_id' => $entrega->id,
                    'caja_id' => $caja->id,
                    'codigo' => $caja->codigo,
                    'nombre' => str_starts_with(mb_strtoupper($caja->codigo), 'SET-')
                        ? 'Set · '.$caja->servicio
                        : 'Caja · '.$caja->servicio,
                    'created_at' => $cuando,
                    'updated_at' => $cuando,
                ]);
                $this->registrarEn(
                    $cuando,
                    $item['user'],
                    'Registró entrega',
                    'entrega',
                    $caja->codigo,
                    'Retira '.$receptor[0].' · '.$servicio.' · '.$entrega->codigo
                );
            }
        }
    }

    /**
     * ~10–12 cajas/día hábil y 4–5 el fin de semana (~130 en 14 días).
     *
     * @return list<array<string, mixed>>
     */
    private function plantillas(): array
    {
        $etapas = Caja::etapas();
        $servicios = ['Pabellon', 'UCI', 'Maternidad', 'Urgencia', 'Curaciones', 'Dental'];
        $plantillas = [];
        $nSet = 1;
        $nCaja = 1;

        for ($dia = 0; $dia <= 13; $dia++) {
            $fecha = now()->timezone('America/Santiago')->subDays(13 - $dia)->startOfDay();
            $esFinDeSemana = $fecha->isWeekend();
            $cantidad = $esFinDeSemana ? 4 + ($dia % 2) : 10 + ($dia % 3);

            for ($j = 0; $j < $cantidad; $j++) {
                $esSet = ($j % 3) !== 2;
                $codigo = $esSet
                    ? 'SET-'.str_pad((string) $nSet++, 3, '0', STR_PAD_LEFT)
                    : 'CAJA-'.str_pad((string) $nCaja++, 3, '0', STR_PAD_LEFT);
                $servicio = $servicios[($dia + $j) % count($servicios)];
                $urgente = $esFinDeSemana || ($j === 0 && in_array($servicio, ['Pabellon', 'Urgencia', 'UCI'], true));

                // Días antiguos: ciclo completo entregado. Recientes: aún en proceso.
                if ($dia <= 7) {
                    $destino = Caja::ETAPA_ENTREGA;
                    $entregar = true;
                    $proceso = false;
                } elseif ($dia <= 9) {
                    $destino = [Caja::ETAPA_ALMACEN, Caja::ETAPA_ENTREGA, Caja::ETAPA_ESTERILIZACION][$j % 3];
                    $entregar = $destino === Caja::ETAPA_ENTREGA;
                    $proceso = $destino === Caja::ETAPA_ALMACEN && $j % 2 === 0;
                } elseif ($dia <= 11) {
                    $destino = [Caja::ETAPA_PREPARACION, Caja::ETAPA_ESTERILIZACION, Caja::ETAPA_LAVADO, Caja::ETAPA_ALMACEN][$j % 4];
                    $entregar = false;
                    $proceso = in_array($destino, [Caja::ETAPA_LAVADO, Caja::ETAPA_ESTERILIZACION, Caja::ETAPA_ALMACEN], true) && $j % 3 === 0;
                } else {
                    $destino = [Caja::ETAPA_RECEPCION, Caja::ETAPA_LAVADO, Caja::ETAPA_RECEPCION, Caja::ETAPA_PREPARACION][$j % 4];
                    $entregar = false;
                    $proceso = $destino === Caja::ETAPA_LAVADO && $j % 2 === 0;
                }

                $autoclave = 'Autoclave '.(($j % 2) + 1);
                $almacen = 'Almacen esteril '.(['A1', 'A2', 'B1'][$j % 3]);
                $meta = [
                    Caja::ETAPA_LAVADO => ['ubicacion' => $j % 4 === 0 ? 'Lavadora '.(($j % 2) + 1) : 'Sala lavado', 'horas' => 1 + ($j % 3)],
                    Caja::ETAPA_PREPARACION => ['ubicacion' => 'Sala armado', 'horas' => 1 + ($j % 2)],
                    Caja::ETAPA_ESTERILIZACION => ['ubicacion' => $autoclave, 'horas' => 2 + ($j % 3)],
                    Caja::ETAPA_ALMACEN => ['ubicacion' => $almacen, 'horas' => 2 + ($j % 2)],
                    Caja::ETAPA_ENTREGA => ['ubicacion' => 'Ventanilla de entrega', 'horas' => 1 + ($j % 3)],
                ];

                $pasos = $destino === Caja::ETAPA_RECEPCION
                    ? []
                    : $this->rutaHasta($destino, $etapas, $meta);

                if ($destino === Caja::ETAPA_LAVADO && $j % 3 === 1) {
                    $pasos[] = ['tipo' => 'anotacion', 'texto' => 'En secado', 'ubicacion' => 'Secado', 'estado' => 'En secado', 'horas' => 1];
                } elseif ($destino === Caja::ETAPA_PREPARACION && $j % 3 === 0) {
                    $pasos[] = ['tipo' => 'anotacion', 'texto' => 'Armado y rotulado', 'ubicacion' => 'Sala armado', 'estado' => 'Armado y rotulado', 'horas' => 1];
                } elseif ($destino === Caja::ETAPA_ESTERILIZACION && $j % 4 === 0) {
                    $pasos[] = ['tipo' => 'anotacion', 'texto' => 'Espera para abrir: 20 min', 'ubicacion' => $autoclave, 'estado' => 'Espera para abrir la puerta', 'horas' => 1];
                }

                $plantillas[] = [
                    'codigo' => $codigo,
                    'servicio' => $servicio,
                    'dia' => $dia,
                    'urgente' => $urgente,
                    'entregar' => $entregar,
                    'proceso' => $proceso,
                    'pasos' => $pasos,
                ];
            }
        }

        return $plantillas;
    }

    /**
     * @param  list<string>  $etapas
     * @param  array<string, array{ubicacion?: string, horas?: int}>  $meta
     * @return list<array{etapa: string, ubicacion?: string, horas?: int}>
     */
    private function rutaHasta(string $destino, array $etapas, array $meta): array
    {
        $pasos = [];
        foreach ($etapas as $etapa) {
            if ($etapa === Caja::ETAPA_RECEPCION) {
                continue;
            }
            $pasos[] = array_merge(['etapa' => $etapa], $meta[$etapa] ?? ['horas' => 2]);
            if ($etapa === $destino) {
                break;
            }
        }

        return $pasos;
    }

    private function anotar(Caja $caja, ?User $user, ?Personal $persona, string $texto, Carbon $cuando): void
    {
        $nota = new CajaAnotacion([
            'caja_id' => $caja->id,
            'user_id' => $user?->id,
            'personal_id' => $persona?->id,
            'texto' => $texto,
        ]);
        $nota->created_at = $cuando;
        $nota->updated_at = $cuando;
        $nota->save();
    }

    private function registrarEn(Carbon $cuando, ?User $actor, string $accion, string $tipo, string $objeto, string $detalle): void
    {
        $registro = new RegistroAuditoria([
            'user_id' => $actor?->id,
            'accion' => $accion,
            'tipo' => $tipo,
            'objeto' => $objeto,
            'detalle' => $detalle,
        ]);
        $registro->created_at = $cuando;
        $registro->updated_at = $cuando;
        $registro->save();
    }

    private function nombrePropio(string $nombre): string
    {
        $particulas = ['de', 'del', 'la', 'las', 'los', 'y'];
        $palabras = preg_split('/\s+/u', trim($nombre)) ?: [];
        $salida = [];
        foreach ($palabras as $i => $palabra) {
            $minuscula = mb_strtolower($palabra, 'UTF-8');
            if ($i > 0 && in_array($minuscula, $particulas, true)) {
                $salida[] = $minuscula;
                continue;
            }
            $salida[] = mb_convert_case($minuscula, MB_CASE_TITLE, 'UTF-8');
        }

        return implode(' ', $salida);
    }

    /** @param list<string> $usados */
    private function correo(string $nombre, array $usados): string
    {
        $particulas = ['de', 'del', 'la', 'las', 'los', 'y'];
        $palabras = preg_split('/\s+/u', $nombre) ?: [];
        $primero = $this->slug($palabras[0] ?? 'persona');
        $apellidos = [];
        foreach (array_slice($palabras, 1) as $palabra) {
            if (! in_array(mb_strtolower($palabra, 'UTF-8'), $particulas, true)) {
                $apellidos[] = $this->slug($palabra);
            }
        }
        $base = $primero.'.'.($apellidos[0] ?? 'sigte');
        $correo = $base.'@sigte.local';
        $extra = 1;
        while (in_array($correo, $usados, true) || Personal::query()->where('email', $correo)->where('nombre', '!=', $nombre)->exists()) {
            $sufijo = $apellidos[$extra] ?? (string) ($extra + 1);
            $correo = $base.'.'.$sufijo.'@sigte.local';
            $extra++;
        }

        return $correo;
    }

    private function slug(string $texto): string
    {
        $texto = mb_strtolower($texto, 'UTF-8');
        $texto = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ]);

        return preg_replace('/[^a-z]/', '', $texto) ?: 'persona';
    }
}
