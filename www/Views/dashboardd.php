<h2>Dashboard</h2>

<div class="actions">
    <a href="create">Créer une page</a>
</div>

<h3>Liste des pages</h3>

<?php if (!empty($pages)) : ?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Titre</th>
            <th>Slug</th>
            <th>Dernière mise à jour</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pages as $page) : ?>
            <tr>
                <td><?= htmlspecialchars($page->getId()) ?></td>
                <td><?= htmlspecialchars($page->getTitle()) ?></td>
                <td><?= htmlspecialchars($page->getSlug()) ?></td>
                <td><?= htmlspecialchars($page->getUpdatedAt()) ?></td>
                <td>
                    <a href="edit/<?= $page->getId() ?>">Modifier</a> |
                    <a href="delete/<?= $page->getId() ?>">Supprimer</a> |
                    <a href="/<?= $page->getSlug() ?>" target="_blank">Voir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else : ?>
<p>Aucune page pour le moment.</p>
<?php endif; ?>
