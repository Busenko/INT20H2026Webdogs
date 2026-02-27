export class TaxDetailsModal {
    constructor(container) {
        this.container = container;
    }

    render(data) {
        const modalHtml = `
            <div id="modal-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4 animate-fadeIn">
                <div class="bg-white w-full max-w-md rounded-lg shadow-2xl overflow-hidden border border-gray-200">
                    <div class="bg-red-800 p-4 flex justify-between items-center">
                        <h2 class="text-white font-black uppercase tracking-tighter italic text-base">Tax Audit Details — #${data.id}</h2>
                        <button id="close-modal" class="text-white/80 hover:text-white transition text-xl">&times;</button>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between border-b border-gray-300 pb-2">
                            <span class="text-sm uppercase font-bold text-gray-600">Jurisdiction</span>
                            <span class="text-base font-black text-gray-900">${data.county_name || 'N/A'}</span>
                        </div>
                        
                        <div class="space-y-2 bg-gray-50 p-3 rounded border border-gray-200">
                            <p class="text-sm uppercase font-bold text-gray-600 border-b border-gray-200 pb-1 mb-2">Detailed Rates</p>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">NY State Rate:</span>
                                <span class="font-bold text-gray-900">${(parseFloat(data.state_rate || 0) * 100).toFixed(2)}%</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Local (County/City):</span>
                                <span class="font-bold text-gray-900">${((parseFloat(data.county_rate || 0) + parseFloat(data.city_rate || 0)) * 100).toFixed(2)}%</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Special (MCTD):</span>
                                <span class="font-bold text-gray-900">${(parseFloat(data.special_rates || 0) * 100).toFixed(2)}%</span>
                            </div>
                            <div class="flex justify-between text-sm pt-1 border-t border-gray-300 mt-1 font-black">
                                <span class="text-red-800 uppercase">Composite Rate:</span>
                                <span class="text-red-800">${(parseFloat(data.composite_tax_rate || 0) * 100).toFixed(3)}%</span>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-dashed border-gray-300 space-y-2">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Subtotal</span>
                                <span>$${parseFloat(data.subtotal).toFixed(2)}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Computed Tax Amount</span>
                                <span>$${parseFloat(data.tax_amount).toFixed(2)}</span>
                            </div>
                            <div class="flex justify-between text-base font-black text-gray-900 pt-1">
                                <span>Grand Total</span>
                                <span>$${parseFloat(data.total_amount).toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 p-4 flex justify-end">
                        <button id="modal-ok" class="bg-gray-900 text-white px-6 py-2 rounded text-sm font-bold uppercase hover:bg-red-800 transition">Close Report</button>
                    </div>
                </div>
            </div>`;

        this.container.insertAdjacentHTML('beforeend', modalHtml);
        this.attachEvents();
    }

    attachEvents() {
        const close = () => {
            const el = document.getElementById('modal-overlay');
            if (el) el.remove();
        };
        
        const closeBtn = document.getElementById('close-modal');
        const okBtn = document.getElementById('modal-ok');
        const overlay = document.getElementById('modal-overlay');

        if (closeBtn) closeBtn.onclick = close;
        if (okBtn) okBtn.onclick = close;
        if (overlay) overlay.onclick = (e) => {
            if (e.target.id === 'modal-overlay') close();
        };
    }
}