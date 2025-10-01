<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::start();
$user = Auth::user();
if ($user) { header('Location: /account.php'); exit; }
$loginError = null;
$registerError = null;
$registerEmail = '';
$activeTab = $_GET['tab'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $formType = $_POST['form_type'] ?? 'login';

  if ($formType === 'register') {
    $activeTab = 'register';
    $registerEmail = trim($_POST['register_email'] ?? '');
    $password = $_POST['register_password'] ?? '';
    $confirm = $_POST['register_password_confirmation'] ?? '';

    if ($password !== $confirm) {
      $registerError = 'Le password non coincidono.';
    } else {
      [$success, $message] = Auth::register($registerEmail, $password);
      if ($success) {
        header('Location: /account.php');
        exit;
      }
      $registerError = $message;
    }
  } else {
    $activeTab = 'login';
    if (Auth::login($_POST['email'] ?? '', $_POST['password'] ?? '')) {
      header('Location: /account.php');
      exit;
    }
    $loginError = 'Credenziali non valide';
  }
}
include __DIR__ . '/../templates/header.php';
?>
<section class="grid grid-cols-1 xl:grid-cols-[1.3fr_1fr] gap-10 items-start">
  <div class="space-y-8">
    <div class="p-6 md:p-10 rounded-3xl bg-gradient-to-br from-emerald-500/20 via-sky-500/15 to-purple-600/10 border border-white/10 shadow-2xl">
      <p class="uppercase tracking-[0.35em] text-xs text-white/60 mb-4">RaceVerse Performance Garage</p>
      <h1 class="text-4xl md:text-5xl font-black leading-tight mb-4">Un solo hub per scegliere l'auto perfetta e scaricare i setup dei pro.</h1>
      <p class="text-white/80 text-lg max-w-3xl">Analizziamo gli hotlap di Le Mans Ultimate, iRacing e ACC per mostrarti quale vettura domina ogni pista, in ogni categoria. Con il piano <strong>RaceVerse Pro</strong> sblocchi i setup ufficiali dei nostri coach per replicare la performance in pista.</p>
      <ul class="grid sm:grid-cols-2 gap-4 mt-8 text-sm text-white/80">
        <li class="flex items-start gap-3 p-4 rounded-2xl bg-black/40 border border-white/10"><span class="mt-1 w-2 h-2 rounded-full bg-emerald-400"></span><div><strong>Database hotlap live</strong><br>Ogni combinazione pista/auto aggiornata dai pro.</div></li>
        <li class="flex items-start gap-3 p-4 rounded-2xl bg-black/40 border border-white/10"><span class="mt-1 w-2 h-2 rounded-full bg-sky-400"></span><div><strong>Meta Advisor</strong><br>Consigli automatici sull'auto più competitiva.</div></li>
        <li class="flex items-start gap-3 p-4 rounded-2xl bg-black/40 border border-white/10"><span class="mt-1 w-2 h-2 rounded-full bg-amber-400"></span><div><strong>Setup esclusivi</strong><br>Scarica assetti pronti all'uso per ogni gioco supportato.</div></li>
        <li class="flex items-start gap-3 p-4 rounded-2xl bg-black/40 border border-white/10"><span class="mt-1 w-2 h-2 rounded-full bg-purple-400"></span><div><strong>Roadmap condivisa</strong><br>Vota le prossime piste da analizzare e i pacchetti setup.</div></li>
      </ul>
    </div>

    <div class="bg-white/3 border border-white/10 rounded-3xl p-6 md:p-8">
      <h2 class="text-2xl font-bold mb-1">Ruoli e privilegi</h2>
      <p class="text-sm text-white/70 mb-6">Ogni gruppo all'interno di RaceVerse ha accessi differenti. Scopri cosa sblocchi quando effettui il login.</p>
      <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-amber-400/40 bg-amber-500/10 p-5">
          <div class="text-xs uppercase tracking-[0.2em] text-amber-200">Admin</div>
          <div class="text-lg font-semibold mt-2">Gestione totale</div>
          <ul class="mt-3 space-y-2 text-sm text-amber-100/80">
            <li>• Inserisci e modifica auto</li>
            <li>• Aggiorna hotlap ufficiali</li>
            <li>• Carica i file assetto premium</li>
            <li>• Assegna ruoli e abbonamenti</li>
          </ul>
        </div>
        <div class="rounded-2xl border border-emerald-400/40 bg-emerald-500/10 p-5">
          <div class="text-xs uppercase tracking-[0.2em] text-emerald-200">RaceVerse Pro</div>
          <div class="text-lg font-semibold mt-2">Setup illimitati</div>
          <ul class="mt-3 space-y-2 text-sm text-emerald-100/80">
            <li>• Scarica ogni assetto disponibile</li>
            <li>• Accesso anticipato ai meta report</li>
            <li>• Telemetria e consigli personalizzati</li>
          </ul>
        </div>
        <div class="rounded-2xl border border-white/10 bg-black/50 p-5">
          <div class="text-xs uppercase tracking-[0.2em] text-white/60">RaceVerse Guest</div>
          <div class="text-lg font-semibold mt-2">Accesso gratuito</div>
          <ul class="mt-3 space-y-2 text-sm text-white/70">
            <li>• Consulta i migliori hotlap</li>
            <li>• Scopri l'auto più veloce per pista</li>
            <li>• Upgrade rapido per i setup premium</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="sticky top-28">
    <div class="rounded-3xl p-8 bg-white/10 border border-white/20 shadow-xl backdrop-blur">
      <div class="flex items-center gap-3 mb-6">
        <img src="/assets/images/logo.png" class="w-12 h-12" alt="logo">
        <div>
          <p class="text-xs uppercase tracking-[0.3em] text-white/60">RaceVerse Access</p>
          <h2 class="text-2xl font-bold">Accedi o crea un profilo</h2>
        </div>
      </div>

      <div class="flex mb-6 rounded-2xl bg-black/30 border border-white/10 p-1" data-auth-tabs>
        <button type="button" data-auth-tab="login" class="flex-1 px-4 py-2 rounded-2xl text-sm font-semibold <?= $activeTab === 'register' ? 'text-white/70' : 'bg-white text-black shadow-lg' ?>">Login</button>
        <button type="button" data-auth-tab="register" class="flex-1 px-4 py-2 rounded-2xl text-sm font-semibold <?= $activeTab === 'register' ? 'bg-white text-black shadow-lg' : 'text-white/70' ?>">Registrati</button>
      </div>

      <div data-auth-panel="login" class="space-y-4 <?= $activeTab === 'register' ? 'hidden' : '' ?>">
        <?php if ($loginError): ?>
          <div class="p-3 rounded-xl bg-red-500/15 border border-red-500/25 text-red-200 text-sm"><?= htmlspecialchars($loginError) ?></div>
        <?php endif; ?>
        <form method="post" class="space-y-4">
          <input type="hidden" name="form_type" value="login">
          <div>
            <label class="block text-sm mb-1 text-white/70">Email</label>
            <input type="email" name="email" class="w-full p-3 rounded-2xl bg-black/40 border border-white/15 focus:border-emerald-400/60 focus:outline-none" placeholder="nome@raceverse.gg" required>
          </div>
          <div>
            <label class="block text-sm mb-1 text-white/70">Password</label>
            <input type="password" name="password" class="w-full p-3 rounded-2xl bg-black/40 border border-white/15 focus:border-emerald-400/60 focus:outline-none" placeholder="••••••••" required>
          </div>
          <button class="w-full py-3 rounded-2xl bg-emerald-400 text-black font-semibold text-sm uppercase tracking-[0.2em]">Entra in RaceVerse</button>
        </form>
        <div class="text-xs text-white/60 leading-relaxed">
          Accedendo con un profilo <strong>RaceVerse Pro</strong> avrai download illimitati degli assetti.
        </div>
      </div>

      <div data-auth-panel="register" class="space-y-4 <?= $activeTab === 'register' ? '' : 'hidden' ?>">
        <?php if ($registerError): ?>
          <div class="p-3 rounded-xl bg-red-500/15 border border-red-500/25 text-red-200 text-sm"><?= htmlspecialchars($registerError) ?></div>
        <?php endif; ?>
        <form method="post" class="space-y-4">
          <input type="hidden" name="form_type" value="register">
          <div>
            <label class="block text-sm mb-1 text-white/70">Email</label>
            <input type="email" name="register_email" value="<?= htmlspecialchars($registerEmail) ?>" class="w-full p-3 rounded-2xl bg-black/40 border border-white/15 focus:border-emerald-400/60 focus:outline-none" placeholder="nome@raceverse.gg" required>
          </div>
          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="block text-sm mb-1 text-white/70">Password</label>
              <input type="password" name="register_password" class="w-full p-3 rounded-2xl bg-black/40 border border-white/15 focus:border-emerald-400/60 focus:outline-none" placeholder="Minimo 8 caratteri" required>
            </div>
            <div>
              <label class="block text-sm mb-1 text-white/70">Conferma password</label>
              <input type="password" name="register_password_confirmation" class="w-full p-3 rounded-2xl bg-black/40 border border-white/15 focus:border-emerald-400/60 focus:outline-none" placeholder="Ripeti la password" required>
            </div>
          </div>
          <div class="text-xs text-white/60 bg-black/30 border border-white/10 rounded-2xl p-4">
            La registrazione crea un profilo <strong>RaceVerse Guest</strong> gratuito. Potrai passare a <a class="text-emerald-300 underline" href="/abbonamenti.php">RaceVerse Pro</a> per sbloccare gli assetti premium.
          </div>
          <button class="w-full py-3 rounded-2xl bg-emerald-400 text-black font-semibold text-sm uppercase tracking-[0.2em]">Crea il tuo account gratuito</button>
        </form>
      </div>

      <div class="mt-6 text-sm text-white/60 space-y-2">
        <p>Abbonamento completo a <strong>5,00€/mese</strong> per scaricare tutti gli assetti. Vuoi un singolo setup? Disponibile a <strong>1,99€</strong>.</p>
        <p class="text-white/50">L'accesso agli assetti è riservato a chi possiede un piano attivo RaceVerse Pro.</p>
      </div>
    </div>
  </div>
</section>
<script>
document.querySelectorAll('[data-auth-tab]').forEach((button) => {
  button.addEventListener('click', () => {
    const target = button.getAttribute('data-auth-tab');
    document.querySelectorAll('[data-auth-panel]').forEach((panel) => {
      panel.classList.toggle('hidden', panel.getAttribute('data-auth-panel') !== target);
    });
    document.querySelectorAll('[data-auth-tab]').forEach((btn) => {
      btn.classList.remove('bg-white', 'text-black', 'shadow-lg');
      btn.classList.add('text-white/70');
    });
    button.classList.add('bg-white', 'text-black', 'shadow-lg');
    button.classList.remove('text-white/70');
  });
});
</script>
<?php include __DIR__ . '/../templates/footer.php'; ?>
