const navButton = document.querySelector('[data-menu]');
const links = document.querySelector('[data-links]');

if (navButton && links) {
    navButton.addEventListener('click', () => {
        links.classList.toggle('open');
    });

    links.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => links.classList.remove('open'));
    });
}

const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            entry.target.classList.add('in-view');
            revealObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.16 });

document.querySelectorAll('.reveal').forEach((element) => revealObserver.observe(element));

const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (!entry.isIntersecting) {
            return;
        }

        const element = entry.target;
        const target = Number.parseFloat(element.dataset.counter || '0');
        const isDecimal = !Number.isInteger(target);
        const start = performance.now();
        const duration = 1300;

        const tick = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const value = target * eased;
            element.textContent = isDecimal ? value.toFixed(1) : Math.round(value).toString();
            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        };

        requestAnimationFrame(tick);
        counterObserver.unobserve(element);
    });
}, { threshold: 0.5 });

document.querySelectorAll('[data-counter]').forEach((element) => counterObserver.observe(element));

document.querySelectorAll('[data-package-tabs]').forEach((tabs) => {
    const buttons = tabs.querySelectorAll('[data-package-tab]');
    const panels = document.querySelectorAll('[data-package-panel]');

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const target = button.dataset.packageTab;

            buttons.forEach((item) => item.classList.toggle('active', item === button));
            panels.forEach((panel) => panel.classList.toggle('active', panel.dataset.packagePanel === target));
        });
    });
});

window.addEventListener('scroll', () => {
    const nav = document.querySelector('[data-nav]');
    if (nav) {
        nav.classList.toggle('scrolled', window.scrollY > 40);
    }
}, { passive: true });
