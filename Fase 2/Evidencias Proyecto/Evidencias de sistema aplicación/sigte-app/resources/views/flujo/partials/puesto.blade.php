@if (auth()->user()?->esCuentaDelPuesto())
    <section class="puesto-bar panel" aria-label="Quién está en el puesto">
        <form method="post" action="{{ route('mockups.puesto') }}">
            @csrf
            <div class="puesto-texto">
                <p class="eyebrow">Quién está en el puesto</p>
                <p class="puesto-actual">{{ $puesto?->name ?? 'Nadie elegida' }}</p>
            </div>
            <label class="field">
                <span class="sr-only">Cuentas de operadora</span>
                <select name="user_id" required data-sigte-buscar="nombre">
                    <option value="">Elige la cuenta</option>
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
                        <option value="{{ $cuenta->id }}" @selected($puesto?->id === $cuenta->id)>{{ $cuenta->name }}</option>
                    @endforeach
                </select>
            </label>
            <button class="btn btn-dark" type="submit">Usar este nombre</button>
            <p class="puesto-turno">Prototipo: el turno de ejemplo de ahora es {{ \App\Models\Personal::TURNOS[$turnoVigente]['etiqueta'] }} ({{ \App\Models\Personal::TURNOS[$turnoVigente]['horario'] }}). Esos nombres quedan primero.</p>
        </form>
        @error('user_id')
            <p class="sigte-error" role="alert">{{ $message }}</p>
        @enderror
        @error('puesto')
            <p class="sigte-error" role="alert">{{ $message }}</p>
        @enderror
    </section>
@elseif (auth()->user()?->isOperador())
    <section class="puesto-bar panel" aria-label="Quién está registrando">
        <p class="eyebrow">Quién está registrando</p>
        <p class="puesto-actual">{{ auth()->user()->name }}</p>
    </section>
@endif
