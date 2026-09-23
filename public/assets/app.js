document.addEventListener('change', (event) => {
    if (event.target.matches('[data-product-select]')) {
        const selected = event.target.options[event.target.selectedIndex];
        const form = event.target.closest('form');
        const nameInput = form.querySelector('[name="product_name"]');
        const visibleNameInput = form.querySelector('[data-product-name-visible]');
        if (nameInput && selected.dataset.name) nameInput.value = selected.dataset.name;
        if (visibleNameInput && selected.dataset.name) visibleNameInput.value = selected.dataset.name;
        if (selected.dataset.rate && form.querySelector('[name="rate"]')) form.querySelector('[name="rate"]').value = selected.dataset.rate;
        if (selected.dataset.purity && form.querySelector('[name="melting"]')) form.querySelector('[name="melting"]').value = selected.dataset.purity;
    }

    if (event.target.matches('[data-transaction-type]')) {
        const params = new URLSearchParams(window.location.search);
        params.set('type', event.target.value);
        const vyapari = event.target.closest('form').querySelector('[name="vyapari_id"]');
        if (vyapari && vyapari.value) params.set('vyapari_id', vyapari.value);
        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }
});

document.addEventListener('input', (event) => {
    if (event.target.matches('[data-gross-weight]')) {
        const net = event.target.closest('form').querySelector('[data-net-weight]');
        if (net) net.value = event.target.value;
    }
});

// Sync auth token from URL into localStorage or across navigation in iframes
(function() {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const authFromUrl = urlParams.get('auth');
        if (authFromUrl) {
            localStorage.setItem('vyapari_auth_token', authFromUrl);
        }
        const token = localStorage.getItem('vyapari_auth_token');
        if (token) {
            document.addEventListener('click', (e) => {
                const anchor = e.target.closest('a');
                if (anchor && anchor.href && anchor.origin === window.location.origin && !anchor.href.includes('logout')) {
                    const url = new URL(anchor.href);
                    if (!url.searchParams.has('auth')) {
                        url.searchParams.set('auth', token);
                        anchor.href = url.toString();
                    }
                }
            });
            document.addEventListener('submit', (e) => {
                const form = e.target;
                if (form && form.action && form.action.includes('logout')) {
                    localStorage.removeItem('vyapari_auth_token');
                    return;
                }
                if (form && !form.querySelector('input[name="auth"]')) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'auth';
                    hidden.value = token;
                    form.appendChild(hidden);
                }
            });
        }
    } catch (e) {
        // ignore localStorage access issues
    }
})();

