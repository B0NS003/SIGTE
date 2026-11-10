<section class="panel list inv-cajas-lavado">

    <div class="panel-head">
        <div>
            <h2 class="inv-titulo inv-titulo-cajas">
                <svg
                    class="sigte-icon"
                    xmlns="http://www.w3.org/2000/svg"
                    width="21"
                    height="21"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true">
                    <path d="m12 3 8.5 4.5v9L12 21l-8.5-4.5v-9L12 3Z" />
                    <path d="m12 12 8.5-4.5M12 12v9M12 12 3.5 7.5" />
                    <path d="m7.75 5.25 8.5 4.5" />
                </svg>
                <span>Cajas y sets en Lavado</span>
            </h2>
            <p class="main-sub">
                Consulta las cajas físicas que se encuentran en esta etapa del proceso.
            </p>
        </div>

        <ul class="inv-resumen">
            <li>
                <strong>{{ $cajasLavado->total() }}</strong>
                cajas registradas
            </li>
        </ul>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Ficha / Set</th>
                    <th>Servicio</th>
                    <th>Ubicación</th>
                    <th>Estado</th>
                    <th>Composición</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($cajasLavado as $caja)
                <tr>
                    <td>
                        <strong>{{ $caja->codigo }}</strong>
                    </td>

                    <td>
                        @if ($caja->ficha)
                        <strong>{{ $caja->ficha->nombre }}</strong>
                        <br>
                        <small>{{ $caja->ficha->codigo }}</small>
                        @else
                        <span class="text-muted">Sin ficha vinculada</span>
                        @endif
                    </td>

                    <td>
                        {{ $caja->servicioClinico?->nombre ?? 'Sin servicio' }}
                    </td>

                    <td>
                        {{ $caja->ubicacion ?? 'Sin ubicación' }}
                    </td>

                    <td>
                        {{ $caja->estado ?? 'Sin estado registrado' }}
                    </td>

                    <td>
                        @if ($caja->ficha && $caja->ficha->items->isNotEmpty())
                        <ul class="inv-caja-composicion">
                            @foreach ($caja->ficha->items as $item)
                            <li>
                                {{ $item->instrumento?->nombre ?? 'Instrumento sin nombre' }}
                                <strong>× {{ $item->cantidad }}</strong>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <span class="text-muted">
                            Composición no disponible
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">
                        No hay cajas registradas en la etapa de Lavado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- PAGINACIÓN DE CAJAS -->
    <div class="inv-paginacion">

        <form class="inv-paginacion-info" method="GET" action="{{ route('mockups.inventario') }}">
            <input type="hidden" name="sala" value="lavado">

            <label for="cajas-items-pagina">Elementos por página</label>

            <select
                id="cajas-items-pagina"
                name="cajas_per_page"
                data-sigte-lista="1"
                onchange="this.form.submit()">

                @foreach ([5, 10, 20, 50] as $cantidad)
                <option
                    value="{{ $cantidad }}"
                    {{ (int) request('cajas_per_page', 10) === $cantidad ? 'selected' : '' }}>
                    {{ $cantidad }}
                </option>
                @endforeach

            </select>
        </form>

        <div class="inv-paginacion-controles">

            <span class="inv-rango">
                {{ $cajasLavado->firstItem() ?? 0 }}–{{ $cajasLavado->lastItem() ?? 0 }}
                de {{ $cajasLavado->total() }}
            </span>

            <!-- PÁGINA ANTERIOR -->
            @if ($cajasLavado->onFirstPage())
            <span class="inv-pagina-btn" aria-disabled="true">‹</span>
            @else
            <a
                class="inv-pagina-btn"
                href="{{ $cajasLavado->previousPageUrl() }}"
                aria-label="Página anterior">
                ‹
            </a>
            @endif

            <!-- NÚMEROS DE PÁGINA -->
            @if ($cajasLavado->lastPage() > 1)
            @foreach ($cajasLavado->getUrlRange(1, $cajasLavado->lastPage()) as $pagina => $url)
            <a
                class="inv-pagina-numero {{ $cajasLavado->currentPage() === $pagina ? 'is-active' : '' }}"
                href="{{ $url }}"
                @if ($cajasLavado->currentPage() === $pagina) aria-current="page" @endif>
                {{ $pagina }}
            </a>
            @endforeach
            @endif

            <!-- PÁGINA SIGUIENTE -->
            @if ($cajasLavado->hasMorePages())
            <a
                class="inv-pagina-btn"
                href="{{ $cajasLavado->nextPageUrl() }}"
                aria-label="Página siguiente">
                ›
            </a>
            @else
            <span class="inv-pagina-btn" aria-disabled="true">›</span>
            @endif

        </div>

    </div>

</section>