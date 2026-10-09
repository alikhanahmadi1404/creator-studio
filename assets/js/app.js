document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      const message = form.getAttribute('data-confirm') || 'ادامه می‌دهی؟';
      if (!window.confirm(message)) event.preventDefault();
    });
  });

  document.querySelectorAll('[data-autofocus]').forEach((element) => element.focus());
});
