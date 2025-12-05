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
    <nav>
        <a href="/dashboard">Accueil</a> |
        <a href="/create">Créer une page</a> |
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
