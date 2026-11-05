
// Function to detect if the user is on a mobile device
function isMobileDevice() {
    return /Mobi|Android/i.test(navigator.userAgent);
}

// Function to handle key events for blocking developer tools
function keyFunction(event) {
    event = event || window.event;
    
    // F12 key
    if (event.keyCode == 123) {
        return false;
    }

    // Ctrl+Shift+I
    if (event.ctrlKey && event.shiftKey && event.keyCode == 73) {
        return false;
    }

    // Ctrl+Shift+J
    if (event.ctrlKey && event.shiftKey && event.keyCode == 74) {
        return false;
    }

    // Ctrl+U
    if (event.ctrlKey && event.keyCode == 85) {
        return false;
    }

    // F5 (Refresh)
    if (event.keyCode == 116) {
        return false;
    }
}

// Attach the keyFunction to key events for desktop
if (!isMobileDevice()) {
    document.onkeydown = keyFunction;
}

// Disable right-click for the entire document
document.addEventListener('contextmenu', function(event) {
    if (!isMobileDevice() || (event.target.tagName !== 'INPUT' && event.target.tagName !== 'TEXTAREA')) {
        event.preventDefault();
    }
}, false);

// Allow copy-paste functionality only on mobile devices in input fields
document.addEventListener('keydown', function(event) {
    if (isMobileDevice()) {
        if ((event.ctrlKey || event.metaKey) && (event.key === 'c' || event.key === 'v')) {
            if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') {
                return; // Allow copy and paste in input fields on mobile devices
            } else {
                event.preventDefault(); // Prevent copy-paste outside input fields on mobile devices
            }
        }
    } else {
        keyFunction(event); // Call keyFunction to handle other cases on desktop
    }
}, false);
