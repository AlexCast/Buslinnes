// ================================
// Preloader con retardo
// ================================
window.addEventListener("load", () => {
  const preloader = document.getElementById("preloader");
  setTimeout(() => {
    preloader.style.opacity = "0";
    setTimeout(() => {
      preloader.style.display = "none";
    }, 600);
  }, 1500); // 👈 espera 1.5s antes de desaparecer
});

// ================================
// Navbar móvil
// ================================
const menuBtn = document.getElementById("menu-btn");
const navMenu = document.querySelector(".nav-menu");

menuBtn.addEventListener("click", () => {
  navMenu.classList.toggle("active");
});
