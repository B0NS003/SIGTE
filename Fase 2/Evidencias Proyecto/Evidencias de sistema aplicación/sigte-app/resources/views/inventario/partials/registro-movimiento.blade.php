@if ($esOperador && in_array($sala, ['lavado', 'armado']))
<section
    class="panel inv-registro-vista"
    id="inv-vista-registro"
    aria-hidden="true"
    data-historial-url="{{ route('mockups.inventario.historial-consumos', ['sala' => '__SALA__', 'codigo' => '__CODIGO__']) }}">

    <div class="panel-head inv-registro-cabecera">
        <div>
            <p
                class="inv-quien"
                id="inv-registro-fecha-hora"
                data-usuario="{{ $usuario['nombre'] }}">
                {{ $usuario['nombre'] }} · {{ $fecha }} · {{ $hora }}
            </p>

            <h2>Registrar movimiento</h2>
        </div>

        <button type="button" class="inv-volver-inventario" id="inv-volver-inventario">
            Volver
        </button>
    </div>

    <div class="inv-registro-contenido">

        <div class="inv-registro-formulario">
            <div class="inv-registro-elemento">
                <span>Elemento seleccionado</span>
                <h3 id="inv-movimiento-nombre">Selecciona un elemento</h3>

                <div class="inv-registro-stock">
                    <div>
                        <span>Stock disponible</span>
                        <strong id="inv-movimiento-stock">0</strong>
                    </div>

                    <div>
                        <span>En proceso</span>
                        <strong id="inv-movimiento-proceso">0</strong>
                    </div>
                </div>
            </div>

            <form
                id="form-inv-movimiento"
                action="{{ route('mockups.inventario.movimiento') }}"
                method="post"
                novalidate>
                @csrf

                <input type="hidden" name="sala" value="{{ $sala }}">
                <input type="hidden" name="elemento" id="inv-movimiento-elemento">

                <div class="inv-campo-movimiento">
                    <label for="inv-movimiento-tipo" id="inv-movimiento-tipo-label">
                        Tipo de movimiento
                    </label>

                    <div class="inv-selector-personalizado" id="inv-selector-tipo">

                        <button
                            type="button"
                            class="inv-selector-boton"
                            id="inv-selector-tipo-boton"
                            aria-haspopup="listbox"
                            aria-expanded="false"
                            aria-labelledby="inv-movimiento-tipo-label inv-selector-tipo-valor">

                            <span id="inv-selector-tipo-valor">Selecciona una opción</span>

                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m7 10 5 5 5-5"></path>
                            </svg>
                        </button>

                        <div
                            class="inv-selector-opciones"
                            id="inv-selector-tipo-opciones"
                            role="listbox"
                            aria-labelledby="inv-movimiento-tipo-label"
                            hidden>

                            <button type="button" role="option" data-value="consumo" aria-selected="false">
                                Consumo
                            </button>

                            <button type="button" role="option" data-value="uso" aria-selected="false">
                                Uso
                            </button>

                            <button type="button" role="option" data-value="devolucion" aria-selected="false">
                                Devolución
                            </button>
                        </div>

                    </div>

                    <select name="tipo" id="inv-movimiento-tipo" data-sigte-lista="1" required hidden>
                        <option value="" selected disabled>Selecciona una opción</option>
                        <option value="consumo">Consumo</option>
                        <option value="uso">Uso</option>
                        <option value="devolucion">Devolución</option>
                    </select>
                </div>

                <div class="inv-campo-movimiento">
                    <label for="inv-movimiento-cantidad" id="inv-movimiento-cantidad-label">
                        Cantidad
                    </label>

                    <input
                        type="number"
                        name="cantidad"
                        id="inv-movimiento-cantidad"
                        min="1"
                        max="9999"
                        step="1"
                        placeholder="Ingresa una cantidad"
                        required
                        disabled>

                    <small id="inv-movimiento-ayuda">
                        Primero selecciona el tipo de movimiento.
                    </small>

                    <p id="inv-movimiento-error" role="alert" hidden></p>
                </div>

                <div class="inv-campo-movimiento">
                    <label for="inv-movimiento-observacion">Observación</label>

                    <textarea
                        name="observacion"
                        id="inv-movimiento-observacion"
                        maxlength="255"
                        rows="3"
                        placeholder="Ej.: Material utilizado durante un procedimiento."></textarea>

                    <span id="inv-movimiento-contador">0/255</span>
                </div>

                <button type="submit" id="inv-movimiento-guardar" disabled>
                    <p id="inv-movimiento-mensaje" role="status" hidden></p>
                    Registrar
                </button>
            </form>
        </div>

        <aside class="inv-registro-historial">
            <h3>Historial de movimientos</h3>

            <p id="inv-movimiento-historial-cargando" hidden>
                Cargando historial...
            </p>

            <p id="inv-movimiento-historial-vacio" hidden>
                Todavía no hay movimientos registrados.
            </p>

            <p id="inv-movimiento-historial-error" hidden>
                No se pudo cargar el historial.
            </p>

            <div id="inv-movimiento-historial-lista"></div>
        </aside>

    </div>
</section>
@endif