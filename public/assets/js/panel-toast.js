(function () {
    const icons = {
        success: 'check',
        info: 'info-circle',
        warning: 'alert-triangle',
        danger: 'x',
    };

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function ensureStack() {
        let stack = document.getElementById('dokan-toast-stack');

        if (!stack) {
            stack = document.createElement('div');
            stack.id = 'dokan-toast-stack';
            stack.className = 'dokan-toast-stack';
            stack.setAttribute('aria-live', 'polite');
            document.body.appendChild(stack);
        }

        return stack;
    }

    function showToast(type, message, options) {
        const opts = options || {};
        const stack = ensureStack();
        const toastType = icons[type] ? type : 'info';
        const toast = document.createElement('div');
        const bodyContent = opts.html ? message : escapeHtml(message).replace(/\n/g, '<br>');

        toast.className = 'dokan-toast dokan-toast-' + toastType;
        toast.innerHTML =
            '<div class="dokan-toast-icon">' + (window.uiIcon ? window.uiIcon(icons[toastType]) : '') + '</div>' +
            '<div class="dokan-toast-body">' + bodyContent + '</div>' +
            '<button type="button" class="dokan-toast-close" aria-label="بستن">' + (window.uiIcon ? window.uiIcon('x') : '') + '</button>';

        toast.querySelector('.dokan-toast-close').addEventListener('click', function () {
            dismissToast(toast);
        });

        stack.appendChild(toast);
        requestAnimationFrame(function () {
            toast.classList.add('is-visible');
        });

        const duration = opts.duration || (toastType === 'success' ? 4200 : 7600);
        window.setTimeout(function () {
            dismissToast(toast);
        }, duration);

        return toast;
    }

    function dismissToast(toast) {
        if (!toast || toast.classList.contains('is-leaving')) {
            return;
        }

        toast.classList.add('is-leaving');
        window.setTimeout(function () {
            toast.remove();
        }, 220);
    }

    window.DokanToast = {
        show: showToast,
        success: function (message, options) {
            return showToast('success', message, options);
        },
        info: function (message, options) {
            return showToast('info', message, options);
        },
        warning: function (message, options) {
            return showToast('warning', message, options);
        },
        error: function (message, options) {
            return showToast('danger', message, options);
        },
    };

    function flushQueue() {
        if (!Array.isArray(window.__dokanToastQueue) || !window.__dokanToastQueue.length) {
            return;
        }

        const queue = window.__dokanToastQueue.splice(0);
        queue.forEach(function (item) {
            if (!item || !item.message) {
                return;
            }

            showToast(item.type || 'info', item.message, item.options || {});
        });
    }

    window.DokanFlushToasts = flushQueue;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', flushQueue);
    } else {
        flushQueue();
    }
})();
