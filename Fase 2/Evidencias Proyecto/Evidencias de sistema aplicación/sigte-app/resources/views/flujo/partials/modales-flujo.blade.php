<div
    id="flujo-modales"
    data-accion="{{ $accion_modal }}"
    data-caja="{{ $caja_modal }}"
    data-listo="{{ session('ok') && $accion_modal === 'etapa' ? '1' : '0' }}"
    data-corregido="{{ session('ok') && $accion_modal === 'retroceso' ? '1' : '0' }}"
    data-sin-puesto="{{ $puesto ? '0' : '1' }}"
    data-anotar="{{ route('mockups.anotar') }}"
    data-entrega="{{ url('/operador/entrega') }}"
>
    <dialog id="modal-etapa" class="flujo-modal" aria-labelledby="etapa-titulo">
        <div class="flujo-modal-cuerpo">
            <div class="flujo-modal-top">
                <h2 id="etapa-titulo"></h2>
            </div>
            <p class="flujo-ruta" id="etapa-ruta">
                <span class="flujo-ruta-desde" id="etapa-ruta-desde"></span>
                <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="flujo-ruta-hacia" id="etapa-ruta-hacia"></span>
            </p>

            @unless ($puesto)
                <div class="flujo-sin-puesto" role="alert">
                    <p>Antes de anotar o pasar la caja, elige quién está en el puesto.</p>
                    <button class="btn btn-ghost" type="button" data-elegir-puesto>Elegir operadora</button>
                </div>
            @endunless

            <p class="auth-error" id="etapa-error" role="alert" @unless($errors->any() && $accion_modal === 'etapa') hidden @endunless>{{ $errors->first() }}</p>
            <p class="recv-help" id="etapa-vacio" hidden>Esa caja no está en el flujo.</p>

            <form id="etapa-pedir" action="{{ route('mockups.avanzar.guardar') }}" method="post">
                @csrf
                <input type="hidden" name="caja" id="etapa-caja-input" value="{{ $caja_modal }}">
                <input type="hidden" name="etapa_destino" id="etapa-destino" value="">
                @if ($busqueda !== '')
                    <input type="hidden" name="q" value="{{ $busqueda }}">
                @endif
                @if ($etapa_filtro !== null)
                    <input type="hidden" name="etapa" value="{{ $etapa_filtro }}">
                @endif

                <section class="flujo-paso" id="etapa-anotar" aria-labelledby="etapa-anotar-titulo">
                    <div class="flujo-paso-cabeza">
                        <p class="flujo-paso-titulo" id="etapa-anotar-titulo">Anotar</p>
                        <span class="flujo-opcional">Opcional</span>
                    </div>
                    <p class="flujo-paso-resumen" id="etapa-anotar-ayuda"></p>
                    <div class="flujo-chips" id="etapa-chips"></div>

                    <div id="etapa-detalle-bloque" hidden>
                        <label class="field-label" id="etapa-detalle-label" for="etapa-detalle"></label>
                        <div class="flujo-tiempo">
                            <input class="flujo-detalle" id="etapa-detalle" maxlength="120" autocomplete="off">
                            <button class="flujo-chip" id="etapa-detalle-guardar" type="button">Anotar</button>
                        </div>
                    </div>

                    <div id="etapa-tiempos"></div>
                    <p class="auth-error" id="etapa-anota-error" role="alert" hidden></p>

                    <details class="flujo-historial" id="etapa-historial" hidden>
                        <summary>Historial de la caja <span id="etapa-historial-total"></span></summary>
                        <ol class="act-lista" id="etapa-lista"></ol>
                    </details>

                    <div class="flujo-modal-acciones" id="etapa-acciones-anotar">
                        <button class="btn btn-ghost" type="button" data-cerrar>Cerrar</button>
                        <button class="btn btn-primary" type="button" id="etapa-ir-pasar">
                            <span id="etapa-ir-pasar-texto">Pasar de etapa</span>
                            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9.5 6.5 15 12l-5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </section>

                <section class="flujo-paso" id="etapa-resumen" aria-labelledby="etapa-resumen-titulo" hidden>
                    <p class="flujo-paso-titulo" id="etapa-resumen-titulo">Anotaciones en esta etapa</p>
                    <p class="flujo-paso-resumen" id="etapa-resumen-vacio" hidden>Todavía no hay anotaciones en esta etapa.</p>
                    <ul class="flujo-resumen-lista" id="etapa-resumen-lista"></ul>
                </section>

                <section class="flujo-pasar" id="etapa-pasar" aria-labelledby="etapa-pasar-titulo">
                    <p class="flujo-paso-titulo" id="etapa-pasar-titulo">Pasar de etapa</p>
                    @if ($puesto)
                        <p class="flujo-paso-resumen">Queda registrado a nombre de {{ $puesto->name }}, con fecha y hora.</p>
                    @endif
                    <p class="flujo-pendiente" id="etapa-pendiente" hidden></p>
                    <div class="flujo-modal-acciones">
                        <button class="btn btn-ghost" type="button" data-cerrar>Cerrar</button>
                        <button class="btn btn-primary" type="submit" id="etapa-enviar">
                            <span id="etapa-enviar-texto">Pasar de etapa</span>
                            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9.5 6.5 15 12l-5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <a class="btn btn-primary" id="etapa-entrega" href="{{ route('mockups.entrega') }}" hidden>
                            <span>Registrar la entrega</span>
                            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9.5 6.5 15 12l-5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                </section>
            </form>
        </div>
    </dialog>

    <dialog id="modal-retroceso" class="flujo-modal" aria-labelledby="retro-titulo">
        <div class="flujo-modal-cuerpo">
            <div class="flujo-modal-top">
                <h2 id="retro-titulo"></h2>
            </div>

            @unless ($puesto)
                <div class="flujo-sin-puesto" role="alert">
                    <p>Antes de corregir la caja, elige quién está en el puesto.</p>
                    <button class="btn btn-ghost" type="button" data-elegir-puesto>Elegir operadora</button>
                </div>
            @endunless

            <p class="auth-error" id="retro-error" role="alert" @unless($errors->any() && $accion_modal === 'retroceso') hidden @endunless>{{ $accion_modal === 'retroceso' ? $errors->first() : '' }}</p>

            <form id="retro-pedir" action="{{ route('mockups.retroceder') }}" method="post">
                @csrf
                <input type="hidden" name="caja" id="retro-caja" value="{{ $accion_modal === 'retroceso' ? $caja_modal : '' }}">
                @if ($busqueda !== '')
                    <input type="hidden" name="q" value="{{ $busqueda }}">
                @endif
                @if ($etapa_filtro !== null)
                    <input type="hidden" name="etapa" value="{{ $etapa_filtro }}">
                @endif

                <div class="flujo-paso">
                    <p class="flujo-paso-titulo" id="retro-movimiento"></p>
                    <p class="flujo-paso-resumen">@if ($puesto) Lo registra {{ $puesto->name }}. @endif Esta corrección queda con la fecha y la hora.</p>
                    <label class="field-label" for="retro-motivo">Motivo</label>
                    <textarea class="flujo-motivo" id="retro-motivo" name="motivo" maxlength="180" required placeholder="Qué pasó con esta caja">{{ $accion_modal === 'retroceso' ? old('motivo') : '' }}</textarea>
                    <div class="flujo-modal-acciones">
                        <button class="btn btn-ghost" type="button" data-cerrar>Cancelar</button>
                        <button class="btn btn-primary" type="submit" id="retro-enviar">
                            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M5.5 12.5 10 17l8.5-9" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span id="retro-enviar-texto">Confirmar corrección</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </dialog>

    <div class="flujo-aviso" id="flujo-aviso" role="status" hidden>
        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M5.5 12.5 10 17l8.5-9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <p id="flujo-aviso-texto">{{ session('ok') }}</p>
        <button type="button" class="flujo-aviso-cerrar" aria-label="Cerrar aviso">
            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M7 7l10 10M17 7 7 17" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
            </svg>
        </button>
    </div>
</div>
<script type="application/json" id="pasos-etapa">@json($pasos_etapa)</script>
<script type="application/json" id="anotaciones-caja">@json($anotaciones_por_caja)</script>
@once
    @push('scripts')
        <script src="{{ asset('js/modulos/flujo/modales.js') }}?v={{ @filemtime(public_path('js/modulos/flujo/modales.js')) }}"></script>
    @endpush
@endonce
