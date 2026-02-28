const currentPort = window.location.port;
const apiUrl = (currentPort === '3000') 
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