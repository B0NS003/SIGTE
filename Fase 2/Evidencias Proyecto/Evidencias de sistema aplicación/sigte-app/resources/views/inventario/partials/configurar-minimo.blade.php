<!-- VISTA PARA CONFIGURAR EL MÍNIMO -->
@if ($esEnfermera && in_array($sala, ['lavado', 'armado', 'esteril']))
<section
    class="panel inv-registro-vista inv-minimo-vista sala-{{ $sala }}"
    id="inv-vista-minimo"
    aria-hidden="true">

    <!-- MENSAJE DE CONFIRMACIÓN -->
    <div
        id="inv-toast-minimo"
        class="inv-toast-confirmacion"
        role="status"
        aria-live="polite"
        hidden>

        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path
                d="M5 12.5 9.5 17 19 7"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>

        <span id="inv-toast-minimo-texto"></span>
    </div>

    <div class="panel-head inv-registro-cabecera">
        <div>
            <p
                class="inv-quien"
                id="inv-minimo-fecha-hora"
                data-usuario="{{ $usuario['nombre'] }}">
            </p>

            <h2>Configurar Mínimo</h2>
        </div>

        <button
            type="button"
            class="inv-volver-inventario"
            id="inv-volver-minimo">
            Volver
        </button>
    </div>

    <div class="inv-registro-contenido">

        <div class="inv-registro-formulario">

            <div class="inv-registro-elemento">
                <h3 id="minimo-nombre">Selecciona un elemento</h3>

                <div class="inv-registro-stock">
                    <div>
                        <span>Stock actual</span>
                        <strong id="minimo-stock">0</strong>
                    </div>

                    <div>
                        <span>Stock mínimo requerido</span>
                        <strong id="minimo-actual">0</strong>
                    </div>
                </div>
            </div>

            <form
                id="form-configurar-minimo"
                action="{{ route('mockups.inventario.minimo') }}"
                method="POST"
                novalidate>

                @csrf

                <input type="hidden" name="sala" value="{{ $sala }}">
                <input type="hidden" name="codigo" id="minimo-codigo">

                <div class="inv-campo-movimiento">
                    <label for="minimo-nuevo">
                        Establecer stock mínimo
                    </label>

                    <input
                        type="number"
                        id="minimo-nuevo"
                        name="minimo"
                        min="0"
                        max="9999"
                        step="1"
                        placeholder="Ingresa las unidades"
                        required>

                    <p id="minimo-error" role="alert" hidden>
                        Este campo no puede quedar vacío
                    </p>
                </div>

                <button
                    class="btn btn-primary"
                    type="submit"
                    id="inv-guardar-minimo">
                    Guardar
                </button>

                <p id="minimo-mensaje" role="status" aria-live="polite" hidden></p>

            </form>
        </div>

    </div>
</section>
@endif