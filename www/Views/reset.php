<h2>Réinitialiser mot de passe</h2>
<form method="POST" action="/reset-password-post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
    <input type="password" name="password" placeholder="Nouveau mot de passe" required>
    <input type="password" name="passwordConfirm" placeholder="Confirmer mot de passe" required>

    <button type="submit">Changer</button>
</form>
