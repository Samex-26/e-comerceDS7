// Script principal del sistema E-Commerce DS7
console.log("Sistema E-Commerce DS7 cargado.");

function actualizarCantidad(idProducto, cantidad) {
    var formData = new FormData();
    formData.append('cantidad', cantidad);

    fetch(BASE_URL + 'carrito/actualizar/' + idProducto, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success) {
            var subtotalEl = document.querySelector('[data-subtotal-for="' + idProducto + '"]');
            if (subtotalEl) subtotalEl.textContent = '$' + data.subtotal;

            var totalSub = document.getElementById('cart-subtotal-summary');
            var totalEl  = document.getElementById('cart-total-summary');
            if (totalSub) totalSub.textContent = '$' + data.total;
            if (totalEl)  totalEl.textContent  = '$' + data.total;
        } else {
            alert(data.error);
        }
    })
    .catch(function () {
        alert('Error al actualizar la cantidad.');
    });
}

function enviarActualizacion(idProducto, input) {
    var val = parseInt(input.value) || 1;
    if (val < 1) val = 1;
    input.value = val;
    actualizarCantidad(idProducto, val);
}

document.addEventListener('click', function (e) {
    console.log('click detectado', e.target);
    var btn = e.target.closest('.btn-qty-minus, .btn-qty-plus');
    if (!btn) return;

    var container = btn.closest('[data-id-producto]');
    if (!container) return;

    var idProducto = container.getAttribute('data-id-producto');
    var input = container.querySelector('.input-qty');
    if (!input) return;

    var val = parseInt(input.value) || 1;
    if (btn.classList.contains('btn-qty-minus')) {
        if (val > 1) val--;
    } else {
        val++;
    }
    input.value = val;
    enviarActualizacion(idProducto, input);
});

document.addEventListener('change', function (e) {
    var input = e.target.closest('.input-qty');
    if (!input) return;

    var container = input.closest('[data-id-producto]');
    if (!container) return;

    var idProducto = container.getAttribute('data-id-producto');
    enviarActualizacion(idProducto, input);
});