import { AuthService } from '../services/serviceAuth.js';

export class Header {
    constructor(view) {
        this.view = view;
    }

    render() {
        return `
            <div class="flex justify-between items-center mb-8 border-b border-gray-300 pb-4">
                <h1 class="text-2xl font-black text-red-700 uppercase italic tracking-tighter">Webdogs test work</h1>
                <button id="logout-btn" class="text-sm font-bold text-gray-600 hover:text-red-700 uppercase tracking-widest transition">Logout</button>
            </div>
        `;
    }

    attachEvents() {
        document.getElementById('logout-btn')?.addEventListener('click', () => AuthService.logout());
    }
}