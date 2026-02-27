import { OrderService } from './serviceOrder.js';

export class AdminService {
    constructor(view) {
        this.view = view;
    }

    getInitialState() {
        return {
            orders: [],
            jurisdictions: [],
            currentPage: 1,
            limit: 10,
            sort: 'DESC',
            county: '',
            orderId: '',
            coordsSearch: '',
            selectedFile: null,
            isImporting: false
        };
    }

    resetFilters() {
        this.view.state.orderId = '';
        this.view.state.county = '';
        this.view.state.coordsSearch = '';
        this.view.state.limit = 10;
        this.view.state.sort = 'DESC';

        document.querySelectorAll('[data-filter="orderId"]').forEach(el => el.value = '');
        document.querySelectorAll('[data-filter="county"]').forEach(el => el.value = '');
        document.querySelectorAll('[data-filter="coordsSearch"]').forEach(el => el.value = '');
        document.querySelectorAll('[data-filter="limit"]').forEach(el => el.value = '10');
        document.querySelectorAll('[data-filter="sort"]').forEach(el => el.value = 'DESC');

        this.view.loadData(1);
    }

    async loadData(page = 1) {
        this.view.state.currentPage = page;

        let lat = '', lon = '';
        const coordsValue = this.view.state.coordsSearch.trim();

        if (coordsValue !== '') {
            const parts = coordsValue.split(',');
            if (parts.length !== 2 || parts[0].trim() === '' || parts[1].trim() === '') {
                return;
            }
            lat = parts[0].trim();
            lon = parts[1].trim();
        }

        const viewElement = document.getElementById('table-view');
        if (viewElement) viewElement.style.opacity = '0.5';

        const params = {
            page: this.view.state.currentPage,
            limit: this.view.state.limit,
            sort: this.view.state.sort,
            county: this.view.state.county,
            id: this.view.state.orderId,
            lat: lat,
            lon: lon
        };

        const res = await OrderService.getOrders(params);
        if (res && !res.error) {
            this.view.state.orders = res.data?.data || [];
            this.view.components.table.render();
            this.view.components.pagination.update(res.data?.meta?.total_pages || 1);
            if (viewElement) viewElement.style.opacity = '1';
        }
    }
}