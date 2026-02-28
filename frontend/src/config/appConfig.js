const currentHost = window.location.hostname;
const apiUrl = (currentHost === 'localhost' || currentHost === '127.0.0.1') 
    ? 'http://localhost:8080' 
    : 'http://webdogs';

export const CONFIG = {
    API_URL: apiUrl,
    
    ENDPOINTS: {
        LOGIN: '/login',
        ORDERS: '/orders',
        JURISDICTIONS: '/orders/jurisdictions',
        IMPORT: '/orders/import'
    },
    STORAGE_KEYS: {
        TOKEN: 'jwt_token'
    }
};