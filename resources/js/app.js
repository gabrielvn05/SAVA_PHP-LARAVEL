import './bootstrap';

function registerPwa() {
    if (!('serviceWorker' in navigator)) return;
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}

function showLoading(label = 'Cargando…') {
    if (document.querySelector('.loading-overlay')) return;
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = `<div class="loading-overlay__panel"><div class="loading-overlay__spinner" aria-hidden="true"></div><span>${label}</span></div>`;
    document.body.appendChild(overlay);
}

function initSidebar() {
    const shell = document.querySelector('[data-app-shell]');
    if (!shell) return;

    const toggle = shell.querySelector('[data-sidebar-toggle]');
    const panel = shell.querySelector('[data-sidebar-panel]');
    const backdrop = shell.querySelector('[data-sidebar-backdrop]');
    const html = document.documentElement;

    function setOpen(open) {
        panel?.classList.toggle('is-open', open);
        backdrop?.classList.toggle('is-visible', open);
        backdrop?.setAttribute('aria-hidden', open ? 'false' : 'true');
        toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle?.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
        html.classList.toggle('sidebar-open', open);
    }

    toggle?.addEventListener('click', () => setOpen(!panel?.classList.contains('is-open')));
    backdrop?.addEventListener('click', () => setOpen(false));

    document.querySelectorAll('[data-nav-group]').forEach((group) => {
        const btn = group.querySelector('[data-nav-group-btn]');
        const sub = group.querySelector('[data-nav-group-sub]');
        const chevron = group.querySelector('.sidebar-nav__chevron');
        btn?.addEventListener('click', () => {
            const open = btn.getAttribute('aria-expanded') !== 'true';
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (sub) sub.hidden = !open;
            if (chevron) chevron.style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
        });
    });

    const modal = document.querySelector('[data-logout-modal]');
    const openBtn = document.querySelector('[data-logout-open]');
    function setLogout(open) {
        if (!modal) return;
        modal.hidden = !open;
        modal.setAttribute('aria-hidden', open ? 'false' : 'true');
    }
    openBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        setLogout(true);
    });
    modal?.querySelectorAll('[data-logout-close]').forEach((el) => {
        el.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            setLogout(false);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        if (modal && !modal.hidden) setLogout(false);
        else setOpen(false);
    });

    const media = window.matchMedia('(min-width: 1024px)');
    media.addEventListener('change', () => {
        if (media.matches) setOpen(false);
    });
}

function initPasswordToggles() {
    document.querySelectorAll('[data-password-toggle]').forEach((btn) => {
        const wrap = btn.closest('.password-input-wrap');
        const input = wrap?.querySelector('input');
        if (!input) return;
        btn.addEventListener('click', () => {
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            btn.setAttribute('aria-pressed', visible ? 'false' : 'true');
            btn.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
        });
    });
}

function initLoadingForms() {
    document.querySelectorAll('form:not([data-no-loading])').forEach((form) => {
        form.addEventListener('submit', () => {
            const label = form.getAttribute('data-loading-label') || 'Procesando…';
            showLoading(label);
        });
    });
}

function initWizard() {
    const cards = document.querySelectorAll('.wizard-tipo-card');
    const tipoInput = document.getElementById('tipo-input');
    const panelTipo = document.getElementById('panel-tipo');
    const panelDatos = document.getElementById('panel-datos');
    const progress = document.getElementById('wizard-progress-step-1');
    const continueBtn = document.querySelector('[data-wizard-continue]');
    const backBtn = document.querySelector('[data-wizard-back]');
    const anexoLabel = document.getElementById('anexo-label');
    const instTipo = document.getElementById('institucion-medica-tipo');
    if (!cards.length || !tipoInput) return;

    function showFields(tipo) {
        document.querySelectorAll('.wizard-field').forEach((el) => {
            el.hidden = true;
        });
        const map = {
            enfermedad: ['inasistencia', 'enfermedad', 'motivo'],
            calamidad_domestica: ['inasistencia', 'calamidad', 'motivo'],
            viaje: ['viaje', 'motivo'],
            falta_marcado: ['falta', 'motivo'],
            permiso: ['fechas', 'permiso', 'motivo'],
            justificacion: ['fechas', 'motivo'],
        };
        (map[tipo] || ['fechas', 'motivo']).forEach((key) => {
            document.querySelectorAll(`.wizard-field--${key}`).forEach((el) => { el.hidden = false; });
        });
        if (anexoLabel) {
            anexoLabel.textContent = tipo === 'enfermedad'
                ? 'Adjuntar certificado o soporte médico *'
                : 'Adjuntar documento de respaldo (opcional)';
        }
        syncInstitucionNombre();
    }

    function syncInstitucionNombre() {
        const wrap = document.querySelector('.wizard-field--institucion-nombre');
        if (!wrap || !instTipo) return;
        const show = !document.querySelector('.wizard-field--enfermedad')?.hidden
            && instTipo.value
            && instTipo.value !== 'IESS';
        wrap.hidden = !show;
    }

    function selectTipo(tipo, card) {
        cards.forEach((c) => c.classList.remove('wizard-tipo-card--active', 'is-selected'));
        card.classList.add('wizard-tipo-card--active', 'is-selected');
        tipoInput.value = tipo;
        if (continueBtn) continueBtn.disabled = false;
        showFields(tipo);
    }

    function setStep(step) {
        if (panelTipo) panelTipo.hidden = step !== 0;
        if (panelDatos) panelDatos.hidden = step !== 1;
        if (progress) progress.classList.toggle('is-done', step >= 1);
    }

    cards.forEach((card) => {
        card.addEventListener('click', () => selectTipo(card.dataset.tipo, card));
    });
    continueBtn?.addEventListener('click', () => {
        if (tipoInput.value) setStep(1);
    });
    backBtn?.addEventListener('click', () => setStep(0));
    instTipo?.addEventListener('change', syncInstitucionNombre);

    if (tipoInput.value) {
        const selected = document.querySelector(`[data-tipo="${tipoInput.value}"]`);
        if (selected) {
            selectTipo(tipoInput.value, selected);
            setStep(1);
        }
    }
}

registerPwa();
document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initPasswordToggles();
    initLoadingForms();
    initWizard();
});
