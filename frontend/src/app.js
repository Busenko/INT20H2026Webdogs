import { AuthService } from './services/serviceAuth.js';
import { LoginView } from './views/login.js';
import { AdminView } from './views/admin.js';

export class App {
    constructor(rootElementId) {
        this.appElement = document.getElementById(rootElementId);
        this.setupGlobalListeners();
        this.injectStyles();
    }

    setupGlobalListeners() {
        // Слухаємо кастомні події для контролю доступу
        window.addEventListener('auth:unauthorized', () => this.handleLogout());
        window.addEventListener('auth:logout', () => this.render());
    }

    injectStyles() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes progressIndeterminate {
                0% { left: -35%; right: 100%; }
                60% { left: 100%; right: -90%; }
                100% { left: 100%; right: -90%; }
            }
            .progress-bar-inner {
                position: absolute; background-color: #dc2626;
                top: 0; bottom: 0; animation: progressIndeterminate 1.5s infinite ease-in-out;
            }
            .animate-fadeIn { animation: fadeIn 0.2s ease-out; }
            @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
            .locked-nav { pointer-events: none; opacity: 0.5; filter: grayscale(1); }
        `;
        document.head.appendChild(style);
    }

    init() { 
        this.render(); 
    }

    render() {
        this.appElement.innerHTML = ''; // Очищаємо DOM
        
        if (!AuthService.isAuthenticated()) {
            const loginView = new LoginView(this.appElement, () => this.render());
            loginView.render();
        } else {
            const adminView = new AdminView(this.appElement);
            adminView.render();
        }
    }

    handleLogout() {
        AuthService.logout(); // Це викличе подію 'auth:logout', яка запустить this.render()
    }
}