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
