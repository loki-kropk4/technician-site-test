import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/**
 * Generic password visibility toggle. Any button with
 * data-password-toggle="#target-input-id" flips that input between
 * type="password" and type="text" and swaps its icon.
 */
document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-password-toggle]');
    if (!toggle) return;

    const input = document.querySelector(toggle.getAttribute('data-password-toggle'));
    if (!input) return;

    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';

    const icon = toggle.querySelector('img');
    if (icon) {
        icon.src = isHidden ? toggle.dataset.iconVisible : toggle.dataset.iconHidden;
        icon.alt = isHidden ? 'Hide password' : 'Show password';
    }
});
