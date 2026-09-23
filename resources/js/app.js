import './bootstrap';

// Show expiration message and redirect
function handleRedirect(redirectUrl, customMessage) {
    const defaultMsg = 'Your session has expired. Please login again.';
    const message = customMessage || defaultMsg;
    
    // Clear any active interval or timers if needed, then alert and redirect
    if (window.Swal) {
        window.Swal.fire({
            icon: 'warning',
            title: 'Session Expired',
            text: message,
            confirmButtonText: 'Login'
        }).then(() => {
            window.location.href = redirectUrl;
        });
    } else {
        alert(message);
        window.location.href = redirectUrl;
    }
}

// Function to configure jQuery AJAX when jQuery is loaded
function setupJQueryAjax() {
    if (window.jQuery) {
        // Setup global ajaxError handler
        window.jQuery(document).ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {
            if (jqXHR.status === 401 || jqXHR.status === 419) {
                let redirectUrl = '/login';
                let message = null;
                try {
                    const response = JSON.parse(jqXHR.responseText);
                    if (response.redirect) {
                        redirectUrl = response.redirect;
                    }
                    if (response.message) {
                        message = response.message;
                    }
                } catch (e) {}
                
                // Prevent default browser error handling/logs if possible
                event.preventDefault();
                
                // Redirect to login page
                handleRedirect(redirectUrl, message);
            }
        });

        // Disable default alert behavior for DataTables AJAX errors globally
        if (window.jQuery.fn.dataTable) {
            window.jQuery.fn.dataTable.ext.errMode = 'none';
        }

        // Suppress DT errors on document just in case it is loaded/initialized later
        window.jQuery(document).on('error.dt', function(e, settings, techNote, message) {
            e.preventDefault();
            const xhr = settings.jqXHR;
            if (xhr && (xhr.status === 401 || xhr.status === 419)) {
                let redirectUrl = '/login';
                let errorMsg = null;
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.redirect) redirectUrl = response.redirect;
                    if (response.message) errorMsg = response.message;
                } catch (err) {}
                handleRedirect(redirectUrl, errorMsg);
            }
        });
        
        return true;
    }
    return false;
}

// Intercept Axios requests
if (window.axios) {
    window.axios.interceptors.response.use(
        response => response,
        error => {
            if (error.response && (error.response.status === 401 || error.response.status === 419)) {
                let redirectUrl = '/login';
                let message = null;
                if (error.response.data) {
                    if (error.response.data.redirect) {
                        redirectUrl = error.response.data.redirect;
                    }
                    if (error.response.data.message) {
                        message = error.response.data.message;
                    }
                }
                handleRedirect(redirectUrl, message);
                return new Promise(() => {}); // Return unresolved promise to halt the chain
            }
            return Promise.reject(error);
        }
    );
}

// Intercept native Fetch requests
const { fetch: originalFetch } = window;
window.fetch = async (...args) => {
    try {
        const response = await originalFetch(...args);
        if (response.status === 401 || response.status === 419) {
            let redirectUrl = '/login';
            let message = null;
            try {
                const clone = response.clone();
                const data = await clone.json();
                if (data.redirect) {
                    redirectUrl = data.redirect;
                }
                if (data.message) {
                    message = data.message;
                }
            } catch (e) {}
            handleRedirect(redirectUrl, message);
            return new Promise(() => {}); // Halt the execution chain
        }
        return response;
    } catch (error) {
        throw error;
    }
};

// Try setting up jQuery AJAX immediately or wait for DOMContentLoaded / load
if (!setupJQueryAjax()) {
    document.addEventListener('DOMContentLoaded', () => {
        if (!setupJQueryAjax()) {
            window.addEventListener('load', setupJQueryAjax);
        }
    });
}

