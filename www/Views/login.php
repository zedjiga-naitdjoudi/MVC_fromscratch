<h2>Connexion</h2>
<?php if (!empty($_SESSION['activation_message'])): ?>
    <p style="color: green;">
        <?= htmlspecialchars($_SESSION['activation_message']) ?>
    </p>
    <?php unset($_SESSION['activation_message']); ?>
<?php endif; ?>

<?php if (!empty($error)): ?><p><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="POST" action="/login">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <button type="submit">Se connecter</button>
</form>
<p><a href="/forgot">Mot de passe oublié ?</a></p>
