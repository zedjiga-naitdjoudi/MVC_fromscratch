<!-- /Views/Templates/backoffice.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Backoffice</title>
    <link rel="stylesheet" href="/Public/css/stylebo.css">
</head>
<body>
<header>
    <h1>Backoffice Dashboard</h1>
    <?php use App\Core\SessionManager; ?>

        <nav>
        <a href="/dashboard">Accueil</a>
        <a href="/create">Créer une page</a>

    <?php if (SessionManager::get("user_role") === "ROLE_ADMIN"): ?>
        <a href="/users">Utilisateurs</a>
    <?php endif; ?>

    <a href="/logout">Déconnexion</a>
</nav>

</header>


    <main>
        <?php
        
        include $pathView;
        ?>
    </main>
</body>
</html>
