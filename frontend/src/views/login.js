import { AuthService } from '../services/serviceAuth.js';

export class LoginView {
    constructor(container, onLoginSuccess) {
        this.container = container;
        this.onLoginSuccess = onLoginSuccess;
    }

    render() {
        this.container.innerHTML = `
            <div class="flex h-screen items-center justify-center bg-gray-100 p-4">
                <form id="login-form" class="bg-white p-8 rounded shadow-lg w-full max-w-sm border-t-4 border-red-600 animate-fadeIn text-center">
                    <h2 class="text-2xl font-black mb-6 text-gray-800 uppercase italic tracking-tighter">OMS</h2>
                    <input type="text" id="login-user" placeholder="Login" class="w-full border p-3 mb-4 rounded outline-none focus:border-red-600 transition" required>
                    <input type="password" id="pass-user" placeholder="Password" class="w-full border p-3 mb-6 rounded outline-none focus:border-red-600 transition" required>
                    <button type="submit" class="w-full bg-red-600 text-white py-3 rounded font-bold uppercase hover:bg-black transition">Enter</button>
                </form>
            </div>`;

        this.attachEvents();
    }

    attachEvents() {
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const login = document.getElementById('login-user').value;
            const pass = document.getElementById('pass-user').value;
            
            const success = await AuthService.login(login, pass);
            if (success) {
                this.onLoginSuccess();
            } else {
                alert("Login failed. Please check your credentials.");
            }
        });
    }
}