export class Filters {
    constructor(view) {
        this.view = view;
    }

    draw(targetId) {
        const area = document.getElementById(targetId);
        area.innerHTML = `
            <div class="flex flex-col gap-4">
                <div class="grid grid-cols-2 md:grid-cols-6 gap-3 items-end">
                    <div class="flex flex-col">
                        <label class="text-xs font-bold text-gray-600 uppercase mb-1">Search ID</label>
                        <input type="text" data-filter="orderId" value="${this.view.state.orderId}" placeholder="ID#" class="border border-gray-300 p-2 rounded text-sm outline-none focus:border-red-700 focus:ring-1 focus:ring-red-700 transition">
                    </div>
                    <div class="flex flex-col">
                        <label class="text-xs font-bold text-gray-600 uppercase mb-1">Jurisdiction</label>
                        <select data-filter="county" class="border border-gray-300 p-2 rounded text-sm bg-white outline-none focus:border-red-700 focus:ring-1 focus:ring-red-700 transition">
                            <option value="">All Regions</option>
                            ${this.view.state.jurisdictions.map(j => `<option value="${j}" ${this.view.state.county === j ? 'selected' : ''}>${j}</option>`).join('')}
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-xs font-bold text-gray-600 uppercase mb-1">GPS (lat, lon)</label>
                        <input type="text" data-filter="coordsSearch" value="${this.view.state.coordsSearch}" placeholder="42.01, -78.86" class="border border-gray-300 p-2 rounded text-sm outline-none focus:border-red-700 focus:ring-1 focus:ring-red-700 transition">
                    </div>
                    <div class="flex flex-col">
                        <label class="text-xs font-bold text-gray-600 uppercase mb-1">Display/Sort</label>
                        <div class="flex gap-1">
                            <select data-filter="limit" class="border border-gray-300 p-2 rounded text-sm bg-white outline-none focus:border-red-700 focus:ring-1 focus:ring-red-700 transition w-full">
                                <option value="10" ${this.view.state.limit == 10 ? 'selected' : ''}>10</option>
                                <option value="50" ${this.view.state.limit == 50 ? 'selected' : ''}>50</option>
                            </select>
                            <select data-filter="sort" class="border border-gray-300 p-2 rounded text-sm bg-white outline-none focus:border-red-700 focus:ring-1 focus:ring-red-700 transition w-full">
                                <option value="DESC" ${this.view.state.sort == 'DESC' ? 'selected' : ''}>New</option>
                                <option value="ASC" ${this.view.state.sort == 'ASC' ? 'selected' : ''}>Old</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex flex-col">
                        <button class="btn-clear-filters border border-gray-300 text-gray-600 hover:text-red-700 hover:border-red-700 hover:bg-red-50 px-3 py-2 rounded text-xs font-black tracking-widest uppercase transition h-[42px] w-full">
                            Clear
                        </button>
                    </div>

                    <div class="pagination-container flex justify-end gap-1"></div>
                </div>
            </div>`;

        this.attachEvents(area);
    }

    attachEvents(area) {
        area.querySelectorAll('[data-filter]').forEach(el => {
            el.addEventListener('input', (e) => {
                const type = e.target.dataset.filter;
                const val = e.target.value;
                document.querySelectorAll(`[data-filter="${type}"]`).forEach(other => other.value = val);

                clearTimeout(this.view.searchTimeout);
                this.view.searchTimeout = setTimeout(() => {
                    this.view.state[type] = val;
                    this.view.loadData(1);
                }, 400);
            });
        });

        area.querySelector('.btn-clear-filters')?.addEventListener('click', () => {
            this.view.resetFilters();
        });
    }
}