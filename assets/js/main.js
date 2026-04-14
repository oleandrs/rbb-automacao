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

const whatsappLink = 'https://wa.me/5521993860628';

if (!document.querySelector('.whatsapp-float')) {
  const floatButton = document.createElement('a');
  floatButton.className = 'whatsapp-float';
  floatButton.href = whatsappLink;
  floatButton.target = '_blank';
  floatButton.rel = 'noopener';
  floatButton.setAttribute('aria-label', 'Abrir conversa no WhatsApp');
  floatButton.textContent = 'WA';
  document.body.appendChild(floatButton);
}

const contactForms = document.querySelectorAll('.contact-form');

const clearFieldError = (field) => {
  field.classList.remove('is-invalid');
  field.removeAttribute('aria-invalid');
  const container = field.closest('.field');
  if (!container) return;
  const error = container.querySelector('.field-error');
  if (error) {
    error.remove();
  }
};

const showFieldError = (field) => {
  field.classList.add('is-invalid');
  field.setAttribute('aria-invalid', 'true');
  const container = field.closest('.field');
  if (!container || container.querySelector('.field-error')) return;
  const error = document.createElement('small');
  error.className = 'field-error';
  error.textContent = 'Preencha este campo corretamente.';
  container.appendChild(error);
};

contactForms.forEach((form) => {
  const requiredFields = form.querySelectorAll('input[required], textarea[required], select[required]');

  requiredFields.forEach((field) => {
    field.addEventListener('input', () => {
      if (field.checkValidity()) {
        clearFieldError(field);
      }
    });
  });

  form.addEventListener('submit', (event) => {
    let hasError = false;

    requiredFields.forEach((field) => {
      clearFieldError(field);
      if (!field.checkValidity()) {
        showFieldError(field);
        hasError = true;
      }
    });

    if (hasError) {
      event.preventDefault();
      const firstInvalid = form.querySelector('.is-invalid');
      if (firstInvalid) firstInvalid.focus();
    }
  });
});
