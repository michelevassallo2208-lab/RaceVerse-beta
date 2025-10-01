<?php
require_once __DIR__ . '/../src/helpers.php';
include __DIR__ . '/../templates/header.php';
?>
<section class="rounded-3xl p-8 md:p-12 bg-gradient-to-br from-emerald-600/20 via-cyan-500/10 to-indigo-600/20 border border-white/10 shadow-2xl shadow-emerald-500/20">
  <div class="max-w-3xl space-y-5">
    <span class="inline-flex items-center gap-2 px-4 py-1 rounded-full bg-white/10 border border-white/20 text-xs uppercase tracking-[0.35em]">Upgrade PRO</span>
    <h1 class="text-4xl md:text-5xl font-black leading-tight">RaceVerse PRO a €2,99/mese</h1>
    <p class="text-white/70 text-lg">Potenzia il tuo account BASIC con report avanzati, setup certificati e supporto prioritario. Il checkout è in arrivo: intanto puoi scoprire tutti i vantaggi e prepararne l'attivazione.</p>
    <div class="flex flex-wrap gap-3">
      <a href="<?= asset('register.php') ?>" class="px-6 py-3 rounded-2xl bg-white text-black font-semibold shadow-lg shadow-white/40">Registrati gratis</a>
      <button class="px-6 py-3 rounded-2xl bg-white/10 border border-white/30 text-white/80 cursor-not-allowed" disabled>Pagamento online in arrivo</button>
    </div>
  </div>
</section>

<section class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 space-y-6">
    <div class="p-6 rounded-3xl bg-black/40 border border-white/10 shadow-xl shadow-emerald-500/10">
      <h2 class="text-2xl font-bold mb-4">Cosa include RaceVerse PRO</h2>
      <div class="grid md:grid-cols-2 gap-4 text-sm text-white/75">
        <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
          <div class="text-lg font-semibold text-white mb-2">Report dinamici</div>
          <p>Analisi comparative per categoria, pista e pilota con dati aggiornati dal tuo database.</p>
        </div>
        <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
          <div class="text-lg font-semibold text-white mb-2">Setup certificati</div>
          <p>Accesso ai setup sviluppati dal team RaceVerse per endurance e sprint.</p>
        </div>
        <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
          <div class="text-lg font-semibold text-white mb-2">Storico personale</div>
          <p>Tracking automatico dei tuoi hotlap con note, allegati e progresso nel tempo.</p>
        </div>
        <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
          <div class="text-lg font-semibold text-white mb-2">Supporto priority</div>
          <p>Linea diretta via email con risposte rapide del team per coaching e richieste setup.</p>
        </div>
      </div>
    </div>

    <div class="p-6 rounded-3xl bg-black/40 border border-white/10 shadow-xl shadow-indigo-500/10">
      <h2 class="text-2xl font-bold mb-4">Come funzionerà il pagamento</h2>
      <ol class="space-y-3 text-white/70 text-sm">
        <li class="flex gap-3"><span class="w-8 h-8 flex items-center justify-center rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-200">1</span> Selezioni il piano mensile o trimestrale direttamente da questa pagina.</li>
        <li class="flex gap-3"><span class="w-8 h-8 flex items-center justify-center rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-200">2</span> Completi il checkout sicuro (Stripe) con carta o wallet.</li>
        <li class="flex gap-3"><span class="w-8 h-8 flex items-center justify-center rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-200">3</span> L'account viene aggiornato istantaneamente a RaceVerse PRO e sblocca i contenuti premium.</li>
      </ol>
      <p class="mt-4 text-xs text-white/50">Nel frattempo puoi richiedere l'attivazione manuale scrivendo a <a href="mailto:support@raceverse.it" class="underline">support@raceverse.it</a>.</p>
    </div>
  </div>

  <div class="space-y-6">
    <div class="p-6 rounded-3xl bg-gradient-to-br from-emerald-500/30 via-teal-500/20 to-cyan-500/30 border border-emerald-400/40 shadow-xl shadow-emerald-500/30">
      <div class="text-xs uppercase tracking-[0.4em] text-white/70 mb-2">Confronto piani</div>
      <div class="bg-black/40 border border-white/10 rounded-2xl divide-y divide-white/10 text-sm">
        <div class="p-4 flex items-center justify-between">
          <span>RaceVerse BASIC</span>
          <span class="text-white/60">Gratis</span>
        </div>
        <div class="p-4 flex items-center justify-between">
          <span>RaceVerse PRO</span>
          <span class="text-white font-semibold">€2,99 / mese</span>
        </div>
      </div>
      <ul class="mt-4 space-y-2 text-sm text-white/80">
        <li>• Accesso BASIC immediato con conferma email.</li>
        <li>• Upgrade PRO disponibile post pagamento.</li>
        <li>• Disdici in qualsiasi momento senza penali.</li>
      </ul>
    </div>

    <div class="p-6 rounded-3xl bg-black/40 border border-white/10 shadow-xl shadow-purple-500/10 text-sm text-white/70">
      <h3 class="text-lg font-semibold text-white mb-3">Domande frequenti</h3>
      <p class="mb-3"><strong>Quando sarà attivo il checkout?</strong><br>Stiamo integrando Stripe: la pagina sarà disponibile entro poche settimane.</p>
      <p class="mb-3"><strong>Posso usare PRO senza pagare ora?</strong><br>Sì, contattaci e attiveremo una prova manuale finché il pagamento online non sarà pronto.</p>
      <p><strong>Il prezzo aumenterà?</strong><br>No, chi attiva ora manterrà €2,99/mese anche in futuro.</p>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../templates/footer.php'; ?>
