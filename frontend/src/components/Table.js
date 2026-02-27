export class Table {
    constructor(view) {
        this.view = view;
    }

    render() {
        const tableView = document.getElementById('table-view');
        
        if (this.view.state.orders.length === 0) {
            tableView.innerHTML = `
                <p class="text-center text-gray-400 py-20 font-bold uppercase italic tracking-widest text-base animate-fadeIn">
                    No data matching filters
                </p>`;
            return;
        }

        tableView.innerHTML = `
            <table class="w-full text-left mt-4 border-collapse animate-fadeIn">
                <thead>
                    <tr class="text-gray-600 text-sm font-black uppercase tracking-widest border-b border-gray-300">
                        <th class="p-4">ID Order</th>
                        <th class="p-4">Coordinates</th>
                        <th class="p-4">Jurisdiction</th>
                        <th class="p-4 text-right">Subtotal</th>
                        <th class="p-4 text-right text-red-700">Tax</th>
                        <th class="p-4 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="text-sm font-bold cursor-pointer">
                    ${this.view.state.orders.map(o => `
                        <tr class="border-b border-gray-200 hover:bg-red-50/70 transition duration-150 group" data-id="${o.id}">
                            <td class="p-4 text-gray-500 group-hover:text-red-700 transition">#${o.id}</td>
                            <td class="p-4 font-mono text-gray-600">
                                ${parseFloat(o.latitude).toFixed(4)}, ${parseFloat(o.longitude).toFixed(4)}
                            </td>
                            <td class="p-4 uppercase text-gray-800">
                                ${o.county_name || '<span class="text-gray-400">Out of State</span>'}
                            </td>
                            <td class="p-4 text-right text-gray-500">
                                $${parseFloat(o.subtotal).toLocaleString()}
                            </td>
                            <td class="p-4 text-right text-red-700 font-bold">
                                $${parseFloat(o.tax_amount || 0).toFixed(2)}
                            </td>
                            <td class="p-4 text-right text-gray-900 font-black">
                                $${parseFloat(o.total_amount || o.subtotal).toLocaleString()}
                            </td>
                        </tr>`).join('')}
                </tbody>
            </table>`;

        this.attachEvents();
    }

    attachEvents() {
        const tbody = document.querySelector('#table-view tbody');
        if (!tbody) return;
        const newTbody = tbody.cloneNode(true);
        tbody.parentNode.replaceChild(newTbody, tbody);

        newTbody.addEventListener('click', (e) => {
            const row = e.target.closest('tr');
            if (row && row.dataset.id) {
                this.view.handleOrderClick(row.dataset.id);
            }
        });
    }
}