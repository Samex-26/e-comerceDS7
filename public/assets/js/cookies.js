(function () {
    var banner = document.getElementById('cookieBanner');
    if (!banner) return;

    if (document.cookie.indexOf('cookie_consent=1') !== -1) {
        return;
    }

    banner.style.display = '';

    document.getElementById('btnAceptarCookies').addEventListener('click', function () {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', BASE_URL + 'cookie/consentir', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                banner.style.display = 'none';
            }
        };
        xhr.send('');
    });
})();
