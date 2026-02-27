export class Tabs {
    constructor(view) {
        this.view = view;
    }

    render() {
        return `
            <div id="nav-tabs" class="flex gap-6 mb-6 border-b border-gray-300 pb-2">
                <button id="tab-manual" class="pb-2 border-b-2 border-red-700 font-bold text-red-700 text-sm uppercase tracking-widest transition">Manual Entry</button>
                <button id="tab-import" class="pb-2 border-b-2 border-transparent text-gray-500 text-sm uppercase tracking-widest transition hover:text-gray-700">CSV Processing</button>
            </div>
        `;
    }

    attachEvents() {
        const bM = document.getElementById('tab-manual');
        const bI = document.getElementById('tab-import');

        bM?.addEventListener('click', () => {
            if (!this.view.state.isImporting) {
                this.view.toggleTabs(bM, bI);
                this.view.renderManualForm();
            }
        });

        bI?.addEventListener('click', () => {
            if (!this.view.state.isImporting) {
                this.view.toggleTabs(bI, bM);
                this.view.renderImportForm();
            }
        });
    }
}