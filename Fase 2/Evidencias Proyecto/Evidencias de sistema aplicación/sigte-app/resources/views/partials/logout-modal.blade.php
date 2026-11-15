<div
    class="logout-modal"
    id="modal-logout"
    role="dialog"
    aria-modal="true"
    aria-labelledby="logout-titulo"
    aria-describedby="logout-descripcion"
    hidden
>
    <div class="logout-overlay" data-logout-cerrar></div>

    <section class="logout-contenido panel" tabindex="-1">
        <div class="logout-icono" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M10 5.5H7.2A1.7 1.7 0 0 0 5.5 7.2v9.6A1.7 1.7 0 0 0 7.2 18.5H10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                <path d="M10.5 12H19" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                <path d="M15.8 8.5 19.5 12l-3.7 3.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>

        <h2 id="logout-titulo">Cerrar sesión</h2>

        <p id="logout-descripcion">
            ¿Deseas salir de tu cuenta?
        </p>

        <p class="logout-ayuda">
            Tendrás que iniciar sesión nuevamente para volver a acceder al sistema.
        </p>

        <div class="logout-acciones">
            <button type="button" class="btn btn-ghost logout-btn-cancelar" data-logout-cerrar>
                Cancelar
            </button>

            <form method="POST" action="{{ route('logout') }}" id="form-logout">
                @csrf
                <button type="submit" class="btn logout-btn-confirmar">
                    Confirmar
                </button>
            </form>
        </div>
    </section>
</div>
