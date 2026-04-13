// =========================================================================
// Modal Legal (Privacidad, Términos, Cookies)
// =========================================================================
document.addEventListener('DOMContentLoaded', () => {
    const modalsBtns = document.querySelectorAll('.open-legal-modal');
    const modal = document.getElementById('legal-modal');
    const backdrop = document.getElementById('legal-modal-backdrop');
    const closeBtn = document.getElementById('legal-modal-close');
    
    const tabBtns = document.querySelectorAll('.legal-tab-btn');
    const tabContents = document.querySelectorAll('.legal-tab-content');

    // Switch tabs
    const switchTab = (tabId) => {
        tabBtns.forEach(btn => btn.classList.remove('active'));
        tabContents.forEach(content => content.classList.remove('active'));
        
        const targetBtn = document.querySelector(`.legal-tab-btn[data-target="${tabId}"]`);
        const targetContent = document.getElementById(tabId);
        
        if (targetBtn && targetContent) {
            targetBtn.classList.add('active');
            targetContent.classList.add('active');
        }
    };

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.getAttribute('data-target');
            switchTab(tabId);
        });
    });

    // Open modal and set correct tab
    modalsBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const tabId = btn.getAttribute('data-tab') || 'tab-privacy';
            switchTab(tabId);
            modal.classList.add('active');
            backdrop.classList.add('active');
        });
    });

    // Close modal
    const close = () => {
        modal.classList.remove('active');
        backdrop.classList.remove('active');
    };

    if (closeBtn) closeBtn.addEventListener('click', close);
    if (backdrop) backdrop.addEventListener('click', close);
});
