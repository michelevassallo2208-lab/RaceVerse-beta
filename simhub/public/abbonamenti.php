<?php
require_once __DIR__ . '/../src/Auth.php';
Auth::start();
$currentUser = Auth::user();
include __DIR__ . '/../templates/header.php';
?>
<section class="space-y-12">
  <div class="rounded-3xl p-10 bg-gradient-to-br from-emerald-500/20 via-sky-500/10 to-purple-500/10 border border-white/10 shadow-2xl">
    <div class="grid gap-8 lg:grid-cols-[1.2fr_1fr] items-center">
      <div class="space-y-4">
        <p class="uppercase tracking-[0.35em] text-xs text-white/60">RaceVerse Membership</p>
        <h1 class="text-4xl md:text-5xl font-black leading-tight">Sblocca l'intero garage con RaceVerse Pro.</h1>
        <p class="text-white/80 text-lg">Hotlap sempre aggiornati e un servizio di setup premium pensato per Le Mans Ultimate, iRacing e ACC. Con il piano RaceVerse Pro ricevi accesso immediato agli assetti di ogni combinazione pista/auto curata dai nostri coach.</p>
        <div class="flex flex-wrap gap-3 text-sm text-white/70">
          <span class="px-4 py-2 rounded-full border border-white/20 bg-black/40">Hotlap Meta Advisor</span>
          <span class="px-4 py-2 rounded-full border border-white/20 bg-black/40">Setup Pro telemetrati</span>
          <span class="px-4 py-2 rounded-full border border-white/20 bg-black/40">Aggiornamenti mensili inclusi</span>
        </div>
      </div>
      <div class="rounded-3xl bg-white text-black p-6 md:p-8 shadow-xl">
        <p class="text-sm uppercase tracking-[0.25em] text-emerald-600 mb-3">RaceVerse Pro</p>
        <div class="flex items-baseline gap-2">
          <span class="text-5xl font-black">5€</span>
          <span class="text-sm font-semibold">/ mese</span>
        </div>
        <p class="mt-4 text-sm text-gray-700">Download illimitati, supporto prioritario e roadmap condivisa con il team di ingegneri RaceVerse.</p>
        <ul class="mt-6 space-y-3 text-sm text-gray-800">
          <li class="flex items-start gap-3"><span class="mt-1 w-2 h-2 rounded-full bg-emerald-500"></span>Setup completi per qualifica e gara</li>
          <li class="flex items-start gap-3"><span class="mt-1 w-2 h-2 rounded-full bg-emerald-500"></span>Accesso anticipato a nuove piste e categorie</li>
          <li class="flex items-start gap-3"><span class="mt-1 w-2 h-2 rounded-full bg-emerald-500"></span>Report meta cross-game aggiornati ogni settimana</li>
        </ul>
        <a href="<?= $currentUser ? '/account.php' : '/login.php?tab=register' ?>" class="mt-6 inline-flex justify-center items-center w-full py-3 rounded-2xl bg-emerald-500 text-black font-semibold uppercase tracking-[0.2em]">Attiva RaceVerse Pro</a>
        <p class="mt-4 text-xs text-gray-500 text-center">Cancella quando vuoi. Assetti futuri inclusi durante il periodo attivo.</p>
      </div>
    </div>
  </div>

  <div class="rounded-3xl border border-white/10 bg-black/40 p-8 md:p-10 space-y-8">
    <div>
      <h2 class="text-3xl font-bold">Confronto dei piani</h2>
      <p class="text-white/70 mt-2">Scegli il livello di accesso più adatto: gratuito per consultare la meta, Pro per scaricare tutto, oppure acquista singolarmente un setup specifico.</p>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead class="text-white/60 uppercase tracking-[0.2em] text-xs">
          <tr>
            <th class="py-3 px-4"></th>
            <th class="py-3 px-4">RaceVerse Guest</th>
            <th class="py-3 px-4">RaceVerse Pro</th>
            <th class="py-3 px-4">Setup singolo</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/10">
          <tr>
            <td class="py-4 px-4 text-white/70">Prezzo</td>
            <td class="py-4 px-4">Gratis</td>
            <td class="py-4 px-4">5€/mese</td>
            <td class="py-4 px-4">1,99€ una tantum</td>
          </tr>
          <tr>
            <td class="py-4 px-4 text-white/70">Hotlap migliori auto/pista</td>
            <td class="py-4 px-4">✔</td>
            <td class="py-4 px-4">✔</td>
            <td class="py-4 px-4">✔</td>
          </tr>
          <tr>
            <td class="py-4 px-4 text-white/70">Download assetti completi</td>
            <td class="py-4 px-4">—</td>
            <td class="py-4 px-4">✔ Illimitati</td>
            <td class="py-4 px-4">✔ Solo per la combo acquistata</td>
          </tr>
          <tr>
            <td class="py-4 px-4 text-white/70">Aggiornamenti automatici</td>
            <td class="py-4 px-4">—</td>
            <td class="py-4 px-4">✔ Compresi nel mese</td>
            <td class="py-4 px-4">—</td>
          </tr>
          <tr>
            <td class="py-4 px-4 text-white/70">Supporto tecnico prioritario</td>
            <td class="py-4 px-4">—</td>
            <td class="py-4 px-4">✔</td>
            <td class="py-4 px-4">Supporto via email 72h</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="grid gap-6 md:grid-cols-3">
    <div class="rounded-3xl border border-white/10 bg-black/50 p-6 space-y-3">
      <h3 class="text-xl font-semibold">Perché restare Guest</h3>
      <p class="text-sm text-white/70">Perfetto se vuoi monitorare gratuitamente il meta delle auto e capire dove investire tempo in pista.</p>
      <ul class="text-sm text-white/60 space-y-2">
        <li>• Accesso immediato dopo la registrazione</li>
        <li>• Hotlap dei pro per ogni pista</li>
        <li>• Suggerimenti auto e categorie</li>
      </ul>
    </div>
    <div class="rounded-3xl border border-emerald-400/40 bg-emerald-500/10 p-6 space-y-3">
      <h3 class="text-xl font-semibold">Quando passare a Pro</h3>
      <p class="text-sm text-emerald-50/80">Se vuoi copiare setup vincenti e ricevere aggiornamenti costanti per ogni gioco supportato.</p>
      <ul class="text-sm text-emerald-100/80 space-y-2">
        <li>• Assetti pronta gara e qualifica</li>
        <li>• Telemetria e note di guida dei coach</li>
        <li>• Voto prioritario sulle prossime release</li>
      </ul>
    </div>
    <div class="rounded-3xl border border-white/10 bg-black/50 p-6 space-y-3">
      <h3 class="text-xl font-semibold">Acquisto singolo</h3>
      <p class="text-sm text-white/70">Hai bisogno di un solo assetto per un evento specifico? Paghi solo ciò che ti serve, senza abbonamento.</p>
      <ul class="text-sm text-white/60 space-y-2">
        <li>• File setup e guida rapida</li>
        <li>• Aggiornamento garantito 30 giorni</li>
        <li>• Possibilità di upgrade al Pro scalando il costo</li>
      </ul>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../templates/footer.php'; ?>
