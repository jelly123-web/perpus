import './bootstrap';
import './lucide-offline';
import { createIcons, icons } from 'lucide';

window.lucide = {
    ...(window.lucide || {}),
    icons,
    createIcons(options = {}) {
        return createIcons({
            icons,
            nameAttr: 'data-lucide',
            ...options,
        });
    },
};

function initializeLucideIcons() {
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeLucideIcons);
} else {
    initializeLucideIcons();
}

window.addEventListener('load', initializeLucideIcons);

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js').catch(() => {
            // Keep silent in UI; app should still work online even if registration fails.
        });
    });
}
