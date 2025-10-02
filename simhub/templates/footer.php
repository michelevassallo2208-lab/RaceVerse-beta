<?php
require_once __DIR__ . '/../src/helpers.php';
?>
</main>
<footer class="border-t border-white/10 bg-black/30 backdrop-blur-xl">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10 grid gap-8 md:grid-cols-3 text-sm">
    <div class="space-y-3">
      <div class="flex items-center gap-3">
        <img src="<?= asset('assets/images/logo.png') ?>" class="w-9 h-9" alt="logo">
        <div>
          <div class="text-lg font-semibold">RaceVerse</div>
          <div class="text-xs text-white/50">RaceVerse BASIC gratuito, pass PRO da €2,99</div>
        </div>
      </div>
      <p class="text-white/60 leading-relaxed">Hotlap curati, analisi affidabili e strumenti concreti per scalare le classifiche.</p>
    </div>
    <div>
      <h4 class="text-white font-semibold mb-3">Esplora</h4>
      <ul class="space-y-2 text-white/70">
        <li><a class="hover:text-white transition" href="<?= asset('index.php') ?>#insights">Insights &amp; analisi</a></li>
        <li><a class="hover:text-white transition" href="<?= asset('payment.php') ?>">Pass e Accesso PRO</a></li>
        <li><a class="hover:text-white transition" href="<?= asset('register.php') ?>">Crea account BASIC</a></li>
        <li><a class="hover:text-white transition" href="<?= asset('login.php') ?>">Accedi</a></li>
        <li><a class="hover:text-white transition" href="<?= asset('support-guest.php') ?>">Supporto &amp; ticket</a></li>
      </ul>
    </div>
    <div class="space-y-3">
      <h4 class="text-white font-semibold">Resta aggiornato</h4>
      <p class="text-white/60 text-sm">Iscriviti alla newsletter per ricevere setup mirati e aggiornamenti dai campionati.</p>
      <form id="newsletter-form" action="<?= asset('newsletter.php') ?>" method="post" class="flex gap-2" novalidate>
        <input type="email" name="email" placeholder="La tua email" required class="flex-1 px-4 py-2 rounded-xl bg-white/10 border border-white/20 placeholder:text-white/50 focus:outline-none focus:border-indigo-400">
        <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500 text-black font-semibold shadow-lg shadow-indigo-500/20 transition disabled:opacity-60 disabled:cursor-not-allowed">Unisciti</button>
      </form>
      <p id="newsletter-feedback" class="text-sm font-medium hidden"></p>
    </div>
  </div>
  <div class="border-t border-white/10 text-xs text-white/50 py-4 text-center">© <?= date('Y') ?> RaceVerse. Tutti i diritti riservati.</div>
</footer>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('newsletter-form');
  if (!form) return;
  const feedback = document.getElementById('newsletter-feedback');
  const submitBtn = form.querySelector('button[type="submit"]');
  const emailField = form.querySelector('input[name="email"]');
  const bodyEl = document.body || document.getElementsByTagName('body')[0];
  const baseUrl = bodyEl && bodyEl.dataset ? (bodyEl.dataset.baseUrl || '') : '';

  form.addEventListener('submit', async function (event) {
    event.preventDefault();
    if (!emailField || !emailField.value.trim()) {
      showMessage('Inserisci un indirizzo email valido.', false);
      if (emailField) {
        emailField.focus();
      }
      return;
    }

    submitBtn.disabled = true;
    showMessage('Invio in corso…', true, true);

    const endpoint = form.getAttribute('action') || (baseUrl ? baseUrl + '/newsletter.php' : 'newsletter.php');

    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
        body: new URLSearchParams({ email: emailField.value.trim() })
      });

      const result = await response.json();
      if (result.success) {
        showMessage(result.message || 'Iscrizione completata!', true);
        form.reset();
      } else {
        showMessage(result.message || 'Non è stato possibile completare l\'iscrizione.', false);
      }
    } catch (error) {
      showMessage('Connessione non disponibile al momento. Riprova più tardi.', false);
    } finally {
      submitBtn.disabled = false;
    }
  });

  function showMessage(message, isSuccess, isNeutral) {
    if (!feedback) return;
    feedback.textContent = message;
    feedback.classList.remove('hidden');
    feedback.classList.remove('text-emerald-300', 'text-red-300', 'text-indigo-200');
    if (isNeutral) {
      feedback.classList.add('text-indigo-200');
    } else {
      feedback.classList.add(isSuccess ? 'text-emerald-300' : 'text-red-300');
    }
  }
});
</script>
</body>
</html>
