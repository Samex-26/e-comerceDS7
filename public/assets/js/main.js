// Script principal del sistema E-Commerce DS7
document.addEventListener('DOMContentLoaded', function () {
    var banner = document.getElementById('consentCenter');
    var revoke = document.getElementById('revokeConsent');
    var hasDecision = document.cookie.split('; ').some(function (c) { return c.indexOf('analytics_consent=') === 0; });
    // La decisión es HttpOnly; esta cookie auxiliar no contiene identidad ni habilita tracking.
    if (!localStorage.getItem('analyticsConsentDecision') && banner) banner.hidden = false;
    function decide(value) {
        var body = document.body;
        var data = new URLSearchParams({csrf_token: body.dataset.csrf, decision: value});
        fetch(body.dataset.baseUrl + 'cookie/consentimiento', {method: 'POST', credentials: 'same-origin', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: data})
            .then(function (r) { if (!r.ok) throw new Error('consent'); return r.json(); })
            .then(function () { localStorage.setItem('analyticsConsentDecision', value); if (banner) banner.hidden = true; });
    }
    document.querySelectorAll('[data-consent]').forEach(function (b) { b.addEventListener('click', function () { decide(this.dataset.consent); }); });
    if (revoke) revoke.addEventListener('click', function () { if (banner) banner.hidden = false; localStorage.removeItem('analyticsConsentDecision'); decide('revoked'); });
});
