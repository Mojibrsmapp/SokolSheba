(() => {
    'use strict';
    if (window.sokoShellReady) return;
    window.sokoShellReady = true;
    let active = null, opener = null, oldOverflow = '';
    const close = () => {
        if (!active) return;
        active.hidden = true;
        document.body.style.overflow = oldOverflow;
        opener?.setAttribute('aria-expanded', 'false');
        opener?.focus();
        active = null;
    };
    document.querySelectorAll('[data-ss-open]').forEach(button => button.addEventListener('click', () => {
        close();
        opener = button;
        active = document.getElementById(button.dataset.ssOpen);
        if (!active) return;
        oldOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        active.hidden = false;
        opener.setAttribute('aria-expanded', 'true');
        active.querySelector('[role="dialog"]').focus();
    }));
    document.querySelectorAll('[data-ss-close]').forEach(button => button.addEventListener('click', close));
    document.querySelectorAll('.ss-overlay').forEach(overlay => overlay.addEventListener('click', event => { if (event.target === overlay) close(); }));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') { close(); setSupport(false); }
        if (event.key !== 'Tab' || !active) return;
        const focusable = [...active.querySelectorAll('a[href],button,[tabindex="0"]')];
        const first = focusable[0], last = focusable[focusable.length - 1];
        if (event.shiftKey && (document.activeElement === first || document.activeElement.matches('[role="dialog"]'))) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && (document.activeElement === last || document.activeElement.matches('[role="dialog"]'))) { event.preventDefault(); first.focus(); }
    });
    const fab = document.getElementById('ss-support');
    const toggle = document.getElementById('ss-support-toggle');
    const panel = document.getElementById('ss-support-panel');
    if (!fab || !toggle || !panel) return;
    const bottomClearance = document.querySelector('.ss-footer') ? 160 : 78;
    let drag = null, moved = false;
    function positionPanel() {
        const rect = fab.getBoundingClientRect();
        panel.style.right = rect.right < panel.offsetWidth + 12 ? 'auto' : '0';
        panel.style.left = rect.right < panel.offsetWidth + 12 ? '0' : 'auto';
        panel.style.bottom = rect.top < panel.offsetHeight + 80 ? 'auto' : '68px';
        panel.style.top = rect.top < panel.offsetHeight + 80 ? '68px' : 'auto';
    }
    function setSupport(open) { panel.hidden = !open; toggle.setAttribute('aria-expanded', String(open)); if (open) positionPanel(); }
    toggle.addEventListener('pointerdown', event => {
        if (event.button !== 0) return;
        const rect = fab.getBoundingClientRect();
        drag = { x: event.clientX, y: event.clientY, left: rect.left, top: rect.top };
        moved = false;
        toggle.setPointerCapture(event.pointerId);
    });
    toggle.addEventListener('pointermove', event => {
        if (!drag) return;
        const dx = event.clientX - drag.x, dy = event.clientY - drag.y;
        if (Math.abs(dx) + Math.abs(dy) < 6 && !moved) return;
        moved = true;
        setSupport(false);
        fab.style.left = Math.max(12, Math.min(innerWidth - 70, drag.left + dx)) + 'px';
        fab.style.top = Math.max(80, Math.min(innerHeight - bottomClearance, drag.top + dy)) + 'px';
        fab.style.right = 'auto'; fab.style.bottom = 'auto';
    });
    toggle.addEventListener('pointerup', () => { drag = null; });
    toggle.addEventListener('pointercancel', () => { drag = null; moved = true; });
    toggle.addEventListener('click', () => { if (!moved) setSupport(panel.hidden); moved = false; });
    document.addEventListener('click', event => { if (!fab.contains(event.target)) setSupport(false); });
    window.addEventListener('resize', () => { fab.style.left = ''; fab.style.top = ''; fab.style.right = ''; fab.style.bottom = ''; setSupport(false); });
})();
