import { CONFIG } from '../config/appConfig.js';

export class HttpService {
    static getToken() {
        return localStorage.getItem(CONFIG.STORAGE_KEYS.TOKEN);
    }

    static async request(endpoint, options = {}) {
        const token = this.getToken();
        const headers = { 'Accept': 'application/json', ...options.headers };
        
        if (!(options.body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
        }
        
        if (token) headers['Authorization'] = `Bearer ${token}`;

        try {
            const response = await fetch(`${CONFIG.API_URL}${endpoint}`, { ...options, headers });
            
            if (response.status === 401) {
                window.dispatchEvent(new CustomEvent('auth:unauthorized'));
                return { error: true, data: null, status: 401 };
            }
            
            const data = await response.json();
            return { error: !response.ok, data, status: response.status };
        } catch (error) {
            console.error('[HttpService Error]:', error);
            return { error: true, data: { message: "Server Connection Error" }, status: 500 };
        }
    }
}