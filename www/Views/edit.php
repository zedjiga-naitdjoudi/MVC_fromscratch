<h2>Éditer la page</h2>

<?php if (!empty($errors)): ?>
    <div style="color:red; font-weight:bold;">
        <ul>
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="update">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
    <input type="hidden" name="id" value="<?= $page->getId() ?>">

    <label>Titre</label><br>
    <input name="title" value="<?= htmlspecialchars($page->getTitle()) ?>"><br>

    <label>Slug</label><br>
    <input name="slug" value="<?= htmlspecialchars($page->getSlug()) ?>"><br>

    <label>Contenu</label><br>
    <textarea name="content" rows="10"><?= htmlspecialchars($page->getContent()) ?></textarea><br>

    <label>Publié ?</label>
    <input type="checkbox" name="is_published" value="1" <?= $page->isPublished() ? 'checked' : '' ?>><br>

    <button type="submit">Mettre à jour</button>
</form>
