<?php

namespace App\Http\Controllers;

use App\Models\CajaAnotacion;
use App\Models\Caja;
use App\Models\ConsumoServicio;
use App\Models\Insumo;
use App\Models\Personal;
use App\Models\Rol;
use App\Models\User;
use App\Http\Controllers\Concerns\ConsultaDeCajas;
use App\Http\Controllers\Concerns\ResuelveUsuario;
use App\Support\AccesoPorRol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FlujoController extends Controller
{
    use ConsultaDeCajas;
    use ResuelveUsuario;

    /**
     * Lo que la jefatura puede ver con la etapa actual. No hay historial de pasos.
     *
     * @param  list<array{id: string, servicio: string, fase_idx: int, tiempo: string, minutos: int|null}>  $cajas
     * @return array{total: int, en_almacen: int, en_entrega: int, conteo: list<int>, detenidas: list<array{id: string, servicio: string, etapa: string, lleva: string, limite: int}>}
     */
    private function panorama(array $cajas): array
    {
        $fases = $this->fases();
        $etapas = Caja::etapas();
        $guia = [
            Caja::ETAPA_RECEPCION => 60,
            Caja::ETAPA_LAVADO => 60,
            Caja::ETAPA_PREPARACION => 90,
            Caja::ETAPA_ESTERILIZACION => 45,
            Caja::ETAPA_ALMACEN => 240,
            Caja::ETAPA_ENTREGA => 60,
        ];
        $porEtapa = [];
        $detenidas = [];
        foreach ($etapas as $indice => $clave) {
            $porEtapa[$indice] = [
                'indice' => $indice,
                'nombre' => $fases[$indice],
                'clave' => $clave,
                'cantidad' => 0,
                'tarde' => 0,
                'cajas' => [],
            ];
        }
        foreach ($cajas as $caja) {
            $indice = $caja['fase_idx'];
            $etapa = $etapas[$indice] ?? Caja::ETAPA_RECEPCION;
            $limite = $guia[$etapa] ?? 60;
            $tarde = $caja['minutos'] !== null && $caja['minutos'] > $limite;
            $porEtapa[$indice]['cantidad']++;
            if ($tarde) {
                $porEtapa[$indice]['tarde']++;
                $detenidas[] = [
                    'id' => $caja['id'],
                    'servicio' => $caja['servicio'],
                    'etapa' => $fases[$indice],
                    'clave' => $etapa,
                    'espera' => $this->etiquetaEspera($caja['minutos']),
                    'minutos' => $caja['minutos'],
                ];
            }
            $porEtapa[$indice]['cajas'][] = [
                'id' => $caja['id'],
                'servicio' => $caja['servicio'],
                'espera' => $caja['minutos'] === null ? 'Sin registro' : $this->etiquetaEspera($caja['minutos']),
                'tarde' => $tarde,
                'minutos' => $caja['minutos'] ?? -1,
            ];
        }
        foreach ($porEtapa as &$grupo) {
            usort($grupo['cajas'], fn (array $a, array $b) => $b['minutos'] <=> $a['minutos']);
        }
        unset($grupo);
        usort($detenidas, fn (array $a, array $b) => $b['minutos'] <=> $a['minutos']);
        return [
            'total' => count($cajas),
            'en_almacen' => $porEtapa[array_search(Caja::ETAPA_ALMACEN, $etapas, true)]['cantidad'] ?? 0,
            'en_entrega' => $porEtapa[array_search(Caja::ETAPA_ENTREGA, $etapas, true)]['cantidad'] ?? 0,
            'conteo' => array_column($porEtapa, 'cantidad'),
            'detenidas' => $detenidas,
            'etapas' => array_values($porEtapa),
        ];
    }
    private function etiquetaEspera(int $minutos): string
    {
        if ($minutos < 60) {
            return $minutos.' min';
        }
        if ($minutos < 60 * 24) {
            $horas = intdiv($minutos, 60);
            return $horas === 1 ? '1 hora' : $horas.' horas';
        }
        $dias = intdiv($minutos, 60 * 24);
        return $dias === 1 ? '1 día' : $dias.' días';
    }
    /**
     * Consulta de cajas para HIU-EP2-001: búsqueda, filtro por etapa y agrupación.
     *
     * @return array{
     *   busqueda: string,
     *   etapa_filtro: int|null,
     *   cajas: list<array>,
     *   cajas_por_etapa: list<array{indice: int, nombre: string, cajas: list<array>}>,
     *   conteo_etapas: list<int>,
     *   total: int,
     *   hay_filtros: bool
     * }
     */
    private function consultaCajas(Request $request): array
    {
        $fases = $this->fases();
        $todas = $this->cajas();
        $busqueda = trim((string) $request->query('q', ''));
        $etapaRaw = $request->query('etapa');
        $etapaFiltro = is_numeric($etapaRaw) ? (int) $etapaRaw : null;
        if ($etapaFiltro !== null && ($etapaFiltro < 0 || $etapaFiltro >= count($fases))) {
            $etapaFiltro = null;
        }
        $filtradas = $todas;
        if ($busqueda !== '') {
            $needle = mb_strtolower($busqueda);
            $filtradas = array_values(array_filter($filtradas, function (array $caja) use ($needle) {
                $haystack = mb_strtolower(implode(' ', [
                    $caja['id'],
                    $caja['servicio'],
                    $caja['ubicacion'],
                    $caja['estado'],
                    $caja['operadora'],
                ]));
                return str_contains($haystack, $needle);
            }));
        }
        $conteoEtapas = array_fill(0, count($fases), 0);
        foreach ($filtradas as $caja) {
            $conteoEtapas[$caja['fase_idx']]++;
        }
        if ($etapaFiltro !== null) {
            $filtradas = array_values(array_filter(
                $filtradas,
                fn (array $caja) => $caja['fase_idx'] === $etapaFiltro
            ));
        }
        $cajasPorEtapa = [];
        foreach ($fases as $indice => $nombre) {
            $delGrupo = array_values(array_filter(
                $filtradas,
                fn (array $caja) => $caja['fase_idx'] === $indice
            ));
            if ($delGrupo === []) {
                continue;
            }
            $cajasPorEtapa[] = [
                'indice' => $indice,
                'nombre' => $nombre,
                'cajas' => $delGrupo,
            ];
        }
        return [
            'busqueda' => $busqueda,
            'etapa_filtro' => $etapaFiltro,
            'cajas' => $filtradas,
            'cajas_por_etapa' => $cajasPorEtapa,
            'conteo_etapas' => $conteoEtapas,
            'total' => count($filtradas),
            'hay_filtros' => $busqueda !== '' || $etapaFiltro !== null,
        ];
    }
    public function panel(Request $request, string $rol): View|RedirectResponse
    {
        $user = Auth::user();
        $rolReal = $user->nombreRol();
        if ($rolReal === null || ! in_array($rolReal, [Rol::ADMINISTRADORA, Rol::ENFERMERA, Rol::OPERADOR, Rol::SECRETARIA], true)) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Tu cuenta no tiene un rol válido.',
            ]);
        }
        if (! AccesoPorRol::puedeVerPanel($user, $rol)) {
            return redirect()->route('mockups.panel', $rolReal);
        }
        $usuario = $this->usuarioSesion();
        if ($rol === Rol::SECRETARIA) {
            $inicio = now()->startOfMonth()->toDateString();
            $delMes = ConsumoServicio::query()->whereDate('periodo', $inicio)->get();
            $insumos = Insumo::query()->get();
            return view('secretaria.panel', [
                'usuario' => $usuario,
                'resumen' => [
                    'periodo' => ConsumoServicio::etiquetaPeriodo($inicio),
                    'servicios' => $delMes->count(),
                    'consumo' => (int) $delMes->sum('consumo'),
                    'litros' => (float) $delMes->sum('litros'),
                    'bajo_minimo' => $insumos->filter(fn (Insumo $insumo) => $insumo->estadoStock() !== 'ok')->count(),
                ],
            ]);
        }
        $roles = [
            Rol::ADMINISTRADORA => [
                'rol' => 'Administradora',
                'vista' => 'admin.panel',
            ],
            Rol::ENFERMERA => [
                'rol' => 'Enfermera de turno',
                'vista' => 'enfermera.panel',
            ],
            Rol::OPERADOR => [
                'rol' => 'Operadora',
                'vista' => 'flujo.panel',
            ],
        ];
        $vista = $roles[$rol]['vista'];
        $fases = $this->fases();
        if ($rol === Rol::OPERADOR) {
            $consulta = $this->consultaCajas($request);
        } else {
            $todas = $this->cajas();
            $consulta = [
                'busqueda' => '',
                'etapa_filtro' => null,
                'cajas' => $todas,
                'cajas_por_etapa' => [],
                'conteo_etapas' => [],
                'total' => count($todas),
                'hay_filtros' => false,
            ];
        }
        return view($vista, [
            'usuario' => $usuario,
            'rol_key' => $rol,
            'fases' => $fases,
            'cajas' => $consulta['cajas'],
            'cajas_por_etapa' => $consulta['cajas_por_etapa'],
            'busqueda' => $consulta['busqueda'],
            'etapa_filtro' => $consulta['etapa_filtro'],
            'conteo_etapas' => $consulta['conteo_etapas'],
            'total_consulta' => $consulta['total'],
            'hay_filtros' => $consulta['hay_filtros'],
            'kpis' => [
                ['label' => 'En flujo', 'value' => 28, 'hint' => 'Cajas/sets activos', 'tone' => ''],
                ['label' => 'Listas para entrega', 'value' => 12, 'hint' => 'En almacen esteril', 'tone' => 'ok'],
                ['label' => 'Entregadas hoy', 'value' => 19, 'hint' => 'Con custodia', 'tone' => ''],
                ['label' => 'Requieren atencion', 'value' => 3, 'hint' => 'Retraso o faltante', 'tone' => 'warn'],
            ],
            'alertas' => [
                ['nivel' => 'alta', 'titulo' => 'SET-042 lleva 4h en esterilizacion', 'detalle' => 'Pabellon · Autoclave 2'],
                ['nivel' => 'media', 'titulo' => 'CAJA-118 sin custodia al salir de lavado', 'detalle' => 'Urgencia'],
                ['nivel' => 'media', 'titulo' => 'Faltante en SET-007 (1 pinza)', 'detalle' => 'Detectado en preparacion'],
            ],
            'etiquetas_etapa' => array_map(
                fn (string $etapa) => Caja::etiquetaEtapa($etapa),
                Caja::etapas()
            ),
            'panorama' => $this->panorama($consulta['cajas']),
            'falla_produccion' => $request->boolean('falla'),
            'accion_modal' => match ($request->query('accion')) {
                'retroceso' => 'retroceso',
                'etapa', 'actividad' => 'etapa',
                default => null,
            },
            'caja_modal' => trim((string) $request->query('caja', '')),
            'pasos_etapa' => $this->pasosPorEtapa(),
            'anotaciones_por_caja' => $rol === Rol::OPERADOR ? $this->anotacionesPorCaja() : [],
            'ahora_modal' => [
                'fecha' => now()->timezone('America/Santiago')->format('d/m/Y'),
                'hora' => now()->timezone('America/Santiago')->format('H:i'),
            ],
        ]);
    }
    public function recepcion(): View
    {
        return view('flujo.recepcion', [
            'usuario' => $this->usuarioSesion(),
            'servicios' => ['Pabellon', 'Urgencia', 'Maternidad', 'UCI', 'Curaciones', 'Otro servicio'],
            'tipos' => ['Set quirurgico', 'Caja de curacion', 'Contenedor', 'Paquete grado medico'],
        ]);
    }
    public function avanzar(Request $request): RedirectResponse
    {
        return redirect()->route('mockups.panel', array_filter([
            'rol' => Rol::OPERADOR,
            'accion' => 'etapa',
            'caja' => trim((string) $request->query('caja', '')) ?: null,
        ]));
    }
    /**
     * HIU-EP2-002: avanza una sola etapa y deja fecha, hora y usuario.
     */
    public function guardarAvance(Request $request): RedirectResponse
    {
        $codigo = trim((string) $request->input('caja', ''));
        $destino = trim((string) $request->input('etapa_destino', ''));
        $volver = $this->volverAlFlujo($request, $codigo);
        if ($aviso = $this->faltaPuesto()) {
            return $volver->withErrors(['puesto' => $aviso]);
        }
        if ($codigo === '') {
            return $volver->withErrors(['caja' => 'Elige la caja que vas a pasar.']);
        }
        $caja = Caja::query()->where('codigo', $codigo)->first();
        if ($caja === null) {
            return $volver->withErrors(['caja' => 'Esa caja no está en el flujo.']);
        }
        $etapas = Caja::etapas();
        $indice = $caja->indiceEtapa();
        $siguiente = $etapas[$indice + 1] ?? null;
        $etiquetaActual = Caja::etiquetaEtapa($caja->etapa);
        if ($destino === '') {
            return $volver->withErrors([
                'etapa_destino' => 'Falta la etapa a la que pasa la caja.',
            ]);
        }
        if ($siguiente === null) {
            return $volver->withErrors([
                'etapa_destino' => $caja->codigo.' ya está en '.$etiquetaActual.'. El paso que sigue es registrar la entrega.',
            ]);
        }
        if ($destino !== $siguiente) {
            return $volver->withErrors([
                'etapa_destino' => 'No se puede saltar etapas. '.$caja->codigo.' sigue en '.$etiquetaActual.'.',
            ]);
        }
        $caja->etapa = $siguiente;
        $caja->estado = 'En '.Caja::etiquetaEtapa($siguiente);
        $caja->actividad = null;
        $caja->etapa_desde = now();
        $caja->proceso_desde = null;
        $caja->proceso_hasta = null;
        $marca = $this->marca();
        $caja->responsable_id = $marca['user_id'];
        $caja->personal_id = $marca['personal_id'];
        $caja->save();
        $caja->anotaciones()->create([
            'user_id' => $marca['user_id'],
            'personal_id' => $marca['personal_id'],
            'texto' => 'Pasó a '.Caja::etiquetaEtapa($siguiente),
        ]);
        return $this->volverAlFlujo($request, $caja->codigo, false)
            ->with('ok', 'Listo. '.$caja->codigo.' está en '.Caja::etiquetaEtapa($siguiente).'.'.$this->firmaPuesto());
    }
    /**
     * HIU-EP2-003: un paso atrás, con motivo, solo desde lavado o preparación.
     */
    public function guardarRetroceso(Request $request): RedirectResponse
    {
        $codigo = trim((string) $request->input('caja', ''));
        $motivo = trim((string) $request->input('motivo', ''));
        $volver = $this->volverAlFlujo($request, $codigo, true, 'retroceso');
        if ($aviso = $this->faltaPuesto()) {
            return $volver->withErrors(['puesto' => $aviso]);
        }
        if ($codigo === '') {
            return $volver->withErrors(['caja' => 'Elige la caja que vas a corregir.']);
        }
        $caja = Caja::query()->where('codigo', $codigo)->first();
        if ($caja === null) {
            return $volver->withErrors(['caja' => 'Esa caja no está en el flujo.']);
        }
        $anterior = $caja->etapaAnteriorPermitida();
        $etiquetaActual = Caja::etiquetaEtapa(trim((string) $caja->etapa));
        if ($anterior === null) {
            return $volver->withErrors([
                'etapa' => 'El retroceso no está permitido. '.$caja->codigo.' sigue en '.$etiquetaActual.'.',
            ]);
        }
        if ($motivo === '') {
            return $volver->withErrors([
                'motivo' => 'Registra el motivo del retroceso.',
            ]);
        }
        $motivo = mb_substr($motivo, 0, 180);
        $etiquetaAnterior = Caja::etiquetaEtapa($anterior);
        try {
            $marca = $this->marca();
            DB::transaction(function () use ($caja, $anterior, $motivo, $marca): void {
                $origen = trim((string) $caja->etapa);
                $caja->etapa = $anterior;
                $caja->estado = 'En '.Caja::etiquetaEtapa($anterior);
                $caja->actividad = null;
                $caja->etapa_desde = now();
                $caja->proceso_desde = null;
                $caja->proceso_hasta = null;
                $caja->responsable_id = $marca['user_id'];
                $caja->personal_id = $marca['personal_id'];
                $caja->save();
                $caja->retrocesos()->create([
                    'user_id' => $marca['user_id'],
                    'personal_id' => $marca['personal_id'],
                    'etapa_desde' => $origen,
                    'etapa_hacia' => $anterior,
                    'motivo' => $motivo,
                ]);
                $caja->anotaciones()->create([
                    'user_id' => $marca['user_id'],
                    'personal_id' => $marca['personal_id'],
                    'texto' => 'Volvió a '.Caja::etiquetaEtapa($anterior).'. '.$motivo,
                ]);
            });
        } catch (\Throwable) {
            return $volver->withErrors([
                'etapa' => 'No fue posible completar el retroceso. '.$caja->codigo.' sigue en '.$etiquetaActual.'.',
            ]);
        }
        return $this->volverAlFlujo($request, $caja->codigo, false, 'retroceso')
            ->with('ok', 'Listo. '.$caja->codigo.' volvió a '.$etiquetaAnterior.'.'.$this->firmaPuesto());
    }
    private function volverAlFlujo(Request $request, string $codigo, bool $conservarEtapa = true, string $accion = 'etapa'): RedirectResponse
    {
        $params = [
            'rol' => Rol::OPERADOR,
            'accion' => $accion,
        ];
        if ($codigo !== '') {
            $params['caja'] = $codigo;
        }
        $busqueda = trim((string) $request->input('q', ''));
        if ($busqueda !== '') {
            $params['q'] = $busqueda;
        }
        $etapa = $request->input('etapa');
        if ($conservarEtapa && $etapa !== null && $etapa !== '') {
            $params['etapa'] = $etapa;
        }
        return redirect()->route('mockups.panel', $params);
    }
    /**
     * HIU-EP2-004: elegir la caja del flujo y mostrar la etapa en la que ya está.
     * La actividad dentro de esa etapa se agrega en las tareas siguientes.
     */
    public function actividad(Request $request): RedirectResponse
    {
        return redirect()->route('mockups.panel', array_filter([
            'rol' => Rol::OPERADOR,
            'accion' => 'etapa',
            'caja' => trim((string) $request->query('caja', '')) ?: null,
        ]));
    }
    public function guardarAnotacion(Request $request): JsonResponse
    {
        if ($aviso = $this->faltaPuesto()) {
            return response()->json(['mensaje' => $aviso], 422);
        }
        $codigo = trim((string) $request->input('caja', ''));
        $texto = trim((string) $request->input('texto', ''));
        $caja = Caja::query()->where('codigo', $codigo)->first();
        if ($caja === null) {
            return response()->json(['mensaje' => 'Esa caja no está en el flujo.'], 422);
        }
        $etapa = trim($caja->etapa);
        $clave = trim((string) $request->input('clave', ''));
        $detalle = trim((string) $request->input('detalle', ''));
        $actividad = $texto;
        $minutos = null;
        if ($clave !== '') {
            $definicion = $this->tiempoDeClave($etapa, $clave);
            $minutos = filter_var($request->input('minutos'), FILTER_VALIDATE_INT);
            if ($definicion === null) {
                return response()->json(['mensaje' => 'Esa anotación no corresponde a esta etapa.'], 422);
            }
            if ($minutos === false || $minutos < 1 || $minutos > 1440) {
                return response()->json(['mensaje' => 'Indica los minutos.'], 422);
            }
            $actividad = $definicion['etiqueta'];
            $texto = $actividad.': '.$minutos.' min';
        } elseif (! in_array($texto, $this->textosAnotables($etapa), true)) {
            return response()->json(['mensaje' => 'Esa anotación no corresponde a esta etapa.'], 422);
        } else {
            $complemento = $this->complementoDe($etapa, $texto);
            if ($complemento !== null && $detalle === '') {
                return response()->json(['mensaje' => $complemento['falta']], 422);
            }
            if ($detalle !== '') {
                $texto = $actividad.'. '.mb_substr($detalle, 0, 120);
            }
        }
        try {
            $marca = $this->marca();
            $nota = DB::transaction(function () use ($caja, $texto, $actividad, $minutos, $marca) {
                $nota = $caja->anotaciones()->create([
                    'user_id' => $marca['user_id'],
                    'personal_id' => $marca['personal_id'],
                    'texto' => $texto,
                ]);
                $caja->personal_id = $marca['personal_id'] ?? $caja->personal_id;
                $caja->responsable_id = $marca['user_id'];
                $caja->actividad = mb_substr($actividad, 0, 80);
                if ($minutos !== null) {
                    $caja->sumarProceso((int) $minutos);
                }
                $caja->save();
                return $nota;
            });
        } catch (\Throwable) {
            return response()->json(['mensaje' => 'No fue posible completar el registro.'], 500);
        }
        $nota->load(['autor', 'persona']);
        return response()->json([
            'texto' => $nota->texto,
            'actividad' => $caja->actividad,
            'cuando' => $nota->created_at->timezone('America/Santiago')->format('d/m/Y · H:i'),
            'quien' => $nota->nombreQuien(),
            'proceso_desde' => $caja->proceso_desde?->toIso8601String(),
            'proceso_hasta' => $caja->proceso_hasta?->toIso8601String(),
        ]);
    }
    /**
     * @return array<string, list<array{texto: string, cuando: string, quien: string}>>
     */
    private function anotacionesPorCaja(): array
    {
        $grupos = [];
        CajaAnotacion::query()
            ->with(['caja:id,codigo', 'autor:id,name', 'persona:id,nombre'])
            ->orderBy('id')
            ->get()
            ->each(function (CajaAnotacion $nota) use (&$grupos): void {
                $codigo = $nota->caja?->codigo;
                if ($codigo === null) {
                    return;
                }
                $grupos[$codigo][] = [
                    'texto' => $nota->texto,
                    'cuando' => $nota->created_at->timezone('America/Santiago')->format('d/m/Y · H:i'),
                    'quien' => $nota->nombreQuien(),
                ];
            });
        return $grupos;
    }
    /** @return list<string> */
    private function textosAnotables(string $etapa): array
    {
        $paso = $this->pasosPorEtapa()[$etapa] ?? ['opciones' => [], 'tiempos' => []];
        return $paso['opciones'];
    }
    /** @return array{clave: string, etiqueta: string, sugerido: int|null}|null */
    private function tiempoDeClave(string $etapa, string $clave): ?array
    {
        foreach ($this->pasosPorEtapa()[$etapa]['tiempos'] ?? [] as $tiempo) {
            if ($tiempo['clave'] === $clave) {
                return $tiempo;
            }
        }
        return null;
    }
    /** @return array{etiqueta: string, falta: string, placeholder: string}|null */
    private function complementoDe(string $etapa, string $texto): ?array
    {
        $complementos = $this->pasosPorEtapa()[$etapa]['complementos'] ?? [];
        return $complementos[$texto] ?? null;
    }
    /**
     * Cada tramo anota otra cosa. El tiempo solo aparece donde la central lo escribe a mano.
     *
     * @return array<string, array{tramo: string, ayuda: string, opciones: list<string>, tiempos: list<array{texto: string, etiqueta: string}>}>
     */
    private function pasosPorEtapa(): array
    {
        return [
            Caja::ETAPA_RECEPCION => [
                'tramo' => 'Recepción a lavado',
                'resumen' => 'El ingreso ya quedó en Nueva recepción.',
                'ayuda' => 'Servicio, quién entrega y ficha se registran al recibir. Acá solo se confirma el paso a lavado.',
                'opciones' => [],
                'tiempos' => [],
            ],
            Caja::ETAPA_LAVADO => [
                'tramo' => 'Lavado a preparación',
                'resumen' => 'Lavado, secado y la hora de la lavadora.',
                'ayuda' => 'La lavadora, en el ciclo estándar, se demora una hora. Si falla, ese tiempo se anota de nuevo.',
                'opciones' => [],
                'tiempos' => [
                    ['clave' => 'lavadora', 'etiqueta' => 'Lavadora', 'sugerido' => 60],
                    ['clave' => 'secado', 'etiqueta' => 'Secado', 'sugerido' => null],
                ],
            ],
            Caja::ETAPA_PREPARACION => [
                'tramo' => 'Preparación a esterilización',
                'resumen' => 'Inspección, reconteo y quién lo armó.',
                'ayuda' => 'Inspección, reconteo y rotulado de quien armó. Todavía no entra al autoclave.',
                'opciones' => ['Inspección', 'Reconteo', 'Armado y rotulado'],
                'tiempos' => [],
            ],
            Caja::ETAPA_ESTERILIZACION => [
                'tramo' => 'Esterilización a almacén',
                'resumen' => 'La carga, el voucher y la espera para abrir.',
                'ayuda' => 'En el voucher van la temperatura, el tiempo y quién lo tiró. Se copia a mano. Antes de abrir la puerta se esperan 20 minutos.',
                'opciones' => ['Carga del autoclave', 'Voucher de la carga'],
                'complementos' => [
                    'Voucher de la carga' => [
                        'etiqueta' => 'Detalle del voucher',
                        'falta' => 'Falta el detalle del voucher.',
                        'placeholder' => 'Temperatura y tiempo del ciclo',
                    ],
                ],
                'tiempos' => [
                    ['clave' => 'abrir', 'etiqueta' => 'Espera para abrir', 'sugerido' => 20],
                ],
            ],
            Caja::ETAPA_ALMACEN => [
                'tramo' => 'Almacén a entrega',
                'resumen' => 'La descarga y cuánto se dejó enfriar.',
                'ayuda' => 'Al descargar se espera que enfríe: media hora el material chico y una hora el contenedor.',
                'opciones' => ['Descarga'],
                'tiempos' => [
                    ['clave' => 'material_chico', 'etiqueta' => 'Material chico', 'sugerido' => 30],
                    ['clave' => 'contenedor', 'etiqueta' => 'Contenedor', 'sugerido' => 60],
                ],
            ],
            Caja::ETAPA_ENTREGA => [
                'tramo' => 'Entrega',
                'resumen' => 'El servicio, la hora y quién retira.',
                'ayuda' => 'En el libro de salida van el servicio, la hora y quién entrega.',
                'opciones' => ['Entrega al servicio'],
                'tiempos' => [],
            ],
        ];
    }
    public function entrega(Request $request): View
    {
        $listas = collect($this->cajas())
            ->filter(fn (array $c) => (int) $c['fase_idx'] === 5)
            ->values()
            ->all();
        $marcada = trim((string) $request->query('caja', ''));
        $caja = collect($listas)->firstWhere('id', $marcada);
        return view('flujo.entrega', [
            'usuario' => $this->usuarioSesion(),
            'listas' => $listas,
            'caja' => $caja,
            'servicios' => [
                'Pabellon',
                'Urgencia',
                'Maternidad',
                'UCI',
                'Curaciones',
                'Otro servicio',
            ],
            'materiales' => [
                'Paquete de ropa',
                'Material de curación',
                'Instrumental suelto',
            ],
            'fecha' => now()->timezone('America/Santiago')->format('d/m/Y'),
            'hora' => now()->timezone('America/Santiago')->format('H:i'),
        ]);
    }
    public function registrarEntrega(Request $request): RedirectResponse
    {
        $validador = \Illuminate\Support\Facades\Validator::make(
            $request->all(),
            [
                'servicio' => ['required', 'string', 'in:Pabellon,Urgencia,Maternidad,UCI,Curaciones,Otro servicio'],
                'retira' => ['required', 'string', 'max:150'],
                'cajas' => ['nullable', 'array'],
                'cajas.*' => ['required', 'string', 'distinct'],
                'material' => ['nullable', 'string', 'max:100'],
                'otro' => ['nullable', 'string', 'max:150'],
                'obs' => ['nullable', 'string', 'max:1000'],
            ],
            [
                'servicio.required' => 'Debes seleccionar el servicio que recibe la entrega.',
                'servicio.in' => 'El servicio seleccionado no es válido.',
                'retira.required' => 'Debes indicar quién recibe la entrega.',
                'cajas.array' => 'La selección de cajas no es válida.',
                'cajas.*.distinct' => 'No puedes seleccionar una misma caja más de una vez.',
            ]
        );
        $validador->after(function ($validador) use ($request) {
            $cajas = $request->input('cajas', []);
            $material = $request->input('material', '');
            if (
                (!is_array($cajas) || count($cajas) === 0)
                && ($material === null || $material === '')
            ) {
                $validador->errors()->add(
                    'cajas',
                    'Selecciona al menos una caja o un material.'
                );
            }
            if (
                $material === 'otro'
                && trim((string) $request->input('otro', '')) === ''
            ) {
                $validador->errors()->add(
                    'otro',
                    'Indica cuál es el material que deseas entregar.'
                );
            }
        });
        if ($aviso = $this->faltaPuesto()) {
            return back()->withErrors(['puesto' => $aviso])->withInput();
        }
        $datos = $validador->validate();
        $codigos = $datos['cajas'] ?? [];
        // Comprobar que todas las cajas seleccionadas estén listas para entrega.
        if (!empty($codigos)) {
            $cantidadDisponibles = Caja::query()
                ->whereIn('codigo', $codigos)
                ->where('etapa', 'entrega')
                ->whereNull('entregado_at')
                ->count();
            if ($cantidadDisponibles !== count($codigos)) {
                return back()
                    ->withErrors([
                        'cajas' => 'Una o más cajas ya no están disponibles para entrega. Actualiza el formulario.',
                    ])
                    ->withInput();
            }
            // Registrar la fecha y hora de entrega de las cajas.
            $marca = $this->marca();
            Caja::query()
                ->whereIn('codigo', $codigos)
                ->where('etapa', 'entrega')
                ->whereNull('entregado_at')
                ->update([
                    'entregado_at' => now(),
                    'responsable_id' => $marca['user_id'],
                    'personal_id' => $marca['personal_id'],
                ]);
        }
        return back()->with(
            'ok',
            'La entrega se registró correctamente.'.$this->firmaPuesto()
        );
    }

    public function elegirPuesto(Request $request): RedirectResponse
    {
        $cuenta = User::query()
            ->where('activo', true)
            ->where('email', '!=', User::CUENTA_PUESTO)
            ->whereHas('rol', fn ($q) => $q->where('nombre', Rol::OPERADOR))
            ->find($request->input('user_id'));
        if ($cuenta === null) {
            return back()->withErrors([
                'user_id' => 'Elige una cuenta de operadora.',
            ]);
        }
        session(['puesto_user_id' => $cuenta->id]);

        return back();
    }

    private function faltaPuesto(): ?string
    {
        $user = Auth::user();
        if ($user === null || $user->nombreRol() !== Rol::OPERADOR) {
            return null;
        }
        if (User::quienRegistra() === null) {
            return 'Elige qué cuenta de operadora está en el puesto antes de registrar.';
        }

        return null;
    }

    /** @return array{user_id: int|null, personal_id: int|null} */
    private function marca(): array
    {
        $quien = User::quienRegistra();
        $ficha = $quien === null ? null : Personal::query()->where('email', $quien->email)->first();

        return [
            'user_id' => $quien?->id ?? Auth::id(),
            'personal_id' => $ficha?->id,
        ];
    }

    private function firmaPuesto(): string
    {
        $quien = User::quienRegistra();

        return $quien === null ? '' : ' Lo registró '.$quien->name.'.';
    }
}
