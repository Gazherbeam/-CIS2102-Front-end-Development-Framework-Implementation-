document.addEventListener('DOMContentLoaded', () => {
  const body = document.querySelector('#body[data-char-count]') || document.querySelector('[data-review-form] textarea[name="body"]');
  const counter = document.querySelector('[data-char-count]');
  if (body && counter) {
    const updateCount = () => { counter.textContent = `${body.value.length} / 500`; };
    body.addEventListener('input', updateCount);
    updateCount();
  }

  const stars = [...document.querySelectorAll('.rating-picker label')];
  const paintStars = (rating) => stars.forEach((star, index) => star.classList.toggle('selected', index < rating));
  stars.forEach((star, index) => {
    const radio = star.querySelector('input');
    star.addEventListener('mouseenter', () => stars.forEach((item, itemIndex) => item.classList.toggle('hovered', itemIndex <= index)));
    star.addEventListener('mouseleave', () => stars.forEach((item) => item.classList.remove('hovered')));
    radio.addEventListener('change', () => paintStars(index + 1));
  });

  document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
      const input = toggle.closest('.password-wrap').querySelector('input');
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      toggle.textContent = show ? 'Hide' : 'Show';
      toggle.setAttribute('aria-label', `${show ? 'Hide' : 'Show'} password`);
    });
  });

  document.querySelectorAll('[data-review-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      const file = form.querySelector('input[type="file"]')?.files?.[0];
      if (file && (file.size > 5 * 1024 * 1024 || !['image/jpeg', 'image/png'].includes(file.type))) {
        event.preventDefault();
        window.alert('Please choose a JPG or PNG image under 5 MB.');
      }
    });
  });
});
