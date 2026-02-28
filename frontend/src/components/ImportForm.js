import { OrderService } from '../services/serviceOrder.js';

export class ImportForm {
    constructor(view) {
        this.view = view;
        this.startTime = null;
        this.timerInterval = null;
    }

    render() {
        this.view.state.selectedFile = null;
        this.drawUI();
    }

    drawUI() {
        const area = document.getElementById('action-area');
        area.innerHTML = `
            <div id="import-container" class="flex flex-col items-center justify-center gap-4 animate-fadeIn h-full">
                <div id="file-selection-part" class="w-full flex flex-col items-center gap-3">
                    <input type="file" id="csv-input" accept=".csv" class="hidden">
                    <label for="csv-input" class="cursor-pointer bg-gray-900 text-white px-4 py-2 rounded text-sm font-bold uppercase hover:bg-red-700 transition shadow-sm">
                        ${this.view.state.selectedFile ? 'Change File' : 'Select CSV'}
                    </label>
                    ${this.view.state.selectedFile ? `
                        <p class="text-sm font-bold text-red-700 uppercase italic max-w-xs truncate">${this.view.state.selectedFile.name}</p>
                        <button id="start-import-btn" class="bg-red-700 text-white px-5 py-2 rounded font-black uppercase text-sm hover:bg-gray-900 transition shadow-lg">
                            Run Data Injection
                        </button>` : '<p class="text-sm text-gray-500 uppercase tracking-widest">Awaiting CSV Data</p>'}
                </div>

                <div id="import-progress-part" class="hidden w-full max-w-xs">
                    <div class="flex justify-between items-center mb-2 px-1">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-red-700 rounded-full animate-ping"></span>
                            <span class="text-sm font-black text-red-700 uppercase tracking-widest">Injecting...</span>
                        </div>
                        <span id="import-timer" class="text-base font-mono font-black text-gray-900">00:00.0</span>
                    </div>
                    <div class="w-full bg-gray-200 h-2 rounded-full relative overflow-hidden border border-gray-300">
                        <div id="progress-bar" class="h-full bg-red-700 transition-all duration-300 rounded-full" style="width: 0%"></div>
                    </div>
                    <p id="progress-status" class="text-xs text-gray-500 uppercase mt-2 text-center tracking-tighter">Preparing data stream...</p>
                </div>
            </div>`;

        document.getElementById('csv-input')?.addEventListener('change', (e) => {
            if (!this.view.state.isImporting) {
                this.view.state.selectedFile = e.target.files[0];
                this.drawUI();
            }
        });

        const startBtn = document.getElementById('start-import-btn');
        if (startBtn) {
            startBtn.addEventListener('click', this.handleImport.bind(this));
        }
    }

    updateTimer() {
        const now = Date.now();
        const diff = now - this.startTime;
        const minutes = Math.floor(diff / 60000);
        const seconds = Math.floor((diff % 60000) / 1000);
        const tenths = Math.floor((diff % 1000) / 100);
        
        const timeStr = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}.${tenths}`;
        const timerEl = document.getElementById('import-timer');
        if (timerEl) timerEl.innerText = timeStr;
    }

    async handleImport() {
        this.view.state.isImporting = true;
        const loader = document.getElementById('import-progress-part');
        const selector = document.getElementById('file-selection-part');
        const progressBar = document.getElementById('progress-bar');
        const statusText = document.getElementById('progress-status');
        const nav = document.getElementById('nav-tabs');

        selector.classList.add('hidden');
        loader.classList.remove('hidden');
        nav.style.pointerEvents = 'none';
        nav.style.opacity = '0.5';

        this.startTime = Date.now();
        this.timerInterval = setInterval(() => this.updateTimer(), 100);

        let fakeProgress = 0;
        const progressInterval = setInterval(() => {
            if (fakeProgress < 90) {
                fakeProgress += Math.random() * 5;
                if (progressBar) progressBar.style.width = `${Math.min(92, fakeProgress)}%`;
                
                if (fakeProgress > 60) statusText.innerText = "Processing batch records...";
                else if (fakeProgress > 30) statusText.innerText = "Validating entries...";
            }
        }, 800);

        const res = await OrderService.importCsv(this.view.state.selectedFile);

        clearInterval(this.timerInterval);
        clearInterval(progressInterval);
        
        if (progressBar) progressBar.style.width = '100%';
        if (statusText) statusText.innerText = "Finalizing transaction...";

        setTimeout(() => {
            this.view.state.isImporting = false;
            loader.classList.add('hidden');
            selector.classList.remove('hidden');
            nav.style.pointerEvents = 'auto';
            nav.style.opacity = '1';

            if (res && !res.error) {
                const finalTime = document.getElementById('import-timer')?.innerText;
                this.view.showStatus(`SUCCESS: ${res.data.imported_count} ITEMS INJECTED IN ${finalTime}`, "success");
                this.view.state.selectedFile = null;
                this.drawUI();
                this.view.loadData(1);
            } else {
                this.view.showStatus("FAILED: SYSTEM ERROR", "error");
            }
        }, 800);
    }
}