import './bootstrap';

const nav = document.querySelector('#mainNav');
const backToTop = document.querySelector('.back-to-top');
const navLinks = document.querySelectorAll('.nav-link');
const sections = document.querySelectorAll('main section[id]');

const updateNavigation = () => {
    const isScrolled = window.scrollY > 24;

    nav?.classList.toggle('nav-scrolled', isScrolled);
    backToTop?.classList.toggle('show', window.scrollY > 480);

    let currentSection = 'beranda';
    sections.forEach((section) => {
        if (window.scrollY >= section.offsetTop - 130) {
            currentSection = section.id;
        }
    });

    navLinks.forEach((link) => {
        link.classList.toggle('active', link.getAttribute('href') === `#${currentSection}`);
    });
};

window.addEventListener('scroll', updateNavigation, { passive: true });
updateNavigation();

backToTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

const observer = new IntersectionObserver((entries, revealObserver) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            entry.target.classList.add('revealed');
            revealObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.12 });

document.querySelectorAll('[data-reveal]').forEach((element) => observer.observe(element));
