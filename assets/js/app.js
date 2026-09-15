function initPasswordToggles() {
  document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
    if (btn.dataset.passwordToggleBound === 'true') {
      return;
    }
    btn.dataset.passwordToggleBound = 'true';

    btn.addEventListener('click', (e) => {
      e.preventDefault();

      const inputId = btn.getAttribute('data-toggle-password');
      const input = inputId ? document.getElementById(inputId) : null;
      if (!input) {
        return;
      }

      const reveal = input.getAttribute('type') === 'password';
      const value = input.value;
      input.setAttribute('type', reveal ? 'text' : 'password');
      input.value = value;

      const icon = btn.querySelector('i');
      if (icon) {
        icon.classList.toggle('bi-eye', !reveal);
        icon.classList.toggle('bi-eye-slash', reveal);
      }

      const label = reveal ? 'Hide password' : 'Show password';
      btn.setAttribute('aria-label', label);
      btn.setAttribute('title', label);
      input.focus({ preventScroll: true });
    });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  initPasswordToggles();

  // Auto-dismiss alerts after 6s
  if (typeof bootstrap !== 'undefined') {
    document.querySelectorAll('.alert-dismissible').forEach((el) => {
      setTimeout(() => {
        const alert = bootstrap.Alert.getOrCreateInstance(el);
        alert.close();
      }, 6000);
    });
  }

  // Confirm destructive actions
  document.querySelectorAll('[data-confirm]').forEach((el) => {
    el.addEventListener('click', (e) => {
      const msg = el.getAttribute('data-confirm') || 'Are you sure?';
      if (!window.confirm(msg)) {
        e.preventDefault();
      }
    });
  });

  // Role-dependent registration fields
  const roleSelect = document.getElementById('role');
  if (roleSelect) {
    const sync = () => {
      const role = roleSelect.value;
      document.querySelectorAll('[data-role-field]').forEach((wrap) => {
        const roles = (wrap.getAttribute('data-role-field') || '').split(',');
        wrap.classList.toggle('d-none', !roles.includes(role));
      });
    };
    roleSelect.addEventListener('change', sync);
    sync();
  }
});

// Auth pages load this script at the end of <body>; run immediately too.
initPasswordToggles();
