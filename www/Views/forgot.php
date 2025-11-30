<h2>Mot de passe oublié</h2>
<form method="POST" action="/forgot-post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <input type="email" name="email" placeholder="Votre email" required>
    <button type="submit">Envoyer</button>
</form>
