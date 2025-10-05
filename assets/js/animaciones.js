document.addEventListener("DOMContentLoaded", function () {
  // Inicializa AOS (librería de animaciones al hacer scroll)
  AOS.init({
    duration: 1000,
    easing: "ease-in-out",
    once: true,
  });

  // Efecto al cargar contenedor principal
  const container = document.querySelector(".container");
  if (container) {
    container.style.opacity = 0;
    setTimeout(() => {
      container.style.transition = "opacity 1s";
      container.style.opacity = 1;
    }, 300);
  }

  // Navbar cambia al hacer scroll
  const header = document.querySelector("header");
  if (header) {
    window.addEventListener("scroll", () => {
      if (window.scrollY > 50) {
        header.classList.add("navbar-scroll");
      } else {
        header.classList.remove("navbar-scroll");
      }
    });
  }

  // Botones con efecto click
  document.querySelectorAll(".btn").forEach((btn) => {
    btn.addEventListener("mousedown", () => (btn.style.transform = "scale(0.95)"));
    btn.addEventListener("mouseup", () => (btn.style.transform = "scale(1)"));
  });
});
