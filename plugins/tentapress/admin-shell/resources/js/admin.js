import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

const toastRoot = document.getElementById('tp-toast-root');

if (toastRoot) {
    const raw = toastRoot.getAttribute('data-toasts');
    let toasts = [];

    try {
        toasts = raw ? JSON.parse(raw) : [];
    } catch {
        toasts = [];
    }

    toastRoot.classList.add('tp-toast-root');

    const addToast = (toast) => {
        const node = document.createElement('div');
        node.className = `tp-toast tp-toast-${toast.type || 'info'}`;

        const message = document.createElement('div');
        message.className = 'tp-toast-message';
        message.textContent = toast.message || '';

        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'tp-toast-close';
        close.setAttribute('aria-label', 'Dismiss');
        close.textContent = '×';
        close.addEventListener('click', () => node.remove());

        node.appendChild(message);
        node.appendChild(close);
        toastRoot.appendChild(node);

        setTimeout(() => {
            node.classList.add('tp-toast-hide');
            setTimeout(() => node.remove(), 250);
        }, 5000);
    };

    toasts.forEach(addToast);
}
