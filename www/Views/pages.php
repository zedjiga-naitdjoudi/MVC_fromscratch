<?php use App\Core\SessionManager; ?>
<?php if (SessionManager::get('is_logged_in')): ?>
    <p>Bonjour <?= htmlspecialchars(SessionManager::get('user_email')) ?></p>
<?php endif; ?>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Titre</th>
            <th>Slug</th>
            <th>Publié</th>
            <th>Auteur</th> 
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($pages as $p): ?>
            <tr>
                <td><?= $p->getId() ?></td>
                <td><?= htmlspecialchars($p->getTitle()) ?></td>
                <td><?= htmlspecialchars($p->getSlug()) ?></td>
                <td><?= $p->isPublished() ? 'Oui' : 'Non' ?></td>
                <td><?= htmlspecialchars($p->getAuthorEmail() ?? 'Inconnu') ?></td>
                <td>
                    <?php if ($p->getAuthorId() === SessionManager::get('user_id')): ?>
                        <a href="edit?id=<?= $p->getId() ?>">Éditer</a>
                        <form method="POST" action="delete" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(SessionManager::generateCsrfToken()) ?>">
                            <input type="hidden" name="id" value="<?= $p->getId() ?>">
                            <button type="submit" onclick="return confirm('Supprimer ?')">Supprimer</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($p->isPublished()): ?>
                        <a href="/<?= htmlspecialchars($p->getSlug()) ?>" target="_blank">Voir</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
