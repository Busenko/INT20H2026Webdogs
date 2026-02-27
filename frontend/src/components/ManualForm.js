import { OrderService } from '../services/serviceOrder.js';

export class ManualForm {
    constructor(view) {
        this.view = view;
    }

    render() {
        document.getElementById('action-area').innerHTML = `
            <form id="order-form" class="flex flex-wrap gap-4 animate-fadeIn">
                <div class="flex-1 flex flex-col min-w-[250px]">
                    <label class="text-sm font-bold text-gray-600 uppercase mb-1">GPS Coordinates (lat, lon)</label>
                    <input type="text" id="manual-coords" placeholder="42.0125, -78.8672" class="w-full border border-gray-300 p-3 rounded text-sm outline-none focus:border-red-700 focus:ring-1 focus:ring-red-700 transition" required>
                </div>
                <div class="flex-1 flex flex-col min-w-[200px]">
                    <label class="text-sm font-bold text-gray-600 uppercase mb-1">Subtotal Amount</label>
                    <input type="number" step="0.01" id="subtotal-in" placeholder="0.00" class="w-full border border-gray-300 p-3 rounded text-sm outline-none focus:border-red-700 focus:ring-1 focus:ring-red-700 transition" required>
                </div>
                <div class="flex items-end">
                    <button type="submit" id="save-btn" class="bg-red-700 text-white px-8 py-3 rounded font-bold uppercase text-sm hover:bg-gray-900 transition shadow-sm min-w-[140px] h-[50px]">Create Record</button>
                </div>
            </form>`;

        document.getElementById('order-form').addEventListener('submit', this.handleSubmit.bind(this));
    }

    async handleSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('save-btn');
        const coordsInput = document.getElementById('manual-coords').value;

        let lat = 0, lon = 0;
        if (coordsInput.includes(',')) {
            const parts = coordsInput.split(',');
            lat = parseFloat(parts[0].trim());
            lon = parseFloat(parts[1].trim());
        } else {
            alert("Please enter coordinates in 'lat, lon' format.");
            return;
        }

        btn.disabled = true;
        btn.innerText = "Saving...";

        const res = await OrderService.createOrder({
            latitude: lat,
            longitude: lon,
            subtotal: parseFloat(document.getElementById('subtotal-in').value)
        });

        if (res && !res.error) {
            this.view.showStatus("SUCCESS: RECORD SAVED", "success");
            await this.view.loadData(1);
            e.target.reset();
        } else {
            this.view.showStatus("ERROR: SAVE FAILED", "error");
        }
        btn.disabled = false;
        btn.innerText = "Create Record";
    }
}