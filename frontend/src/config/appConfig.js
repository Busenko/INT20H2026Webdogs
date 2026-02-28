const isLocal = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';

export const CONFIG = {
    API_URL: isLocal ? 'http://localhost:8080' : 'http://webdogs',
    
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