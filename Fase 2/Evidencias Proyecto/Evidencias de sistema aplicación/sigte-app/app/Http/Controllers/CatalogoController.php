<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveUsuario;
use App\Models\Caja;
use App\Models\Ficha;
use App\Models\Instrumento;
use App\Support\AlmacenImagenes;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    use ResuelveUsuario;

    private function catalogoItems(): array
    {
        // El catálogo muestra únicamente las fichas existentes en PostgreSQL.
        return $this->catalogoDesdeFichas();
    }

    /** @return list<array<string, mixed>> */
    private function catalogoLista(): array
    {
        if (! Ficha::query()->exists()) {
            return $this->catalogoItems();
        }

        // Sin columna BYTEA imagen: el listado solo necesita ruta/flag para la URL.
        $columnas = [
            'id', 'codigo', 'nombre', 'tipo', 'servicio_id', 'guia_visual',
        ];
        if (Schema::hasColumn('fichas', 'imagen_ruta')) {
            $columnas[] = 'imagen_ruta';
        }

        $idsConBytea = Schema::hasColumn('fichas', 'imagen')
            ? DB::table('fichas')->whereNotNull('imagen')->pluck('id')->flip()
            : collect();

        return Ficha::query()
            ->with(['items.instrumento:id,nombre,codigo_fabricante', 'servicioClinico:id,nombre'])
            ->orderBy('nombre')
            ->withSum('items as piezas', 'cantidad')
            ->get($columnas)
            ->map(function (Ficha $ficha) use ($idsConBytea): array {
                $piezas = (int) $ficha->getAttribute('piezas');
                $contenido = $ficha->items
                    ->map(fn ($linea) => trim(($linea->instrumento?->codigo_fabricante ?? '').' '.($linea->instrumento?->nombre ?? '')))
                    ->implode(' ');

                return [
                    'codigo' => $ficha->codigo,
                    'nombre' => $ficha->nombre,
                    'tipo' => $ficha->tipo === 'set' ? 'Set quirúrgico' : ($ficha->tipo === 'caja' ? 'Caja quirúrgica' : $ficha->tipo),
                    'servicio' => $ficha->servicioClinico?->nombre ?? 'Sin servicio asignado',
                    'guia' => (bool) $ficha->guia_visual,
                    'imagen_url' => $this->urlFotoFicha($ficha, $idsConBytea->has($ficha->id)),
                    'instrumentos' => [],
                    'piezas' => $piezas,
                    'buscar' => $contenido,
                ];
            })
            ->all();
    }

    /** @return list<array<string, mixed>> */
    private function catalogoDesdeFichas(): array
    {
        if (! Ficha::query()->exists()) {
            return [];
        }

        $columnas = [
            'id', 'codigo', 'nombre', 'tipo', 'servicio_id', 'tipo_conjunto_id', 'guia_visual',
        ];
        if (Schema::hasColumn('fichas', 'imagen_ruta')) {
            $columnas[] = 'imagen_ruta';
        }

        $idsConBytea = Schema::hasColumn('fichas', 'imagen')
            ? DB::table('fichas')->whereNotNull('imagen')->pluck('id')->flip()
            : collect();

        return Ficha::query()
            ->with([
                'items.instrumento:id,nombre,codigo_fabricante,tamano',
                'servicioClinico:id,nombre',
            ])
            ->orderBy('nombre')
            ->get($columnas)
            ->map(function (Ficha $ficha) use ($idsConBytea): array {
                $instrumentos = $ficha->items->map(function ($linea): array {
                    $tamano = trim((string) $linea->instrumento?->tamano);

                    return [
                        'id' => $linea->instrumento_id,
                        'codigo' => $linea->instrumento?->codigo_fabricante ?? '',
                        'nombre' => trim(($linea->instrumento?->nombre ?? '').($tamano !== '' ? ' '.$tamano : '')),
                        'cantidad' => $linea->cantidad,
                        'imagen' => false,
                    ];
                })->all();

                return [
                    'codigo' => $ficha->codigo,
                    'nombre' => $ficha->nombre,
                    'tipo' => $ficha->tipo === 'set' ? 'Set quirúrgico' : ($ficha->tipo === 'caja' ? 'Caja quirúrgica' : $ficha->tipo),
                    'servicio' => $ficha->servicioClinico?->nombre ?? 'Sin servicio asignado',
                    'servicio_real' => $ficha->servicioClinico?->nombre ?? 'Sin servicio asignado',
                    'servicio_id' => $ficha->servicio_id,
                    'id' => $ficha->id,
                    'tipo_conjunto_id' => $ficha->tipo_conjunto_id,
                    'guia' => (bool) $ficha->guia_visual,
                    'imagen_url' => $this->urlFotoFicha($ficha, $idsConBytea->has($ficha->id)),
                    'instrumentos' => $instrumentos,
                    'piezas' => array_sum(array_column($instrumentos, 'cantidad')),
                ];
            })
            ->all();
    }

    public function catalogo(Request $request): View
    {
        $items = $this->catalogoLista();
        $busqueda = trim((string) $request->query('q', ''));
        $servicio = trim((string) $request->query('servicio', ''));
        $servicios = collect($items)->pluck('servicio')->unique()->sort()->values()->all();
        $needle = mb_strtolower($busqueda);
        $porBusqueda = array_values(array_filter(
            $items,
            fn (array $item): bool => $this->coincideBusqueda($item, $needle)
        ));
        $conteoServicios = [];
        foreach ($porBusqueda as $item) {
            $clave = $item['servicio'];
            $conteoServicios[$clave] = ($conteoServicios[$clave] ?? 0) + 1;
        }
        $filtrados = $porBusqueda;
        if ($servicio !== '') {
            $filtrados = array_values(array_filter(
                $porBusqueda,
                fn (array $item) => $item['servicio'] === $servicio
            ));
        }
        // Solo el ciclo activo: con reingreso B puede haber varias filas del mismo código.
        $etapas = $filtrados === []
            ? collect()
            : Caja::query()
                ->whereIn('codigo', array_column($filtrados, 'codigo'))
                ->whereNull('entregado_at')
                ->pluck('etapa', 'codigo');
        $filtrados = array_map(function (array $item) use ($etapas): array {
            $etapa = $etapas[$item['codigo']] ?? null;
            $item['etapa_actual'] = $etapa ? Caja::etiquetaEtapa($etapa) : null;

            return $item;
        }, $filtrados);
        usort($filtrados, fn (array $a, array $b): int =>
            strcasecmp((string) $a['servicio'], (string) $b['servicio'])
            ?: strcasecmp((string) $a['nombre'], (string) $b['nombre'])
        );
        return view('catalogo.index', [
            'usuario' => $this->usuarioSesion(),
            'items' => $filtrados,
            'busqueda' => $busqueda,
            'servicio' => $servicio,
            'servicios' => $servicios,
            'conteo_servicios' => $conteoServicios,
            'total_servicios' => count($porBusqueda),
            'hay_filtros' => $busqueda !== '' || $servicio !== '',
        ]);
    }
    /** Foto de ficha (archivo en uploads/ o BYTEA legado). */
    public function imagenFicha(int $id)
    {
        $columnas = ['imagen', 'imagen_formato'];
        if (Schema::hasColumn('fichas', 'imagen_ruta')) {
            $columnas[] = 'imagen_ruta';
        }
        $ficha = DB::table('fichas')->where('id', $id)->first($columnas);

        abort_if(! $ficha, 404);

        if (! empty($ficha->imagen_ruta)) {
            $url = AlmacenImagenes::url($ficha->imagen_ruta);
            abort_if($url === null, 404);

            return redirect()->away($url);
        }

        abort_if(! $ficha->imagen, 404);

        $bytes = $ficha->imagen;

        if (is_resource($bytes)) {
            $bytes = stream_get_contents($bytes);
        }

        if (is_string($bytes) && str_starts_with($bytes, '\\x')) {
            $bytes = hex2bin(substr($bytes, 2));
        }

        abort_unless(is_string($bytes) && $bytes !== '', 404);

        $mime = (string) ($ficha->imagen_formato ?? '');

        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes);
        }

        abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true), 404);

        return response($bytes, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function catalogoAdmin(Request $request)
    {
        // Se reutiliza la ruta GET existente para entregar fotografías sin modificar web.php.
        if ($request->query('instrumento_imagen') !== null) {
            abort_unless(ctype_digit((string) $request->query('instrumento_imagen')), 404);
            $id = (int) $request->query('instrumento_imagen');
            $columnas = ['id'];
            if (Schema::hasColumn('instrumentos', 'imagen_ruta')) {
                $columnas[] = 'imagen_ruta';
            }
            if (Schema::hasColumn('instrumentos', 'imagen')) {
                $columnas[] = 'imagen';
            }
            $campoMime = Schema::hasColumn('instrumentos', 'imagen_mime') ? 'imagen_mime'
                : (Schema::hasColumn('instrumentos', 'imagen_formato') ? 'imagen_formato' : null);
            if ($campoMime) {
                $columnas[] = $campoMime;
            }
            $registro = DB::table('instrumentos')->where('id', $id)->first($columnas);
            abort_if(! $registro, 404);
            if (! empty($registro->imagen_ruta)) {
                $url = AlmacenImagenes::url((string) $registro->imagen_ruta);
                abort_if($url === null, 404);

                return redirect()->away($url);
            }
            abort_unless(Schema::hasColumn('instrumentos', 'imagen') && $registro->imagen, 404);
            $bytes = $registro->imagen;
            if (is_resource($bytes)) $bytes = stream_get_contents($bytes);
            if (is_string($bytes) && str_starts_with($bytes, '\\x')) {
                $bytes = hex2bin(substr($bytes, 2));
            }
            abort_unless(is_string($bytes) && $bytes !== '', 404);
            $mime = $campoMime ? (string) ($registro->{$campoMime} ?? '') : '';
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                $detector = new \finfo(FILEINFO_MIME_TYPE);
                $mime = $detector->buffer($bytes);
            }
            abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true), 404);
            return response($bytes, 200, [
                'Content-Type' => $mime,
                'Cache-Control' => 'private, max-age=3600',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        // Servicios reales desde la tabla servicios.
        $servicios = DB::table('servicios')->orderBy('nombre')->pluck('nombre')->all();
        $tiposConjuntoInstrumento = DB::table('tipo_conjunto')->orderBy('nombre_conjunto')->get(['id', 'codigo', 'nombre_conjunto', 'servicio_id']);
        $serviciosFicha = DB::table('servicios')->orderBy('nombre')->get(['id', 'nombre']);
        $items = $this->catalogoItems();
        $tieneImagen = Schema::hasColumn('instrumentos', 'imagen');
        $tieneRuta = Schema::hasColumn('instrumentos', 'imagen_ruta');
        $consultaInstrumentos = DB::table('instrumentos')
            ->select('id', 'nombre', 'tamano', 'codigo_fabricante', 'tipo_conjunto_id');
        if ($tieneRuta) {
            $consultaInstrumentos->addSelect('imagen_ruta');
        }
        if ($tieneImagen) {
            $consultaInstrumentos->selectRaw('(imagen IS NOT NULL) AS tiene_imagen_bytea');
        }
        $instrumentos = $consultaInstrumentos
            ->orderBy('nombre')->get()
            ->map(function ($inst) use ($tieneImagen, $tieneRuta) {
                $url = null;
                if ($tieneRuta && ! empty($inst->imagen_ruta)) {
                    $url = AlmacenImagenes::url($inst->imagen_ruta);
                } elseif ($tieneImagen && ! empty($inst->tiene_imagen_bytea)) {
                    $url = url()->current().'?instrumento_imagen='.$inst->id;
                }

                return [
                    'id' => $inst->id,
                    'nombre' => $inst->nombre,
                    'tamano' => $inst->tamano,
                    'codigo' => $inst->codigo_fabricante,
                    'tipo_conjunto_id' => $inst->tipo_conjunto_id,
                    'imagen_url' => $url,
                ];
            })->all();

        return view('catalogo.admin', [
            'usuario' => $this->usuarioSesion(),
            'items' => $items,
            'instrumentosCatalogo' => $instrumentos,
            'tipos' => [
                'Caja quirúrgica',
                'Set de instrumental',
                'Set de ópticas',
                'Set de accesorios',
                'Caja de implantes',
                'Otros',
            ],
            'servicios' => $servicios,
            'tiposConjuntoInstrumento' => $tiposConjuntoInstrumento,
            'serviciosFicha' => $serviciosFicha,
        ]);
    }

    /** Crea un tipo de conjunto que puede utilizarse en instrumentos y fichas. */
    public function guardarTipoConjunto(Request $request)
    {
        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:100'],
            'nombre_conjunto' => ['required', 'string', 'max:255'],
            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
        ]);

        $codigo = mb_strtoupper(trim($datos['codigo']));
        $nombre = trim($datos['nombre_conjunto']);
        if ($codigo === '' || $nombre === '') {
            throw ValidationException::withMessages([
                'codigo' => 'El código y el nombre son obligatorios.',
            ]);
        }
        if (DB::table('tipo_conjunto')->whereRaw('UPPER(codigo) = ?', [$codigo])->exists()) {
            throw ValidationException::withMessages([
                'codigo' => 'Ya existe un tipo de conjunto con ese código.',
            ]);
        }

        $valores = [
            'codigo' => $codigo,
            'nombre_conjunto' => $nombre,
            'servicio_id' => (int) $datos['servicio_id'],
        ];
        if (Schema::hasColumn('tipo_conjunto', 'created_at')) {
            $valores['created_at'] = now();
        }
        if (Schema::hasColumn('tipo_conjunto', 'updated_at')) {
            $valores['updated_at'] = now();
        }
        $id = DB::table('tipo_conjunto')->insertGetId($valores);

        return response()->json([
            'message' => 'Tipo de conjunto registrado correctamente.',
            'tipo_conjunto' => [
                'id' => $id,
                'codigo' => $codigo,
                'nombre_conjunto' => $nombre,
                'servicio_id' => (int) $datos['servicio_id'],
            ],
        ], 201);
    }

    public function guardarInstrumento(Request $request)
    {
        // La ruta debe permanecer protegida por los middleware auth y acceso.
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:200'],
            'codigo_fabricante' => ['nullable', 'string', 'max:150'],
            'tamano' => ['nullable', 'string', 'max:150'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'tipo_conjunto_id' => ['required', 'integer', 'exists:tipo_conjunto,id'],
        ]);

        $foto = $request->file('foto');
        if ($foto && ! Schema::hasColumn('instrumentos', 'imagen')) {
            return response()->json(['message' => 'La tabla instrumentos no tiene la columna imagen.'], 422);
        }

        $imagenUrl = null;
        $instrumento = DB::transaction(function () use ($datos, $foto, &$imagenUrl) {
            $registro = Instrumento::create([
                'nombre' => trim($datos['nombre']),
                'codigo_fabricante' => trim($datos['codigo_fabricante'] ?? '') ?: null,
                'tamano' => trim($datos['tamano'] ?? '') ?: null,
                'tipo_conjunto_id' => (int) $datos['tipo_conjunto_id'],
            ]);
            if ($foto) {
                $imagenUrl = $this->guardarFotoInstrumento($registro->id, $foto, trim($datos['nombre']));
            }

            return $registro;
        });

        return response()->json([
            'message' => 'Instrumento registrado correctamente.',
            'instrumento' => [
                'id' => $instrumento->id,
                'nombre' => $instrumento->nombre,
                'codigo' => $instrumento->codigo_fabricante ?? '',
                'tamano' => $instrumento->tamano ?? '',
                'tipo_conjunto_id' => (int) $datos['tipo_conjunto_id'],
                'imagen_url' => $imagenUrl,
            ],
        ], 201);
    }

    public function actualizarInstrumento(Request $request, Instrumento $instrumento)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:200'],
            'codigo_fabricante' => ['nullable', 'string', 'max:150'],
            'tamano' => ['nullable', 'string', 'max:150'],
            'tipo_conjunto_id' => ['required', 'integer', 'exists:tipo_conjunto,id'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'quitar_foto' => ['nullable', 'boolean'],
        ]);

        $foto = $request->file('foto');
        $quitarFoto = (bool) ($datos['quitar_foto'] ?? false);
        if (($foto || $quitarFoto) && ! Schema::hasColumn('instrumentos', 'imagen')) {
            return response()->json(['message' => 'La tabla instrumentos no tiene la columna imagen.'], 422);
        }

        $imagenUrl = null;
        DB::transaction(function () use ($instrumento, $datos, $foto, $quitarFoto, &$imagenUrl) {
            $instrumento->update([
                'nombre' => trim($datos['nombre']),
                'codigo_fabricante' => trim($datos['codigo_fabricante'] ?? '') ?: null,
                'tamano' => trim($datos['tamano'] ?? '') ?: null,
                'tipo_conjunto_id' => (int) $datos['tipo_conjunto_id'],
            ]);

            $campoFormato = Schema::hasColumn('instrumentos', 'imagen_mime') ? 'imagen_mime'
                : (Schema::hasColumn('instrumentos', 'imagen_formato') ? 'imagen_formato' : null);

            if ($foto) {
                $rutaAnterior = Schema::hasColumn('instrumentos', 'imagen_ruta')
                    ? DB::table('instrumentos')->where('id', $instrumento->id)->value('imagen_ruta')
                    : null;
                AlmacenImagenes::borrar($rutaAnterior ? (string) $rutaAnterior : null);
                $imagenUrl = $this->guardarFotoInstrumento($instrumento->id, $foto, $instrumento->nombre);
            } elseif ($quitarFoto) {
                $rutaAnterior = Schema::hasColumn('instrumentos', 'imagen_ruta')
                    ? DB::table('instrumentos')->where('id', $instrumento->id)->value('imagen_ruta')
                    : null;
                AlmacenImagenes::borrar($rutaAnterior ? (string) $rutaAnterior : null);
                $cambios = ['imagen' => null];
                if ($campoFormato) {
                    $cambios[$campoFormato] = null;
                }
                if (Schema::hasColumn('instrumentos', 'imagen_ruta')) {
                    $cambios['imagen_ruta'] = null;
                }
                DB::table('instrumentos')->where('id', $instrumento->id)->update($cambios);
            } else {
                $imagenUrl = $this->urlFotoInstrumento($instrumento->id);
            }
        });

        return response()->json([
            'message' => 'Instrumento actualizado correctamente.',
            'instrumento' => [
                'id' => $instrumento->id,
                'nombre' => $instrumento->nombre,
                'codigo' => $instrumento->codigo_fabricante ?? '',
                'tamano' => $instrumento->tamano ?? '',
                'tipo_conjunto_id' => (int) $instrumento->tipo_conjunto_id,
                'imagen_url' => $imagenUrl,
            ],
        ]);
    }


    /** Registra o actualiza una ficha y su composición en una sola transacción. */
    public function guardarFicha(Request $request, ?Ficha $ficha = null)
    {
        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:100'],
            'nombre' => ['required', 'string', 'max:255'],
            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
            'tipo_conjunto_id' => ['required', 'integer', 'exists:tipo_conjunto,id'],
            'guia_visual' => ['nullable', 'boolean'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'instrumentos' => ['required', 'array', 'min:1'],
            'instrumentos.*.instrumento_id' => ['required', 'integer', 'distinct', 'exists:instrumentos,id'],
            'instrumentos.*.cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $conjunto = DB::table('tipo_conjunto')->where('id', $datos['tipo_conjunto_id'])->first(['id', 'servicio_id', 'nombre_conjunto']);
        if (!$conjunto || (int) $conjunto->servicio_id !== (int) $datos['servicio_id']) {
            throw \Illuminate\Validation\ValidationException::withMessages(['tipo_conjunto_id' => 'El tipo de conjunto no pertenece al servicio seleccionado.']);
        }
        $ids = collect($datos['instrumentos'])->pluck('instrumento_id')->all();
        $validos = DB::table('instrumentos')->whereIn('id', $ids)->where('tipo_conjunto_id', $conjunto->id)->count();
        if ($validos !== count($ids)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['instrumentos' => 'Hay instrumentos que no pertenecen al tipo de conjunto seleccionado.']);
        }
        $codigo = mb_strtoupper(trim($datos['codigo']));
        $duplicado = DB::table('fichas')->whereRaw('UPPER(codigo) = ?', [$codigo]);
        if ($ficha) $duplicado->where('id', '<>', $ficha->id);
        if ($duplicado->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['codigo' => 'Este código ya está registrado.']);
        }

        $registro = DB::transaction(function () use ($datos, $codigo, $ficha) {
            $valores = [
                'codigo' => $codigo,
                'nombre' => trim($datos['nombre']),
                'servicio_id' => (int) $datos['servicio_id'],
                'tipo_conjunto_id' => (int) $datos['tipo_conjunto_id'],
                'guia_visual' => (bool) ($datos['guia_visual'] ?? false),
                'updated_at' => now(),
            ];
            // El tipo de registro es "caja"; el conjunto específico se guarda por FK.
            if (!$ficha) {
                $valores['tipo'] = 'caja';
                $valores['created_at'] = now();
            }
            if ($requestFoto = request()->file('foto')) {
                if ($ficha && Schema::hasColumn('fichas', 'imagen_ruta') && ! empty($ficha->imagen_ruta)) {
                    AlmacenImagenes::borrar((string) $ficha->imagen_ruta);
                }
                $guardada = AlmacenImagenes::guardar($requestFoto, 'fichas', $codigo);
                $valores['imagen_formato'] = $guardada['mime'];
                if (Schema::hasColumn('fichas', 'imagen_ruta')) {
                    $valores['imagen_ruta'] = $guardada['ruta'];
                    $valores['imagen'] = null;
                } else {
                    // Fallback legado si aún no corre la migración de ruta.
                    $valores['imagen'] = DB::raw("decode('".bin2hex(file_get_contents($requestFoto->getRealPath()))."', 'hex')");
                }
            }
            if ($ficha) {
                DB::table('fichas')->where('id', $ficha->id)->update($valores);
                $id = $ficha->id;
                DB::table('ficha_instrumentos')->where('ficha_id', $id)->delete();
            } else {
                $id = DB::table('fichas')->insertGetId($valores);
            }
            foreach ($datos['instrumentos'] as $orden => $linea) {
                DB::table('ficha_instrumentos')->insert([
                    'ficha_id' => $id,
                    'instrumento_id' => (int) $linea['instrumento_id'],
                    'tipo_conjunto_id' => (int) $datos['tipo_conjunto_id'],
                    'cantidad' => (int) $linea['cantidad'],
                    'orden' => $orden + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            return $id;
        });
        return response()->json(['message' => $ficha ? 'Ficha actualizada correctamente.' : 'Ficha creada correctamente.', 'id' => $registro]);
    }

    public function ficha(string $codigo): View
    {
        $registro = Ficha::query()
            ->with(['items.instrumento', 'servicioClinico'])
            ->whereRaw('lower(codigo) = ?', [mb_strtolower($codigo)])
            ->first();
        if ($registro !== null) {
            $tieneBytea = Schema::hasColumn('fichas', 'imagen')
                && DB::table('fichas')->where('id', $registro->id)->whereNotNull('imagen')->exists();
            $item = [
                'codigo' => $registro->codigo,
                'nombre' => $registro->nombre,
                'tipo' => $registro->tipo === 'set' ? 'Set quirúrgico' : ($registro->tipo === 'caja' ? 'Caja quirúrgica' : $registro->tipo),
                'servicio' => $registro->servicioClinico?->nombre ?? 'Sin servicio asignado',
                'guia' => (bool) $registro->guia_visual,
                'imagen_url' => $this->urlFotoFicha($registro, $tieneBytea),
                'instrumentos' => $registro->items->map(function ($linea): array {
                    $tamano = trim((string) $linea->instrumento?->tamano);

                    return [
                        'id' => $linea->instrumento_id,
                        'codigo' => $linea->instrumento?->codigo_fabricante ?? '',
                        'nombre' => trim(($linea->instrumento?->nombre ?? '').($tamano !== '' ? ' '.$tamano : '')),
                        'cantidad' => $linea->cantidad,
                        'imagen' => false,
                    ];
                })->all(),
                'piezas' => (int) $registro->items->sum('cantidad'),
            ];
        } else {
            $item = collect($this->catalogoItems())->first(
                fn (array $caja) => strcasecmp($caja['codigo'], $codigo) === 0
            );
        }
        if ($item !== null) {
            $item = $this->conEtapaActual($item);
        }
        return view('catalogo.ficha', [
            'usuario' => $this->usuarioSesion(),
            'item' => $item,
            'codigo' => $codigo,
        ]);
    }
    /**
     * La etapa pertenece al ejemplar en proceso, no a la definición del set.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    /** @param  array<string, mixed>  $item */
    private function coincideBusqueda(array $item, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        $texto = mb_strtolower(implode(' ', array_filter([
            $item['codigo'] ?? '',
            $item['nombre'] ?? '',
            $item['servicio'] ?? '',
            $item['tipo'] ?? '',
            $item['buscar'] ?? '',
        ])));

        return str_contains($texto, $needle);
    }

    private function conEtapaActual(array $item): array
    {
        $caja = Caja::enFlujo($item['codigo']);
        $item['etapa_actual'] = $caja ? Caja::etiquetaEtapa($caja->etapa) : null;
        return $item;
    }

    private function urlFotoFicha(Ficha $ficha, bool $tieneBytea = false): ?string
    {
        if (Schema::hasColumn('fichas', 'imagen_ruta') && ! empty($ficha->imagen_ruta)) {
            return AlmacenImagenes::url((string) $ficha->imagen_ruta);
        }

        return $tieneBytea
            ? route('fichas.imagen', ['id' => $ficha->id, 'v' => time()])
            : null;
    }

    private function urlFotoInstrumento(int $id): ?string
    {
        $columnas = ['id'];
        if (Schema::hasColumn('instrumentos', 'imagen_ruta')) {
            $columnas[] = 'imagen_ruta';
        }
        if (Schema::hasColumn('instrumentos', 'imagen')) {
            $columnas[] = DB::raw('(imagen IS NOT NULL) AS tiene_imagen_bytea');
        }
        $inst = DB::table('instrumentos')->where('id', $id)->first($columnas);
        if (! $inst) {
            return null;
        }
        if (! empty($inst->imagen_ruta)) {
            return AlmacenImagenes::url((string) $inst->imagen_ruta);
        }
        if (! empty($inst->tiene_imagen_bytea)) {
            return route('mockups.catalogo.admin', ['instrumento_imagen' => $id, 'v' => time()]);
        }

        return null;
    }

    private function guardarFotoInstrumento(int $id, UploadedFile $foto, string $prefijo): string
    {
        $guardada = AlmacenImagenes::guardar($foto, 'instrumentos', $prefijo);
        $cambios = [
            'imagen' => null,
            'imagen_formato' => $guardada['mime'],
        ];
        if (Schema::hasColumn('instrumentos', 'imagen_ruta')) {
            $cambios['imagen_ruta'] = $guardada['ruta'];
        } else {
            $hex = bin2hex(file_get_contents($foto->getRealPath()));
            DB::update("UPDATE instrumentos SET imagen = decode(?, 'hex') WHERE id = ?", [$hex, $id]);
            unset($cambios['imagen']);
        }
        if (Schema::hasColumn('instrumentos', 'imagen_mime')) {
            $cambios['imagen_mime'] = $guardada['mime'];
            unset($cambios['imagen_formato']);
        }
        DB::table('instrumentos')->where('id', $id)->update($cambios);

        return $guardada['url'].'?v='.time();
    }
}
