@once
@push('scripts')
<style>[data-auth-flash]{transition:opacity .5s ease;opacity:1}[data-auth-flash].is-fading{opacity:0}.pw-wrap{position:relative}.pw-wrap input[type=password],.pw-wrap input[type=text]{padding-right:44px!important;width:100%}.pw-toggle{position:absolute;right:8px;top:50%;transform:translateY(-50%);border:1px solid #d1d5db;background:#fff;border-radius:8px;padding:6px 9px;cursor:pointer;line-height:1}</style>
<script>
(function(){
  document.querySelectorAll('[data-pw-toggle]').forEach(function(btn){
    var input = document.getElementById(btn.getAttribute('data-pw-toggle'));
    if(!input) return;
    btn.addEventListener('click', function(){
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-pressed', String(show));
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      btn.textContent = show ? '🙈' : '👁';
    });
  });
  document.querySelectorAll('[data-auth-flash]').forEach(function(el){
    setTimeout(function(){
      el.classList.add('is-fading');
      setTimeout(function(){ el.remove(); }, 550);
    }, 5000);
  });
})();
</script>
@endpush
@endonce
