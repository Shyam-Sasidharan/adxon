document.querySelectorAll('[data-repeat-form]').forEach((form) => {
    const rows = form.querySelector('[data-rows]');
    const template = form.querySelector('[data-row-template]');
    const add = form.querySelector('[data-add-row]');

    const refreshNames = () => {
        rows.querySelectorAll('.cms-row').forEach((row, index) => {
            row.querySelectorAll('[data-name]').forEach((field) => {
                field.name = `rows[${index}][${field.dataset.name}]`;
            });
            row.querySelectorAll('[name^="rows["]').forEach((field) => {
                field.name = field.name.replace(/rows\[\d+\]/, `rows[${index}]`);
            });
        });
    };

    add?.addEventListener('click', () => {
        const fragment = template.content.cloneNode(true);
        rows.appendChild(fragment);
        refreshNames();
    });

    rows.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-row]');
        if (!button) {
            return;
        }
        button.closest('.cms-row')?.remove();
        refreshNames();
    });
});

const adminBody = document.querySelector('.admin-body');
const themeToggle = document.querySelector('[data-theme-toggle]');
const themeLogos = document.querySelectorAll('[data-theme-logo]');

const refreshThemeLogos = () => {
    themeLogos.forEach((logo) => {
        const source = adminBody?.classList.contains('light-mode') ? logo.dataset.lightSrc : logo.dataset.darkSrc;
        if (source) {
            logo.src = source;
        }
    });
};

if (adminBody && themeToggle) {
    const savedMode = localStorage.getItem('adxon-admin-theme');
    if (savedMode === 'light') {
        adminBody.classList.add('light-mode');
    } else if (savedMode === 'dark') {
        adminBody.classList.remove('light-mode');
    }
    refreshThemeLogos();

    themeToggle.addEventListener('click', () => {
        adminBody.classList.toggle('light-mode');
        localStorage.setItem('adxon-admin-theme', adminBody.classList.contains('light-mode') ? 'light' : 'dark');
        refreshThemeLogos();
    });
}

document.querySelectorAll('[data-sidebar-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
        const group = toggle.closest('[data-sidebar-group]');
        const isOpen = group?.classList.toggle('open') ?? false;
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
});
