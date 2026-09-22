document.addEventListener('change', (event) => {
    if (event.target.matches('[data-product-select]')) {
        const selected = event.target.options[event.target.selectedIndex];
        const nameInput = document.querySelector('[name="product_name"]');
        if (nameInput && selected.dataset.name) nameInput.value = selected.dataset.name;
        if (selected.dataset.rate) document.querySelector('[name="rate"]').value = selected.dataset.rate;
        if (selected.dataset.purity) document.querySelector('[name="melting"]').value = selected.dataset.purity;
    }
});
