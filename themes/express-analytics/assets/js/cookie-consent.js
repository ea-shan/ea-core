(function () {
  function setCookie(name, value, days) {
    var expires = "";
    if (days) {
      var date = new Date();
      date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
      expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
  }
  function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) == ' ') c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
  }
  if (getCookie('ea_cookie_consent')) return;
  var banner = document.createElement('div');
  banner.id = 'ea-cookie-consent';
  banner.innerHTML = `
        <div class="ea-cookie-content">
            <h3>We use cookies</h3>
            <p>
                This site uses cookies to enhance your experience, analyze traffic, and for marketing.
                <a href="/privacy-policy" target="_blank">Learn more</a>
            </p>
            <form id="ea-cookie-form">
                <label><input type="checkbox" name="necessary" checked disabled> Necessary (always active)</label><br>
                <label><input type="checkbox" name="analytics" checked> Analytics</label><br>
                <label><input type="checkbox" name="marketing"> Marketing</label><br>
                <div class="ea-cookie-actions">
                    <button type="button" id="ea-accept-all">Accept All</button>
                    <button type="submit">Save Selection</button>
                </div>
            </form>
        </div>
    `;
  document.body.appendChild(banner);
  document.getElementById('ea-accept-all').onclick = function () {
    setCookie('ea_cookie_consent', JSON.stringify({ necessary: true, analytics: true, marketing: true }), 180);
    banner.remove();
  };
  document.getElementById('ea-cookie-form').onsubmit = function (e) {
    e.preventDefault();
    var analytics = this.analytics.checked;
    var marketing = this.marketing.checked;
    setCookie('ea_cookie_consent', JSON.stringify({ necessary: true, analytics, marketing }), 180);
    banner.remove();
  };
})();
