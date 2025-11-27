<h2><?= $title ?></h2>
<p><?= $content ?></p>

<?php if (!empty($is_logged_in)): ?>
    <p>Vous êtes connecté !</p>
<?php else: ?>
    <p>Vous n'êtes pas connecté.</p>
<?php endif; ?>
