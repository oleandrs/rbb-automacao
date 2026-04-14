const menuToggle = document.querySelector('.menu-toggle');
const headerBottom = document.querySelector('.header-bottom');
const navLinks = document.querySelectorAll('.main-nav a');

if (menuToggle && headerBottom) {
  menuToggle.addEventListener('click', () => {
    const isOpen = headerBottom.classList.toggle('is-open');
    menuToggle.classList.toggle('is-active', isOpen);
    menuToggle.setAttribute('aria-expanded', String(isOpen));
    document.body.classList.toggle('menu-open', isOpen);
  });

  navLinks.forEach((link) => {
    link.addEventListener('click', () => {
      if (window.innerWidth <= 860) {
        headerBottom.classList.remove('is-open');
        menuToggle.classList.remove('is-active');
        menuToggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('menu-open');
      }
    });
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth > 860) {
      headerBottom.classList.remove('is-open');
      menuToggle.classList.remove('is-active');
      menuToggle.setAttribute('aria-expanded', 'false');
      document.body.classList.remove('menu-open');
    }
  });
}

const revealItems = document.querySelectorAll('.reveal');

if ('IntersectionObserver' in window && revealItems.length) {
  const observer = new IntersectionObserver((entries, obs) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        obs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

  revealItems.forEach((item) => observer.observe(item));
} else {
  revealItems.forEach((item) => item.classList.add('is-visible'));
}

const forms = document.querySelectorAll('form[novalidate]');

forms.forEach((form) => {
  form.addEventListener('submit', (event) => {
    if (!form.checkValidity()) {
      event.preventDefault();
      form.reportValidity();
    }
  });
});

const thankYouSection = document.querySelector('.thank-you-section');

if (thankYouSection) {
  const status = new URLSearchParams(window.location.search).get('status');
  const badge = thankYouSection.querySelector('.maintenance-badge');
  const title = thankYouSection.querySelector('h1');
  const message = thankYouSection.querySelector('p');
  const primaryButton = thankYouSection.querySelector('.btn-primary');
  const secondaryButton = thankYouSection.querySelector('.btn-outline');

  if (status === 'erro') {
    if (badge) badge.textContent = 'Falha no envio';
    if (title) title.textContent = 'Nao foi possivel enviar sua mensagem';
    if (message) message.textContent = 'Tente novamente em alguns minutos ou fale conosco pelo WhatsApp.';
    if (primaryButton) {
      primaryButton.textContent = 'Tentar novamente';
      primaryButton.setAttribute('href', 'fale-conosco.html');
    }
    if (secondaryButton) {
      secondaryButton.textContent = 'Falar no WhatsApp';
      secondaryButton.setAttribute('href', 'https://wa.me/5521993860628');
      secondaryButton.setAttribute('target', '_blank');
      secondaryButton.setAttribute('rel', 'noopener noreferrer');
    }
  }
}
