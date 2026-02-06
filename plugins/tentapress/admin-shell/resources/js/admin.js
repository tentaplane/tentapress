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

const dialogState = {
    active: null,
};

const buildDialog = ({ title, message, confirmText, cancelText }) => {
    const backdrop = document.createElement('div');
    backdrop.className = 'tp-dialog-backdrop';

    const card = document.createElement('div');
    card.className = 'tp-dialog-card';

    if (title) {
        const header = document.createElement('div');
        header.className = 'tp-dialog-title';
        header.textContent = title;
        card.appendChild(header);
    }

    const body = document.createElement('div');
    body.className = 'tp-dialog-message';
    body.textContent = message;
    card.appendChild(body);

    const actions = document.createElement('div');
    actions.className = 'tp-dialog-actions';

    const cancel = document.createElement('button');
    cancel.type = 'button';
    cancel.className = 'tp-button-secondary';
    cancel.textContent = cancelText;

    const confirm = document.createElement('button');
    confirm.type = 'button';
    confirm.className = 'tp-button-primary';
    confirm.textContent = confirmText;

    actions.appendChild(cancel);
    actions.appendChild(confirm);
    card.appendChild(actions);

    backdrop.appendChild(card);

    return { backdrop, cancel, confirm };
};

const openConfirmDialog = ({ message, title, confirmText, cancelText }) =>
    new Promise((resolve) => {
        if (dialogState.active) {
            dialogState.active(false);
        }

        const dialog = buildDialog({
            title,
            message,
            confirmText,
            cancelText,
        });

        const close = (result) => {
            dialog.backdrop.remove();
            dialogState.active = null;
            resolve(result);
        };

        dialogState.active = close;

        dialog.cancel.addEventListener('click', () => close(false));
        dialog.confirm.addEventListener('click', () => close(true));
        dialog.backdrop.addEventListener('click', (event) => {
            if (event.target === dialog.backdrop) {
                close(false);
            }
        });

        document.addEventListener(
            'keydown',
            (event) => {
                if (event.key === 'Escape') {
                    close(false);
                }
            },
            { once: true },
        );

        document.body.appendChild(dialog.backdrop);
        dialog.confirm.focus();
    });

window.tpConfirm = (message, options = {}) =>
    openConfirmDialog({
        message: message || 'Are you sure?',
        title: options.title || 'Please confirm',
        confirmText: options.confirmText || 'Confirm',
        cancelText: options.cancelText || 'Cancel',
    });

const confirmSubmitHandler = (event) => {
    const form = event.target;
    if (!form || !(form instanceof HTMLFormElement)) {
        return;
    }

    const message = form.dataset.confirmMessage || form.dataset.confirm;
    if (!message) {
        return;
    }

    if (form.dataset.confirming === 'true') {
        form.dataset.confirming = '';
        return;
    }

    event.preventDefault();

    window.tpConfirm(message, {
        confirmText: form.dataset.confirmAction || 'Confirm',
    }).then((ok) => {
        if (!ok) {
            return;
        }

        form.dataset.confirming = 'true';
        form.requestSubmit();
    });
};

const confirmClickHandler = (event) => {
    const trigger = event.target.closest('[data-confirm]');
    if (!trigger) {
        return;
    }

    if (trigger.closest('form')) {
        return;
    }

    const message = trigger.dataset.confirmMessage || trigger.dataset.confirm;
    if (!message) {
        return;
    }

    event.preventDefault();

    window.tpConfirm(message, {
        confirmText: trigger.dataset.confirmAction || 'Confirm',
    }).then((ok) => {
        if (!ok) {
            return;
        }

        if (trigger.tagName === 'A') {
            window.location.href = trigger.href;
        }
    });
};

document.addEventListener('submit', confirmSubmitHandler, true);
document.addEventListener('click', confirmClickHandler);
