import { AuthService } from '../services/serviceAuth.js';
import { OrderService } from '../services/serviceOrder.js';
import { AdminService } from '../services/serviceAdmin.js';
import { Header } from '../components/Header.js';
import { Tabs } from '../components/Tabs.js';
import { ManualForm } from '../components/ManualForm.js';
import { ImportForm } from '../components/ImportForm.js';
import { Filters } from '../components/Filters.js';
import { Table } from '../components/Table.js';
import { TaxDetailsModal } from '../components/TaxDetailsModal.js';
import { Pagination } from '../components/Pagination.js';
import { showStatus, toggleTabs } from '../utils/helpers.js';

export class AdminView {
    constructor(container) {
        this.container = container;
        this.service = new AdminService(this);
        this.components = {
            header: new Header(this),
            tabs: new Tabs(this),
            manualForm: new ManualForm(this),
            importForm: new ImportForm(this),
            filters: new Filters(this),
            table: new Table(this),
            pagination: new Pagination(this)
        };
        this.state = this.service.getInitialState();
        this.searchTimeout = null;
    }

    async render() {
        const jRes = await OrderService.getJurisdictions();
        this.state.jurisdictions = (jRes && !jRes.error) ? jRes.data : [];

        this.container.innerHTML = this.getTemplate();
        this.attachStaticEvents();
        this.components.manualForm.render();
        this.components.filters.draw('controls-top');
        this.components.filters.draw('controls-bottom');
        
        await this.loadData();
    }

    handleOrderClick(orderId) {
        const orderData = this.state.orders.find(o => o.id == orderId);
        if (orderData) {
            const modal = new TaxDetailsModal(document.body);
            modal.render(orderData);
        }
    }

    getTemplate() {
        return `
            <div class="p-4 md:p-8 max-w-6xl mx-auto">
                ${this.components.header.render()}
                <div class="grid grid-cols-1 gap-8">
                    <div class="relative min-h-[220px] max-h-[220px]">
                        <div id="status-msg" class="absolute top-0 left-0 right-0 -translate-y-full mb-2 hidden font-bold text-center p-2 rounded text-[10px] uppercase tracking-widest animate-fadeIn z-50 shadow-md border"></div>
                        <div class="bg-white p-6 rounded shadow-sm border border-gray-100 h-full flex flex-col overflow-hidden">
                            ${this.components.tabs.render()}
                            <div id="action-area" class="flex-1 flex flex-col justify-center"></div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded shadow-sm border border-gray-100">
                        <div id="controls-top" class="mb-6"></div>
                        <div id="table-view" class="overflow-x-auto min-h-[300px]"></div>
                        <div id="controls-bottom" class="mt-8 border-t pt-6"></div>
                    </div>
                </div>
            </div>`;
    }

    attachStaticEvents() {
        this.components.header.attachEvents();
        this.components.tabs.attachEvents();
    }

    async loadData(page = 1) {
        await this.service.loadData(page);
    }

    resetFilters() {
        this.service.resetFilters();
    }

    showStatus(msg, type) {
        showStatus(msg, type);
    }

    toggleTabs(activeBtn, inactiveBtn) {
        toggleTabs(activeBtn, inactiveBtn);
        document.getElementById('status-msg').classList.add('hidden');
    }

    renderManualForm() {
        this.components.manualForm.render();
    }

    renderImportForm() {
        this.components.importForm.render();
    }
}