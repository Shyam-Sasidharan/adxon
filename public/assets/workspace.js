document.addEventListener('DOMContentLoaded', () => {
    window.lucide?.createIcons();
    const menu = document.querySelector('[data-workspace-menu]');
    const closeMenu = () => { document.body.classList.remove('nav-open'); menu?.setAttribute('aria-expanded', 'false'); };
    menu?.addEventListener('click', () => menu.setAttribute('aria-expanded', String(document.body.classList.toggle('nav-open'))));
    document.addEventListener('keydown', event => { if (event.key === 'Escape') closeMenu(); });
    document.addEventListener('click', event => { if (!event.target.closest('.admin-sidebar, [data-workspace-menu]')) closeMenu(); });
    document.querySelectorAll('[data-filter-table]').forEach(container => {
        const rows = [...container.querySelectorAll('[data-record]')];
        const filter = () => {
            const query = container.querySelector('[data-search]')?.value.toLowerCase().trim() || '';
            const filters = [...container.querySelectorAll('[data-filter]')];
            const from = container.querySelector('[data-date-from]')?.value;
            const to = container.querySelector('[data-date-to]')?.value;
            rows.forEach(row => {
                row.hidden = !row.textContent.toLowerCase().includes(query) || filters.some(input => input.value && row.dataset[input.dataset.filter] !== input.value) || !!(from && row.dataset.date < from) || !!(to && row.dataset.date > to);
                if (row.hidden) row.querySelectorAll('[name="ids[]"]').forEach(input => { input.checked = false; });
            });
            const empty = container.querySelector('[data-empty]');
            if (empty) empty.hidden = rows.some(row => !row.hidden);
        };
        container.querySelectorAll('[data-search], [data-filter], [data-date-from], [data-date-to]').forEach(input => input.addEventListener('input', filter));
        container.querySelector('[data-select-all]')?.addEventListener('change', event => rows.filter(row => !row.hidden).forEach(row => row.querySelectorAll('[name="ids[]"]').forEach(input => { input.checked = event.target.checked; })));
        container.querySelector('[data-sort]')?.addEventListener('change', event => {
            const sorted = [...rows].sort((a, b) => event.target.value === 'name' ? a.dataset.name.localeCompare(b.dataset.name) : (b.dataset.date || '').localeCompare(a.dataset.date || ''));
            sorted.forEach(row => row.parentElement.appendChild(row));
        });
        container.querySelector('[data-sort]')?.dispatchEvent(new Event('change'));
        filter();
    });
    if (window.Chart) {
        const colors = ['#2d8662', '#e8bd56', '#87b4a1', '#5d91b4', '#bba0bc', '#bd7859', '#b8c3bb'];
        document.querySelectorAll('[data-chart]').forEach(canvas => {
            const type = canvas.dataset.chart;
            new Chart(canvas, {
                type,
                data: { labels: JSON.parse(canvas.dataset.labels), datasets: [{ label: type === 'doughnut' ? 'Share (%)' : 'Total', data: JSON.parse(canvas.dataset.values), backgroundColor: type === 'doughnut' ? colors : '#2d866225', borderColor: type === 'doughnut' ? '#ffffff' : '#2d8662', borderWidth: 2, fill: type === 'line', tension: .35, pointRadius: 3, pointHoverRadius: 6, borderRadius: type === 'bar' ? 4 : 0 }] },
                options: { responsive: true, maintainAspectRatio: false, animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : { duration: 500 }, plugins: { legend: { display: type === 'doughnut', position: 'bottom', labels: { boxWidth: 9, boxHeight: 9, padding: 12, color: '#718278', font: { size: 10 } } } }, ...(type === 'doughnut' ? { cutout: '75%' } : { scales: { x: { grid: { display: false }, ticks: { color: '#718278', font: { size: 10 } } }, y: { beginAtZero: true, border: { display: false }, grid: { color: '#80988820' }, ticks: { color: '#718278', font: { size: 10 } } } } }) }
            });
        });
    }
    document.querySelectorAll('form[data-confirm]').forEach(form => form.addEventListener('submit', event => { if (!window.confirm(form.dataset.confirm)) event.preventDefault(); }));
    document.querySelectorAll('form').forEach(form => form.addEventListener('submit', event => {
        if (event.defaultPrevented || !form.checkValidity()) return;
        const button = form.querySelector('button[type="submit"], button:not([type])');
        if (button) { button.disabled = true; button.setAttribute('aria-busy', 'true'); }
    }));
    window.addEventListener('pageshow', () => document.querySelectorAll('[aria-busy="true"]').forEach(button => {
        button.disabled = false; button.removeAttribute('aria-busy');
    }));
});
