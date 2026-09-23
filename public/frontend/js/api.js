/**
 * Noubtigo API Client
 * Handles token management and AJAX requests to the Laravel backend.
 */

const API_BASE_URL = 'http://localhost:8000/api/v1';

class ApiClient {
    static getToken() {
        return localStorage.getItem('noubtigo_token');
    }

    static setToken(token) {
        localStorage.setItem('noubtigo_token', token);
    }

    static removeToken() {
        localStorage.removeItem('noubtigo_token');
    }

    static async request(endpoint, options = {}) {
        const token = this.getToken();
        
        const defaultHeaders = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        };

        if (token) {
            defaultHeaders['Authorization'] = `Bearer ${token}`;
        }

        const config = {
            ...options,
            headers: {
                ...defaultHeaders,
                ...options.headers
            }
        };

        try {
            const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
            
            if (response.status === 401) {
                // Unauthorized - clear token and redirect to login
                this.removeToken();
                if (!window.location.pathname.includes('login.html') && !window.location.pathname.includes('register.html')) {
                    window.location.href = 'login.html';
                }
            }

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Something went wrong');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // Auth Shortcuts
    static async login(email, password) {
        const data = await this.request('/auth/login', {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });
        this.setToken(data.token);
        return data;
    }

    static async logout() {
        await this.request('/auth/logout', { method: 'POST' });
        this.removeToken();
        window.location.href = 'login.html';
    }
}

// Global UI Helper: Handle Logout
document.addEventListener('click', (e) => {
    if (e.target.closest('[data-action="logout"]')) {
        e.preventDefault();
        ApiClient.logout();
    }
});
