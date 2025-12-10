<h2>Connexion</h2>

<?php if (!empty($_SESSION['activation_message'])): ?>
    <p style="color: green;">
        <?= htmlspecialchars($_SESSION['activation_message']) ?>
    </p>
    <?php unset($_SESSION['activation_message']); ?>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="/login-post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <button type="submit">Se connecter</button>
</form>

<p><a href="/forgot">Mot de passe oublié ?</a></p>
<p><a href="/">Retour à l'accueil</a> | <a href="/register">Créer un compte</a></p>
