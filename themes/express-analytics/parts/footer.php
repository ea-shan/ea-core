<?php
// Accessible Footer Template Part for Express Analytics Theme
?>
<footer class="footer-section wp-block-group" role="contentinfo" aria-label="Site Footer">
  <nav class="footer-navigation" role="navigation" aria-label="Footer Navigation">
    <!-- Example navigation links, replace with your dynamic menu if needed -->
    <ul>
      <li><a href="/" aria-label="Back to Home"><i class="fa fa-home"></i></a></li>
      <li><a href="/account" aria-label="Your Account"><i class="fa fa-user-circle"></i></a></li>
      <li><a href="/wishlist" aria-label="Wishlist"><i class="fa fa-heart"></i></a></li>
      <li><a href="/settings" aria-label="Settings"><i class="fa fa-cog"></i></a></li>
      <li><a href="/help" aria-label="Help and Support"><i class="fa fa-comments"></i></a></li>
      <li><a href="/cart" aria-label="Your Cart"><i class="fa fa-shopping-cart"></i></a></li>
    </ul>
  </nav>
  <div class="footer-info" aria-label="Footer Info">
    <p>&copy; <?php echo date('Y'); ?> <a href="https://expressanalytics.com">Express Analytics</a> All Rights Reserved</p>
    <p>
      <a href="/privacy-policy" aria-label="Privacy Policy">Privacy Policy</a> |
      <a href="/terms" aria-label="Terms and Conditions">Terms &amp; Conditions</a> |
      <a href="/support-policy" aria-label="Support Policy">Support Policy</a>
      <!-- Uncomment if needed for CCPA/CPRA -->
      <!-- | <a href="/do-not-sell" aria-label="Do Not Sell My Personal Information">Do Not Sell My Personal Information</a> -->
    </p>
  </div>
  <div class="footer-contact" aria-label="Contact Information">
    <p>Email: <a href="mailto:info@expressanalytics.com">info@expressanalytics.com</a></p>
    <p>Phone: <a href="tel:+1234567890">+1 234 567 890</a></p>
  </div>
  <div class="footer-backtotop">
    <button class="top" role="button" tabindex="0" aria-label="Back to top" onclick="window.scrollTo({top:0,behavior:'smooth'});">
      <i class="fa fa-chevron-up"></i>
    </button>
  </div>
</footer>
<!-- Cookie Notification Banner -->
<div id="cookie-notice" role="dialog" aria-live="polite" aria-label="Cookie Consent" style="display:none;">
  <div>
    This website uses cookies to ensure you get the best experience.
    <a href="/privacy-policy" target="_blank" aria-label="Read our privacy policy">Learn more</a>.
    <button id="cookie-accept" aria-label="Accept Cookies">Accept</button>
    <button id="cookie-decline" aria-label="Decline Non-Essential Cookies">Decline</button>
  </div>
</div>
<script>
  (function() {
    if (!localStorage.getItem('cookieConsent')) {
      document.getElementById('cookie-notice').style.display = 'block';
    }
    document.getElementById('cookie-accept').onclick = function() {
      localStorage.setItem('cookieConsent', 'accepted');
      document.getElementById('cookie-notice').style.display = 'none';
    };
    document.getElementById('cookie-decline').onclick = function() {
      localStorage.setItem('cookieConsent', 'declined');
      document.getElementById('cookie-notice').style.display = 'none';
    };
  })();
</script>
<style>
  #cookie-notice {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #222;
    color: #fff;
    padding: 1em;
    z-index: 9999;
    text-align: center;
  }

  #cookie-notice button {
    margin-left: 1em;
  }
</style>
