<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $ahora = now();

        Schema::create('salas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 16)->unique();
            $table->string('nombre', 80);
            $table->timestamps();
        });

        DB::table('salas')->insert([
            ['codigo' => 'lavado', 'nombre' => 'Sala de lavado', 'created_at' => $ahora, 'updated_at' => $ahora],
            ['codigo' => 'armado', 'nombre' => 'Sala de armado', 'created_at' => $ahora, 'updated_at' => $ahora],
            ['codigo' => 'esteril', 'nombre' => 'Material estéril', 'created_at' => $ahora, 'updated_at' => $ahora],
        ]);

        Schema::table('elementos_inventario', function (Blueprint $table) {
            $table->foreignId('sala_id')->nullable()->after('id')->constrained('salas')->restrictOnDelete();
        });

        $salas = DB::table('salas')->pluck('id', 'codigo');
        foreach (DB::table('elementos_inventario')->get() as $elemento) {
            $codigo = (string) ($elemento->sala ?? 'lavado');
            DB::table('elementos_inventario')->where('id', $elemento->id)->update([
                'sala_id' => $salas[$codigo] ?? $salas['lavado'],
            ]);
        }

        Schema::table('elementos_inventario', function (Blueprint $table) {
            $table->dropUnique(['sala', 'codigo']);
            $table->dropColumn('sala');
            $table->unique(['sala_id', 'codigo']);
        });

        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('elemento_inventario_id')->constrained('elementos_inventario')->cascadeOnDelete();
            $table->string('tipo', 16);
            $table->unsignedInteger('cantidad');
            $table->string('observacion')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        foreach (DB::table('reposiciones')->get() as $fila) {
            DB::table('movimientos_inventario')->insert([
                'elemento_inventario_id' => $fila->elemento_inventario_id,
                'tipo' => 'reposicion',
                'cantidad' => $fila->cantidad,
                'observacion' => null,
                'user_id' => $fila->user_id,
                'created_at' => $fila->created_at,
                'updated_at' => $fila->updated_at,
            ]);
        }

        $porCodigo = DB::table('elementos_inventario')->orderBy('id')->get()->groupBy('codigo');
        foreach (DB::table('insumos')->get() as $insumo) {
            $destino = $porCodigo[$insumo->codigo][0] ?? null;
            if ($destino === null) {
                $id = DB::table('elementos_inventario')->insertGetId([
                    'sala_id' => $salas['lavado'],
                    'codigo' => $insumo->codigo,
                    'nombre' => $insumo->nombre,
                    'ubicacion' => $insumo->ubicacion,
                    'stock' => $insumo->stock,
                    'minimo' => $insumo->minimo,
                    'en_proceso' => 0,
                    'created_at' => $insumo->created_at,
                    'updated_at' => $insumo->updated_at,
                ]);
                $destino = (object) ['id' => $id];
            }
            foreach (DB::table('movimientos_insumo')->where('insumo_id', $insumo->id)->get() as $mov) {
                DB::table('movimientos_inventario')->insert([
                    'elemento_inventario_id' => $destino->id,
                    'tipo' => $mov->tipo,
                    'cantidad' => $mov->cantidad,
                    'observacion' => $mov->observacion,
                    'user_id' => $mov->user_id,
                    'created_at' => $mov->created_at,
                    'updated_at' => $mov->updated_at,
                ]);
            }
        }

        Schema::dropIfExists('reposiciones');
        Schema::dropIfExists('movimientos_insumo');
        Schema::dropIfExists('insumos');

        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 32)->unique();
            $table->string('nombre', 80);
            $table->timestamps();
        });

        $servicios = [
            'pabellon' => 'Pabellon',
            'dental' => 'Dental',
            'maternidad' => 'Maternidad',
            'urgencia' => 'Urgencia',
            'uci' => 'UCI',
            'curaciones' => 'Curaciones',
            'general' => 'General',
            'otro' => 'Otro servicio',
        ];
        foreach ($servicios as $codigo => $nombre) {
            DB::table('servicios')->insert([
                'codigo' => $codigo,
                'nombre' => $nombre,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        }
        $servicioId = DB::table('servicios')->pluck('id', 'codigo');

        Schema::table('cajas', function (Blueprint $table) {
            $table->foreignId('servicio_id')->nullable()->after('codigo')->constrained('servicios')->nullOnDelete();
        });
        Schema::table('consumos_servicio', function (Blueprint $table) {
            $table->dropUnique(['servicio', 'periodo']);
            $table->foreignId('servicio_id')->nullable()->after('id')->constrained('servicios')->nullOnDelete();
        });

        foreach (DB::table('cajas')->get() as $caja) {
            DB::table('cajas')->where('id', $caja->id)->update([
                'servicio_id' => $servicioId[$this->codigoServicio((string) $caja->servicio)] ?? $servicioId['general'],
            ]);
        }
        foreach (DB::table('consumos_servicio')->get() as $consumo) {
            DB::table('consumos_servicio')->where('id', $consumo->id)->update([
                'servicio_id' => $servicioId[$this->codigoServicio((string) $consumo->servicio)] ?? $servicioId['general'],
            ]);
        }

        Schema::table('cajas', function (Blueprint $table) {
            $table->dropColumn('servicio');
        });
        Schema::table('consumos_servicio', function (Blueprint $table) {
            $table->dropColumn('servicio');
            $table->unique(['servicio_id', 'periodo']);
        });

        Schema::table('personal', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->after('id')->constrained('users')->nullOnDelete();
        });
        $usuarios = DB::table('users')->get(['id', 'email']);
        foreach (DB::table('personal')->get() as $persona) {
            $cuenta = $usuarios->first(fn ($user) => strcasecmp((string) $user->email, (string) $persona->email) === 0);
            if ($cuenta !== null) {
                DB::table('personal')->where('id', $persona->id)->update(['user_id' => $cuenta->id]);
            }
        }

        Schema::table('cajas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('personal_id');
        });
        Schema::table('caja_anotaciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('personal_id');
        });
        Schema::table('caja_retrocesos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('personal_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        // Entregas: las crea 2026_10_28_210000_create_entregas_tables (modelo de yami-sprint-3).

        Schema::create('instrumentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 160);
            $table->string('tamano', 40)->default('');
            $table->string('codigo_fabricante', 80)->default('');
            $table->timestamps();
            $table->unique(['nombre', 'tamano', 'codigo_fabricante']);
        });

        Schema::create('fichas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 32)->unique();
            $table->string('nombre', 160);
            $table->string('tipo', 16);
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('ficha_instrumentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ficha_id')->constrained('fichas')->cascadeOnDelete();
            $table->foreignId('instrumento_id')->constrained('instrumentos')->restrictOnDelete();
            $table->unsignedInteger('cantidad');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
            $table->unique(['ficha_id', 'instrumento_id']);
        });

        Schema::table('cajas', function (Blueprint $table) {
            $table->foreignId('ficha_id')->nullable()->after('codigo')->constrained('fichas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ficha_id');
        });
        Schema::dropIfExists('ficha_instrumentos');
        Schema::dropIfExists('fichas');
        Schema::dropIfExists('instrumentos');

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 32)->default('tecnico')->after('email');
        });

        Schema::table('cajas', function (Blueprint $table) {
            $table->foreignId('personal_id')->nullable()->after('responsable_id')->constrained('personal')->nullOnDelete();
        });
        Schema::table('caja_anotaciones', function (Blueprint $table) {
            $table->foreignId('personal_id')->nullable()->after('user_id')->constrained('personal')->nullOnDelete();
        });
        Schema::table('caja_retrocesos', function (Blueprint $table) {
            $table->foreignId('personal_id')->nullable()->after('user_id')->constrained('personal')->nullOnDelete();
        });
        Schema::table('personal', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('consumos_servicio', function (Blueprint $table) {
            $table->dropUnique(['servicio_id', 'periodo']);
            $table->dropConstrainedForeignId('servicio_id');
            $table->string('servicio')->after('id');
            $table->unique(['servicio', 'periodo']);
        });
        Schema::table('cajas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('servicio_id');
            $table->string('servicio')->after('codigo');
        });
        Schema::dropIfExists('servicios');

        Schema::dropIfExists('movimientos_inventario');
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->string('ubicacion')->nullable();
            $table->integer('stock')->default(0);
            $table->integer('minimo')->default(0);
            $table->timestamps();
        });
        Schema::create('movimientos_insumo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insumo_id')->constrained('insumos')->cascadeOnDelete();
            $table->string('tipo', 16);
            $table->unsignedInteger('cantidad');
            $table->string('observacion')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('reposiciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('elemento_inventario_id')->constrained('elementos_inventario')->cascadeOnDelete();
            $table->unsignedInteger('cantidad');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('elementos_inventario', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sala_id');
            $table->string('sala', 16)->after('codigo');
        });
        Schema::dropIfExists('salas');
    }

    private function codigoServicio(string $nombre): string
    {
        $texto = Str::ascii(Str::lower(trim($nombre)));
        foreach ([
            'pabellon' => 'pabell',
            'dental' => 'dental',
            'maternidad' => 'matern',
            'urgencia' => 'urgenc',
            'uci' => 'uci',
            'curaciones' => 'curacion',
            'otro' => 'otro',
        ] as $codigo => $pista) {
            if (str_contains($texto, $pista)) {
                return $codigo;
            }
        }

        return 'general';
    }
};
