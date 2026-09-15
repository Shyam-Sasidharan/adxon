document.addEventListener('DOMContentLoaded', () => {
    window.lucide?.createIcons();
    const nav = document.querySelector('#site-nav');
    const toggle = document.querySelector('[data-nav-toggle]');
    toggle?.addEventListener('click', () => toggle.setAttribute('aria-expanded', String(nav.classList.toggle('open'))));
    nav?.querySelectorAll('a').forEach(link => link.addEventListener('click', () => { nav.classList.remove('open'); toggle.setAttribute('aria-expanded', 'false'); }));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && nav?.classList.contains('open')) {
            nav.classList.remove('open'); toggle.setAttribute('aria-expanded', 'false'); toggle.focus();
        }
    });
    const tabs = [...document.querySelectorAll('[data-tab]')];
    const selectTab = tab => {
        tabs.forEach(item => { item.setAttribute('aria-selected', String(item === tab)); item.tabIndex = item === tab ? 0 : -1; });
        document.querySelectorAll('[data-panel]').forEach(panel => { panel.hidden = panel.dataset.panel !== tab.dataset.tab; });
    };
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => selectTab(tab));
        tab.addEventListener('keydown', event => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            const next = event.key === 'Home' ? tabs[0] : event.key === 'End' ? tabs.at(-1) : tabs[(index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length];
            selectTab(next); next.focus();
        });
    });
    document.querySelector('[data-blog-filter]')?.addEventListener('change', event => document.querySelectorAll('[data-category]').forEach(card => { card.hidden = !!event.target.value && card.dataset.category !== event.target.value; }));
    document.querySelectorAll('[data-package], [data-service]').forEach(link => link.addEventListener('click', () => {
        const message = document.querySelector('[name="message"]');
        if (message) message.value = `I would like to discuss ${link.dataset.package || link.dataset.service}.`;
    }));
    document.querySelectorAll('form').forEach(form => form.addEventListener('submit', () => {
        if (!form.checkValidity()) return;
        const submit = form.querySelector('button[type="submit"]');
        if (submit) { submit.disabled = true; submit.setAttribute('aria-busy', 'true'); }
    }));
    window.addEventListener('pageshow', () => document.querySelectorAll('[aria-busy="true"]').forEach(button => {
        button.disabled = false; button.removeAttribute('aria-busy');
    }));
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && 'IntersectionObserver' in window) {
        const counters = new IntersectionObserver(entries => entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            counters.unobserve(entry.target);
            const target = Number(entry.target.dataset.counter);
            if (!Number.isFinite(target)) return;
            const started = performance.now();
            const animate = now => {
                const progress = Math.min((now - started) / 900, 1);
                entry.target.textContent = (target * (1 - Math.pow(1 - progress, 3))).toFixed(Number.isInteger(target) ? 0 : 1);
                if (progress < 1) requestAnimationFrame(animate);
                else entry.target.textContent = entry.target.dataset.counter;
            };
            requestAnimationFrame(animate);
        }), { threshold: .5 });
        document.querySelectorAll('[data-counter]').forEach(element => counters.observe(element));
    }
});
