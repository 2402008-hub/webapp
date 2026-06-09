// Menú móvil
function toggleMenu() {
  const nav = document.getElementById('navMobile');
  nav.classList.toggle('open');
}

// Navbar transparente al hacer scroll
const navbar = document.querySelector('.navbar');
window.addEventListener('scroll', () => {
  if (window.scrollY > 60) {
    navbar.style.background = 'rgba(13, 31, 60, 0.98)';
  } else {
    navbar.style.background = 'rgba(13, 31, 60, 0.97)';
  }
});

// Animación de entrada en scroll (Intersection Observer)
const animEls = document.querySelectorAll('.mini-card, .horario-card, .valor-item, .info-item');

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = '1';
      entry.target.style.transform = 'translateY(0)';
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.15 });

animEls.forEach(el => {
  el.style.opacity = '0';
  el.style.transform = 'translateY(20px)';
  el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
  observer.observe(el);
});

// Año actual en footer
const footerCopy = document.querySelector('.footer-copy');
if (footerCopy) {
  footerCopy.textContent = footerCopy.textContent.replace('2025', new Date().getFullYear());
}
