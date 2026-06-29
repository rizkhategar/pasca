function pascaContactReady(callback) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback);
        return;
    }

    callback();
}

pascaContactReady(() => {
    const modal = document.getElementById('waAdminModal');
    const contactCard = document.querySelector('[data-wa-contact-card]');
    const floatingButton = document.querySelector('.wa-floating');

    let primaryAdmin = null;

    try {
        const json = modal?.querySelector('[data-primary-admin-json]')?.textContent || '';
        primaryAdmin = json ? JSON.parse(json) : null;
    } catch (error) {
        primaryAdmin = null;
    }

    const name = primaryAdmin?.name || contactCard?.dataset.waAdminName || floatingButton?.dataset.waAdminName || 'Admin WhatsApp';
    const number = primaryAdmin?.number || contactCard?.dataset.waAdminNumber || floatingButton?.dataset.waAdminNumber || '';
    const url = primaryAdmin?.url || contactCard?.dataset.waAdminUrl || floatingButton?.dataset.waAdminUrl || '#waAdminModal';

    if (contactCard) {
        const title = contactCard.querySelector('.contact-info h2');
        const link = contactCard.querySelector('.contact-info a');

        if (title) title.textContent = name;
        if (link) {
            link.innerHTML = `${number || 'Nomor WhatsApp belum tersedia'} <i class="fas fa-chevron-right"></i>`;
            link.setAttribute('href', url);
            link.setAttribute('aria-label', `Chat WhatsApp ${name}`);
        }

        contactCard.setAttribute('aria-label', `Pilih ${name}`);
    }

    if (floatingButton) {
        floatingButton.setAttribute('href', url);
        floatingButton.setAttribute('aria-label', `Chat WhatsApp ${name}`);
    }
});
