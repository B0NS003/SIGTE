<!-- SECCIÓN DE LOS ELEMENTOS PRIORITARIOS A REPONER -->
<section class="panel inv-mira">
    <div class="panel-head">
        <div>
            <h2 class="inv-titulo">
                <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 4.5 20.5 19.5h-17L12 4.5z"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.75"
                        stroke-linejoin="round" />
                    <path d="M12 10v4.2"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.75"
                        stroke-linecap="round" />
                    <path d="M12 17.2h.01"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.25"
                        stroke-linecap="round" />
                </svg>
                Prioridades de reposición
            </h2>
        </div>
    </div>

    @if ($atencion === [])
    <p class="recv-help">Ningún elemento está bajo el mínimo.</p>
    @else

    <ul class="inv-atencion" id="inv-prioridades-lista">
        @foreach ($atencion as $item)
        <li class="inv-prioridad inv-prioridad-{{ $item['estado'] }}">

            <div class="inv-prioridad-contenido">

                <div class="inv-prioridad-estado">
                    @if ($item['estado'] === 'critico')
                    <span class="pill pill-danger">Crítico</span>
                    @else
                    <span class="pill pill-warn">Bajo mínimo</span>
                    @endif
                </div>

                <strong class="inv-prioridad-nombre">
                    {{ $item['nombre'] }}
                </strong>

                <span class="inv-prioridad-detalle">
                    {{ $item['ubicacion'] }}
                    · Stock: {{ $item['stock'] }}
                    · Mínimo: {{ $item['minimo'] }}
                </span>
            </div>

            <div class="inv-prioridad-lateral">
                <span class="badge badge-sala badge-{{ $item['sala'] }}">
                    {{ $salas[$item['sala']] ?? 'Sala' }}
                </span>

                @if ($puedeReponer)
                <a class="inv-prioridad-reponer"
                    href="{{ route('mockups.inventario', ['sala' => $item['sala'], 'elemento' => $item['codigo']]) }}">
                    Reponer <span aria-hidden="true"></span>
                </a>
                @endif
            </div>

        </li>
        @endforeach
    </ul>

    <!-- PAGINACIÓN DE PRIORIDADES -->
    <div class="inv-paginacion" id="inv-prioridades-paginacion">

        <div class="inv-paginacion-info">
            <label for="inv-prioridades-items-pagina">
                Elementos por página
            </label>

            <select id="inv-prioridades-items-pagina">
                <option value="5" selected>5</option>
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
        </div>

        <div class="inv-paginacion-controles">
            <span class="inv-rango" id="inv-prioridades-rango">
                0–0 de 0
            </span>

            <button
                type="button"
                class="inv-pagina-btn"
                id="inv-prioridades-anterior"
                aria-label="Página anterior">
                ‹
            </button>

            <div
                class="inv-paginas"
                id="inv-prioridades-paginas">
            </div>

            <button
                type="button"
                class="inv-pagina-btn"
                id="inv-prioridades-siguiente"
                aria-label="Página siguiente">
                ›
            </button>
        </div>
    </div>
    @endif
</section>