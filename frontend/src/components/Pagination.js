export class Pagination {
    constructor(view) {
        this.view = view;
    }

    update(totalPages) {
        const containers = document.querySelectorAll('.pagination-container');
        containers.forEach(container => {
            container.innerHTML = '';
            if (totalPages <= 1) return;

            const range = 1;
            let start = Math.max(1, this.view.state.currentPage - range);
            let end = Math.min(totalPages, this.view.state.currentPage + range);

            if (start > 1) container.appendChild(this.createPageButton(1, '1'));
            
            for (let i = start; i <= end; i++) {
                container.appendChild(this.createPageButton(i, i));
            }
            
            if (end < totalPages) {
                container.appendChild(this.createPageButton(totalPages, totalPages));
            }
        });
    }

    createPageButton(page, label) {
        const btn = document.createElement('button');
        btn.innerText = label;
        btn.className = `w-8 h-8 flex items-center justify-center text-sm font-black border rounded transition duration-200 ${
            page === this.view.state.currentPage 
                ? 'bg-red-700 text-white border-red-700 shadow-md scale-110' 
                : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300'
        }`;
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            this.view.loadData(page);
        });
        return btn;
    }
}