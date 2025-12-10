<h2><?= htmlspecialchars($title) ?></h2>
<p><?= htmlspecialchars($content) ?></p>

<?php if (!empty($is_logged_in)): ?>
    <p>Vous êtes connecté !</p>
<?php else: ?>
    <p>Vous n'êtes pas connecté.</p>
    <p>
        <a href="/login">Se connecter</a> |
        <a href="/register">Créer un compte</a>
    </p>
<?php endif; ?>

<?php if (!empty($flash)): ?>
    <p style="color:green"><?= htmlspecialchars($flash) ?></p>
<?php endif; ?>
