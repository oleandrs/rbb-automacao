const siteHeader = document.querySelector('.site-header');
const menuToggle = document.querySelector('.menu-toggle');
const mainNav = document.querySelector('.main-nav');

const closeMenu = () => {
  if (!menuToggle || !mainNav) return;
  mainNav.classList.remove('is-open');
  menuToggle.classList.remove('is-active');
  menuToggle.setAttribute('aria-expanded', 'false');
  document.body.classList.remove('menu-open');
};

if (menuToggle && mainNav) {
  menuToggle.addEventListener('click', () => {
    const isOpen = mainNav.classList.toggle('is-open');
    menuToggle.classList.toggle('is-active', isOpen);
    menuToggle.setAttribute('aria-expanded', String(isOpen));
    document.body.classList.toggle('menu-open', isOpen);
  });

  mainNav.addEventListener('click', (event) => {
    if (window.innerWidth <= 860 && event.target.closest('a')) {
      closeMenu();
    }
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth > 860) closeMenu();
  });

  window.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && mainNav.classList.contains('is-open')) {
      closeMenu();
      menuToggle.focus();
    }
  });
}

if (siteHeader) {
  let lastScrollY = window.scrollY;

  window.addEventListener('scroll', () => {
    const currentScrollY = window.scrollY;
    const scrollingDown = currentScrollY > lastScrollY;
    const passedThreshold = currentScrollY > 120;

    siteHeader.classList.toggle('is-hidden', scrollingDown && passedThreshold && window.innerWidth > 860);
    lastScrollY = currentScrollY;
  }, { passive: true });
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

const sectionLinks = document.querySelectorAll('.main-nav a[href^="#"]');
const pageSections = [...sectionLinks]
  .map((link) => document.querySelector(link.getAttribute('href')))
  .filter(Boolean);

if (sectionLinks.length && pageSections.length && 'IntersectionObserver' in window) {
  const navMap = new Map([...sectionLinks].map((link) => [link.getAttribute('href'), link]));

  const sectionObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      const currentId = `#${entry.target.id}`;
      navMap.forEach((link) => link.classList.remove('is-active'));
      navMap.get(currentId)?.classList.add('is-active');
    });
  }, { threshold: 0.45 });

  pageSections.forEach((section) => sectionObserver.observe(section));
}

if (!document.querySelector('.whatsapp-float')) {
  const floatButton = document.createElement('a');
  floatButton.className = 'whatsapp-float';
  floatButton.href = 'https://wa.me/5521993860628';
  floatButton.target = '_blank';
  floatButton.rel = 'noopener';
  floatButton.setAttribute('aria-label', 'Abrir conversa no WhatsApp');
  floatButton.textContent = 'WhatsApp';
  document.body.appendChild(floatButton);
}

const contactForms = document.querySelectorAll('.contact-form');

const clearFieldError = (field) => {
  field.classList.remove('is-invalid');
  field.removeAttribute('aria-invalid');
  const container = field.closest('.field');
  const error = container?.querySelector('.field-error');
  if (error) error.remove();
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
      if (field.checkValidity()) clearFieldError(field);
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
      form.querySelector('.is-invalid')?.focus();
    }
  });
});

const statusCard = document.querySelector('[data-status-card]');

if (statusCard) {
  const badge = statusCard.querySelector('[data-status-badge]');
  const title = statusCard.querySelector('[data-status-title]');
  const message = statusCard.querySelector('[data-status-message]');
  const status = new URLSearchParams(window.location.search).get('status');

  if (status === 'ok') {
    badge.textContent = 'Envio confirmado';
    title.textContent = 'Recebemos sua solicitação.';
    message.textContent = 'Obrigado pelo contato. Nossa equipe analisará as informações e retornará pelo canal informado.';
  } else if (status === 'erro') {
    statusCard.classList.add('is-error');
    badge.textContent = 'Falha no envio';
    title.textContent = 'Não foi possível concluir o envio.';
    message.textContent = 'Tente novamente em alguns minutos ou entre em contato diretamente pelo WhatsApp ou e-mail.';
  }
}
