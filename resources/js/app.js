import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const portalSidebar = document.getElementById('portalSidebar');
    if (!portalSidebar) return;

    const publicDonatePath = '/donate';
    const portalDonatePath = '/my-contributions';

    document.querySelectorAll(`a[href$="${publicDonatePath}"]`).forEach((link) => {
        link.setAttribute('href', portalDonatePath);
        link.removeAttribute('target');
        link.removeAttribute('rel');
    });
});
