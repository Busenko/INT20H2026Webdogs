import { HttpService } from './serviceHTTP.js';
import { CONFIG } from '../config/appConfig.js';

export class OrderService {
    static getOrders(params = {}) {
        const query = new URLSearchParams(params).toString();
        return HttpService.request(`${CONFIG.ENDPOINTS.ORDERS}?${query}`, { method: 'GET' });
    }

    static getJurisdictions() {
        return HttpService.request(CONFIG.ENDPOINTS.JURISDICTIONS, { method: 'GET' });
    }

    static createOrder(data) {
        return HttpService.request(CONFIG.ENDPOINTS.ORDERS, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    static importCsv(file) {
        const formData = new FormData();
        formData.append('file', file);
        return HttpService.request(CONFIG.ENDPOINTS.IMPORT, {
            method: 'POST',
            body: formData
        });
    }

    static async getOrderDetails(id) {
    try {
        const response = await fetch(`${this.API_URL}/orders/${id}`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        return await response.json();
    } catch (error) {
        return { error: true, message: error.message };
    }
}
}