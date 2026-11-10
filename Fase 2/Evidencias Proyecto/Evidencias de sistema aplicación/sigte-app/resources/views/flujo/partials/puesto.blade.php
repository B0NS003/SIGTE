@php
    $turnoVigente = $turnoVigente ?? \App\Models\Personal::turnoVigente();
    $turnoEtiqueta = \App\Models\Personal::TURNOS[$turnoVigente]['etiqueta'] ?? '';
    $turnoHorario = \App\Models\Personal::TURNOS[$turnoVigente]['horario'] ?? '';
@endphp
@if (auth()->user()?->esCuentaDelPuesto())
    <section class="puesto-card {{ $puesto ? 'is-ok' : 'is-empty' }}" aria-label="Quién está en el puesto">
        <form method="post" action="{{ route('mockups.puesto') }}">
            @csrf
            <div class="puesto-card-main">
                <span class="puesto-avatar" aria-hidden="true">
                    {{ $puesto ? strtoupper(mb_substr($puesto->name, 0, 1)) : '?' }}
                </span>
                <div class="puesto-card-texto">
                    <p class="puesto-kicker">
                        {{ $puesto ? 'En el puesto' : 'Puesto sin operadora' }}
                        <span aria-hidden="true">·</span>
                        Turno {{ mb_strtolower($turnoEtiqueta) }}, {{ $turnoHorario }}
                    </p>
                    @if ($puesto)
                        <p class="puesto-nombre">{{ $puesto->name }}</p>
                        <p class="puesto-meta">Lo que se anote o se pase en este computador queda a su nombre.</p>
                    @else
                        <p class="puesto-nombre">¿Quién está en el puesto?</p>
                        <p class="puesto-meta">Elige tu cuenta antes de anotar o pasar cajas.</p>
                    @endif
                </div>
                <label class="puesto-cambiar {{ $puesto ? '' : 'is-principal' }}">
                    <span class="sr-only">{{ $puesto ? 'Cambiar la operadora del puesto' : 'Elegir la operadora del puesto' }}</span>
                    <select
                        name="user_id"
                        required
                        data-sigte-buscar="nombre"
                        onchange="if (this.value) this.form.requestSubmit()"
                    >
                        <option value="">{{ $puesto ? 'Cambiar operadora' : 'Elegir operadora' }}</option>
                        @php $marcoTurno = false; $marcoResto = false; @endphp
                        @foreach ($nomina as $cuenta)
                            @if (! $marcoTurno && in_array($cuenta->id, $idsDelTurno ?? [], true))
                                <option value="sep-ahora" disabled>— En el turno de ahora —</option>
                                @php $marcoTurno = true; @endphp
                            @endif
                            @if ($marcoTurno && ! $marcoResto && ! in_array($cuenta->id, $idsDelTurno ?? [], true))
                                <option value="sep-resto" disabled>— Las demás cuentas —</option>
                                @php $marcoResto = true; @endphp
                            @endif
                            <option value="{{ $cuenta->id }}" @disabled($puesto?->id === $cuenta->id)>{{ $cuenta->name }}{{ $puesto?->id === $cuenta->id ? ' (en el puesto)' : '' }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </form>
        @error('user_id')
            <p class="sigte-error" role="alert">{{ $message }}</p>
        @enderror
        @error('puesto')
            <p class="sigte-error" role="alert">{{ $message }}</p>
        @enderror
    </section>
@elseif (auth()->user()?->isOperador())
    <section class="puesto-card is-ok" aria-label="Quién está registrando">
        <div class="puesto-card-main">
            <span class="puesto-avatar" aria-hidden="true">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <div class="puesto-card-texto">
                <p class="puesto-kicker">Operadora</p>
                <p class="puesto-nombre">{{ auth()->user()->name }}</p>
                <p class="puesto-meta">Lo que anotes o pases queda a tu nombre.</p>
            </div>
        </div>
    </section>
@endif
