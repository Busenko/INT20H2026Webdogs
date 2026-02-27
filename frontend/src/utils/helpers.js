export function showStatus(msg, type) {
    const s = document.getElementById('status-msg');
    if (!s) return;
    
    s.className = `absolute top-0 left-0 right-0 -translate-y-full mb-2 p-3 rounded text-sm font-black uppercase tracking-widest animate-fadeIn z-50 shadow-md ${
        type === 'success' 
            ? 'bg-green-50 text-green-700 border border-green-300' 
            : 'bg-red-50 text-red-700 border border-red-300'
    }`;
    s.innerText = msg;
    s.classList.remove('hidden');
    setTimeout(() => s.classList.add('hidden'), 7000);
}

export function toggleTabs(activeBtn, inactiveBtn) {
    
    activeBtn.className = "pb-2 border-b-2 border-red-700 font-bold text-red-700 text-sm uppercase tracking-widest transition";
    

    inactiveBtn.className = "pb-2 border-b-2 border-transparent text-gray-500 text-sm uppercase tracking-widest transition hover:text-gray-700";
}