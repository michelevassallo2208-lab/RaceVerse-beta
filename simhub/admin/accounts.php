<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
Auth::start();
if (!Auth::isAdmin()) {
    header('Location: /login.php');
    exit;
}
$pdo = Database::pdo();
$feedback = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)($_POST['user_id'] ?? 0);
    if ($userId > 0) {
        if (($_POST['action'] ?? '') === 'role') {
            $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
            $st = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
            $st->execute([$role, $userId]);
            $feedback = 'Ruolo aggiornato con successo.';
        } elseif (($_POST['action'] ?? '') === 'subscription') {
            $plan = trim($_POST['plan'] ?? '');
            $active = isset($_POST['active']) ? 1 : 0;
            $st = $pdo->prepare('UPDATE users SET subscription_plan = ?, subscription_active = ? WHERE id = ?');
            $st->execute([$plan ?: null, $active, $userId]);
            $feedback = 'Stato abbonamento aggiornato.';
        }
    }
}
$users = $pdo->query('SELECT id,email,first_name,last_name,role,subscription_plan,subscription_active,created_at FROM users ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestione account • Raceverse</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
  <div class="max-w-6xl mx-auto py-10 px-4">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-3xl font-bold">Gestione account</h1>
      <a href="/dashboard.php" class="text-sm underline">← Torna alla dashboard</a>
    </div>
    <?php if ($feedback): ?>
      <div class="mb-4 p-3 rounded bg-emerald-100 text-emerald-800 border border-emerald-300 text-sm"><?= htmlspecialchars($feedback) ?></div>
    <?php endif; ?>
    <div class="overflow-x-auto bg-white shadow rounded-2xl">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Utente</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Ruolo</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Abbonamento</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Azioni</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <?php foreach ($users as $u): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm">
                <div class="font-semibold text-gray-900"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></div>
                <div class="text-gray-500 text-xs"><?= htmlspecialchars($u['email']) ?></div>
                <div class="text-gray-400 text-xs">Registrato il <?= htmlspecialchars(substr($u['created_at'],0,10)) ?></div>
              </td>
              <td class="px-4 py-3 text-sm">
                <form method="post" class="flex items-center gap-2">
                  <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                  <input type="hidden" name="action" value="role">
                  <select name="role" class="border rounded-lg px-3 py-1 text-sm">
                    <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                  </select>
                  <button class="px-3 py-1 rounded-lg bg-indigo-600 text-white text-xs">Salva</button>
                </form>
              </td>
              <td class="px-4 py-3 text-sm">
                <form method="post" class="flex flex-col gap-2">
                  <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                  <input type="hidden" name="action" value="subscription">
                  <input type="text" name="plan" placeholder="Nome piano" value="<?= htmlspecialchars($u['subscription_plan'] ?? '') ?>" class="border rounded-lg px-3 py-1 text-sm">
                  <label class="text-xs text-gray-600 flex items-center gap-2">
                    <input type="checkbox" name="active" <?= $u['subscription_active'] ? 'checked' : '' ?>> Attivo
                  </label>
                  <button class="self-start px-3 py-1 rounded-lg bg-indigo-600 text-white text-xs">Aggiorna</button>
                </form>
              </td>
              <td class="px-4 py-3 text-xs text-gray-500">
                <div>Ruolo attuale: <strong><?= htmlspecialchars($u['role']) ?></strong></div>
                <div>Piano: <strong><?= htmlspecialchars($u['subscription_plan'] ?: 'Nessuno') ?></strong></div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
