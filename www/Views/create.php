<h2>Créer une page</h2>
<?php if (!empty($error)): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>


<form method="POST" action="create_post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
    <label>Titre</label><br>
    <input name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"><br>
    <label>Slug (optionnel)</label><br>
    <input name="slug" value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>"><br>
    <label>Contenu</label><br>
    <textarea name="content" rows="10"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea><br>
    <label>Publier ?</label>
    <input type="checkbox" name="is_published" value="1"><br>
    <button type="submit">Créer</button>
</form>
