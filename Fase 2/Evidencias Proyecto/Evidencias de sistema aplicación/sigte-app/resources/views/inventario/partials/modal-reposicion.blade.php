<!-- MODAL PARA ACTUALIZAR EL ELEMENTO -->
@if ($puedeReponer)
<div class="inv-modal" id="inv-modal-reposicion" hidden>

    <div class="inv-modal-overlay" id="inv-modal-overlay"></div>

    <!-- MENSAJE VERDE DE CONFIRMACIÓN -->
    <div
        id="inv-toast-confirmacion"
        class="inv-toast-confirmacion"
        role="status"
        aria-live="polite"
        hidden>
        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path
                d="M5.5 12.5 10 17l8.5-9"
                fill="none"
                stroke="currentColor"
                stroke-width="1.75"
                stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>

        <span id="inv-toast-confirmacion-texto"></span>
    </div>

    <div class="inv-modal-contenido"
        role="dialog"
        aria-modal="true"
        aria-labelledby="inv-modal-titulo">

        <button
            type="button"
            class="inv-modal-cerrar"
            id="inv-cerrar-reposicion"
            aria-label="Cerrar">
            ×
        </button>

        <form
            class="inv-reposicion-modal"
            action="{{ route('mockups.inventario.reponer') }}"
            method="post"
            novalidate>
            @csrf

            <input type="hidden" name="sala" value="{{ $sala }}">

            <div class="panel-head">
                <p
                    class="inv-quien"
                    id="inv-modal-fecha-hora"
                    data-usuario="{{ $usuario['nombre'] }}">
                    {{ $usuario['nombre'] }} · {{ $fecha }} · {{ $hora }}
                </p>
            </div>

            <div class="inv-modal-columnas">

                <!-- COLUMNA IZQUIERDA: REGISTRO -->
                <section class="inv-modal-registro">

                    <h2 class="inv-modal-seccion-titulo">
                        Registrar reposición
                    </h2>

                    <div class="inv-modal-elemento-bloque">

                        <label class="field-label" for="elemento">
                            Elemento
                        </label>

                        <p class="inv-modal-elemento" id="inv-elemento-nombre">
                            Selecciona un elemento de la tabla
                        </p>

                        <p class="inv-modal-stock" id="inv-modal-stock"></p>

                        <input
                            type="hidden"
                            id="elemento"
                            name="elemento"
                            required>
                    </div>

                    <div class="inv-modal-cantidad">

                        <label class="field-label" for="cantidad">
                            Cantidad que entra
                        </label>

                        <div class="inv-cantidad-campo">
                            <label class="field">
                                <input
                                    id="cantidad"
                                    name="cantidad"
                                    type="number"
                                    min="1"
                                    max="9999"
                                    step="1"
                                    required
                                    placeholder="Unidades"
                                    aria-describedby="inv-cantidad-error">
                            </label>

                            <p
                                class="inv-cantidad-error"
                                id="inv-cantidad-error"
                                hidden>
                                Ingresa una cantidad válida.
                            </p>
                        </div>

                    </div>

                    <p class="recv-help" id="inv-queda" hidden></p>

                    <button
                        class="btn btn-primary inv-btn-registrar"
                        id="inv-anotar"
                        type="submit">
                        Registrar
                    </button>

                </section>

                <!-- COLUMNA DERECHA: HISTORIAL -->
                <section class="inv-modal-columna-historial">

                    <h2 class="inv-modal-seccion-titulo">
                        Historial del elemento
                    </h2>

                    <p id="inv-historial-cargando" class="recv-help" hidden>
                        Cargando historial...
                    </p>

                    <p id="inv-historial-vacio" class="recv-help" hidden>
                        Este elemento todavía no tiene reposiciones.
                    </p>

                    <p id="inv-historial-error" class="recv-help" hidden>
                        No fue posible cargar el historial.
                    </p>

                    <ol class="act-lista" id="inv-historial-lista"></ol>

                </section>

            </div>

        </form>

    </div>
</div>
@endif