document.addEventListener("DOMContentLoaded", () => {
    const botonMenu = document.querySelector(".boton-menu");
    const menu = document.querySelector(".menu-principal");

    if (botonMenu && menu) {
        botonMenu.addEventListener("click", () => {
            const abierto = menu.classList.toggle("menu-abierto");
            botonMenu.setAttribute("aria-expanded", String(abierto));
        });

        menu.querySelectorAll("a").forEach((enlace) => {
            enlace.addEventListener("click", () => {
                menu.classList.remove("menu-abierto");
                botonMenu.setAttribute("aria-expanded", "false");
            });
        });
    }

    document.querySelectorAll(".formulario-eliminar").forEach((formulario) => {
        formulario.addEventListener("submit", (evento) => {
            const mensaje = formulario.dataset.confirmacion
                || "¿Confirma que desea eliminar este registro?";

            if (!window.confirm(mensaje)) {
                evento.preventDefault();
            }
        });
    });
});

