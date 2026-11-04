<!-- MODAL DE CONSUMO EN OPERADOR -->
@if ($esOperador && $sala === 'lavado')
<div class="inv-modal" id="inv-modal-consumo" hidden>

    <div class="inv-modal-overlay" id="inv-modal-consumo-overlay"></div>

    <div class="inv-modal-contenido"
        role="dialog"
        aria-modal="true"
        aria-labelledby="inv-modal-consumo-titulo">

        <button
            type="button"
            class="inv-modal-cerrar"
            id="inv-cerrar-consumo"
            aria-label="Cerrar">
            ×
        </button>

        <!-- MENSAJE DE CONFIRMACIÓN -->
        <div
            id="inv-consumo-toast"
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
            <span id="inv-consumo-toast-texto"></span>
        </div>

        <form
            class="inv-consumo-modal"
            id="form-inv-consumo"
            action="{{ route('mockups.inventario.consumir') }}"
            method="post"
            novalidate>

            @csrf

            <input type="hidden" name="sala" value="lavado">

            <div class="panel-head">
                <p
                    class="inv-quien"
                    id="inv-consumo-fecha-hora"
                    data-usuario="{{ $usuario['nombre'] }}">
                    {{ $usuario['nombre'] }} · {{ $fecha }} · {{ $hora }}
                </p>
            </div>

            <div class="inv-modal-columnas">

                <!-- COLUMNA IZQUIERDA: REGISTRO -->
                <section class="inv-modal-registro">

                    <h2
                        class="inv-modal-seccion-titulo"
                        id="inv-modal-consumo-titulo">
                        Registrar consumo
                    </h2>

                    <div class="inv-modal-elemento-bloque">

                        <p class="field-label">Elemento</p>

                        <p
                            class="inv-modal-elemento"
                            id="inv-consumo-nombre">
                            Selecciona un insumo de la tabla
                        </p>

                        <p
                            class="inv-modal-stock"
                            id="inv-consumo-stock">
                        </p>

                        <input
                            type="hidden"
                            id="inv-consumo-elemento"
                            name="elemento"
                            required>
                    </div>

                    <div class="inv-modal-cantidad">

                        <label class="field-label" for="inv-consumo-cantidad">
                            Cantidad que sale
                        </label>

                        <div class="inv-cantidad-campo">
                            <label class="field">
                                <input
                                    id="inv-consumo-cantidad"
                                    name="cantidad"
                                    type="number"
                                    min="1"
                                    max="9999"
                                    step="1"
                                    required
                                    placeholder="Unidades"
                                    aria-describedby="inv-consumo-cantidad-error">
                            </label>

                            <p
                                class="inv-cantidad-error"
                                id="inv-consumo-cantidad-error"
                                hidden>
                                Ingresa una cantidad válida.
                            </p>
                        </div>

                    </div>

                    <p class="recv-help" id="inv-consumo-queda" hidden></p>

                    <!-- CAMPO OPCIONAL OBSERVACIÓN -->
                    <div class="inv-modal-observacion">

                        <div class="inv-observacion-encabezado">
                            <label class="field-label" for="inv-consumo-observacion">
                                Observación
                            </label>
                        </div>

                        <p class="inv-observacion-ayuda">
                            Agrega un detalle sobre el consumo realizado, si es necesario.
                        </p>

                        <textarea
                            id="inv-consumo-observacion"
                            name="observacion"
                            rows="3"
                            maxlength="255"
                            placeholder="Ej: Se utilizaron 3 unidades durante el turno"></textarea>

                        <div class="inv-observacion-pie">
                            <span id="inv-consumo-contador">0/255</span>
                        </div>

                    </div>

                    <div class="inv-modal-acciones">

                        <button
                            type="submit"
                            class="btn btn-primary inv-btn-registrar"
                            id="inv-registrar-consumo">
                            Registrar
                        </button>

                    </div>

                </section>

                <!-- COLUMNA DERECHA: HISTORIAL -->
                <section class="inv-modal-columna-historial">

                    <h2 class="inv-modal-seccion-titulo">
                        Historial de consumos
                    </h2>

                    <p id="inv-consumo-historial-cargando" class="recv-help" hidden>
                        Cargando historial...
                    </p>

                    <p id="inv-consumo-historial-vacio" class="recv-help" hidden>
                        Este insumo todavía no tiene consumos registrados.
                    </p>

                    <p id="inv-consumo-historial-error" class="recv-help" hidden>
                        No fue posible cargar el historial.
                    </p>

                    <ol class="act-lista" id="inv-consumo-historial-lista"></ol>

                </section>

            </div>

        </form>
    </div>
</div>
@endif