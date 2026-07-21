(function () {
    var banner = document.getElementById('cookieBanner');
    if (!banner) return;

    document.getElementById('btnAceptarCookies').addEventListener('click', function () {
        banner.style.display = 'none';
        var xhr = new XMLHttpRequest();
        xhr.open('POST', BASE_URL + 'cookie/consentir', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send('');
    });
})();
