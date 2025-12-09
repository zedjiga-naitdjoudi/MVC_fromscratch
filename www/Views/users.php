<h1>Gestion des utilisateurs</h1>

<?php if ($msg = \App\Core\SessionManager::getFlash('flash_error')): ?>
    <p style="color:red"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

<?php if ($msg = \App\Core\SessionManager::getFlash('flash_success')): ?>
    <p style="color:green"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>


<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Nom</th>
        <th>Email</th>
        <th>Rôle</th>
        <th>Action</th>
    </tr>

    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user->getId() ?></td>
            <td><?= htmlspecialchars($user->getName()) ?></td>
            <td><?= htmlspecialchars($user->getEmail()) ?></td>
            <td><?= $user->getRole() ?></td>
            <td>
                <?php if ($user->getId() !== \App\Core\SessionManager::get('user_id')): ?>
                    <form method="post" action="/users/update-role">
                        <input type="hidden" name="id" value="<?= $user->getId() ?>">
                        <select name="role">
        <option value="ROLE_USER"   <?= $user->getRole() === 'ROLE_USER' ? 'selected' : '' ?>>USER</option>
        <option value="ROLE_EDITOR" <?= $user->getRole() === 'ROLE_EDITOR' ? 'selected' : '' ?>>EDITOR</option>
        <option value="ROLE_ADMIN"  <?= $user->getRole() === 'ROLE_ADMIN' ? 'selected' : '' ?>>ADMIN</option>
    </select>
    <button type="submit">Changer</button>
</form>

                <?php else: ?>
                    —
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
