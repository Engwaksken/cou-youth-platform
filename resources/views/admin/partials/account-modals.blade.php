@push('styles')
<style>
    .user-dialog{position:fixed;inset:0;margin:auto;height:fit-content;max-height:calc(100vh - 32px)}
    .users-table-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px;border-bottom:1px solid var(--border)}.users-table-heading h2{font-size:1.125rem;margin:0}
    .users-table{min-width:620px}.users-table td{vertical-align:middle}.user-identity{display:flex;align-items:center;gap:12px}.user-avatar{display:grid;place-items:center;flex:0 0 40px;height:40px;border-radius:12px;background:var(--primary-light);color:var(--primary);font-weight:800}.user-identity small{display:block;margin-top:4px;color:var(--muted)}.user-email{overflow-wrap:anywhere}.users-pagination{padding:0 18px 18px}
    .user-dialog{width:min(600px,calc(100% - 28px));max-height:calc(100dvh - 32px);padding:24px;border:1px solid var(--border);border-radius:16px;color:var(--text);background:var(--surface);overflow:auto;box-shadow:0 24px 80px rgba(16,24,40,.25)}.user-dialog::backdrop{background:rgba(15,23,42,.6)}.user-dialog-confirm{max-width:460px}.user-dialog-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px}.user-dialog-heading h2{margin:0;font-size:1.375rem}.user-dialog-heading p,.user-password-help{color:var(--muted);font-size:0.875rem;margin:6px 0 0}.user-dialog .icon-btn{flex-shrink:0}.user-dialog .modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:24px}.user-field-error{display:block;color:var(--danger);font-size:0.8125rem;margin-top:5px}.users-sr-only{position:absolute;width:1px;height:1px;padding:0;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap}
    @media(max-width:600px){.user-dialog{padding:18px}.user-dialog .form-grid{grid-template-columns:1fr}.users-table .btn{min-height:44px}.user-dialog .modal-actions{flex-wrap:wrap}.user-dialog .modal-actions .btn{flex:1;min-height:44px}}
</style>
@endpush

@push('scripts')
<script>
(() => {
    let previousOverflow = '';
    const dialogs = document.querySelectorAll('.user-dialog');
    const open = dialog => {
        if (!dialog || dialog.open) return;
        previousOverflow = document.body.style.overflow;
        dialog.showModal();
        document.body.style.overflow = 'hidden';
    };
    document.querySelectorAll('[data-user-dialog]').forEach(button => {
        button.addEventListener('click', () => open(document.getElementById(button.dataset.userDialog)));
    });
    dialogs.forEach(dialog => {
        dialog.querySelectorAll('[data-user-dialog-close]').forEach(button => button.addEventListener('click', () => dialog.close()));
        dialog.addEventListener('close', () => { document.body.style.overflow = previousOverflow; });
        dialog.addEventListener('click', event => {
            const bounds = dialog.getBoundingClientRect();
            if (event.target === dialog && (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom)) dialog.close();
        });
    });
    open(document.querySelector('.user-dialog[data-auto-open]'));
})();
</script>
@endpush
