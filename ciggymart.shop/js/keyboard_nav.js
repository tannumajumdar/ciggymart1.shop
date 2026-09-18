/**
 * Global Keyboard Navigation for Keshri Express ERP
 * Maps 'Enter' key to 'Tab' behavior to allow rapid data entry without mouse.
 */
document.addEventListener('DOMContentLoaded', function() {
    
    document.addEventListener('keydown', function(e) {
        // Only intercept if the Enter key is pressed
        if (e.key === 'Enter') {
            
            // Get the current active element
            let active = document.activeElement;
            
            // Allow default Enter behavior in textareas or buttons
            if (active.tagName === 'TEXTAREA' || active.tagName === 'BUTTON' || active.type === 'submit') {
                return;
            }

            // Prevent the default form submission
            e.preventDefault();

            // Find all focusable elements in the current form, or document if not in a form
            let container = active.closest('form') || document;
            
            // Select all typical form inputs that are visible and not disabled
            let selectors = 'input:not([type="hidden"]):not([disabled]):not([readonly]), select:not([disabled]), textarea:not([disabled]), button:not([disabled])';
            let focusables = Array.from(container.querySelectorAll(selectors)).filter(el => {
                return el.offsetWidth > 0 && el.offsetHeight > 0; // Check if visible
            });
            
            // Find current element index
            let currentIndex = focusables.indexOf(active);
            
            // If the element is found and there is a next element, focus it
            if (currentIndex > -1 && currentIndex < focusables.length - 1) {
                let nextElement = focusables[currentIndex + 1];
                nextElement.focus();
                
                // If it's a text input, select its text for rapid overwriting
                if (nextElement.tagName === 'INPUT' && ['text', 'number', 'email', 'password'].includes(nextElement.type)) {
                    nextElement.select();
                }
            }
        }
    });
});


/**
 * Mobile Sidebar Toggle Logic
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inject backdrop
    let backdrop = document.createElement('div');
    backdrop.className = 'sidebar-backdrop';
    document.body.appendChild(backdrop);

    // Find the toggle button (in header.php)
    let toggleBtn = document.querySelector('.btn-menu-toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            document.body.classList.toggle('sidebar-open');
        });
    }

    // Close on backdrop click
    backdrop.addEventListener('click', function() {
        document.body.classList.remove('sidebar-open');
    });
});
