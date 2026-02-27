import { HttpService } from './serviceHTTP.js';
import { CONFIG } from '../config/appConfig.js';

export class AuthService {
    static async login(login, password) {
        const res = await HttpService.request(CONFIG.ENDPOINTS.LOGIN, {
            method: 'POST',
            body: JSON.stringify({ login, password })
        });

        if (res && !res.error && res.data.token) {
            this.setToken(res.data.token);
            return true;
        }
        return false;
    }

    static logout() {
        localStorage.removeItem(CONFIG.STORAGE_KEYS.TOKEN);
        window.dispatchEvent(new CustomEvent('auth:logout'));
    }

    static isAuthenticated() {
        return !!localStorage.getItem(CONFIG.STORAGE_KEYS.TOKEN);
    }

    static setToken(token) {
        localStorage.setItem(CONFIG.STORAGE_KEYS.TOKEN, token);
    }
}