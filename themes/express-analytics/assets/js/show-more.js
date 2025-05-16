document.addEventListener('DOMContentLoaded', function () {
  // Find all elements with show_more class
  const showMoreElements = document.querySelectorAll('.show_more');

  showMoreElements.forEach(element => {
    // Check if content exceeds 7 lines
    const lineHeight = parseInt(window.getComputedStyle(element).lineHeight);
    const maxHeight = lineHeight * 7;
    const contentHeight = element.scrollHeight;

    // Only add button if content exceeds 7 lines
    if (contentHeight > maxHeight) {
      // Create the trigger element
      const trigger = document.createElement('button');
      trigger.className = 'show_more_trigger';
      trigger.innerHTML = '<i class="fas fa-plus"></i>';
      trigger.setAttribute('aria-label', 'Show More');

      // Insert the trigger after the show_more element
      element.parentNode.insertBefore(trigger, element.nextSibling);

      // Add click event listener
      trigger.addEventListener('click', function () {
        const isExpanded = element.classList.contains('expanded');

        if (isExpanded) {
          // Collapse the text
          element.classList.remove('expanded');
          trigger.querySelector('i').className = 'fas fa-plus';
          trigger.setAttribute('aria-label', 'Show More');

          // Scroll to the top of the element
          element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
          // Expand the text
          element.classList.add('expanded');
          trigger.querySelector('i').className = 'fas fa-minus';
          trigger.setAttribute('aria-label', 'Show Less');
        }
      });
    } else {
      // Remove padding if no button is needed
      element.style.paddingRight = '0';
      element.style.paddingBottom = '0';
    }
  });
});
