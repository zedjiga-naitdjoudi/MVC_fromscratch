<html>
    <head>
        <title><?= $title ?? "Mon site" ?></title>
        <link rel="stylesheet" href="/Public/css/style.css">
    </head>
    <body>
        <header>
            <h1>Frontoffice</h1>
        </header>

        <main>
            <?= $viewContent ?> <!-- Contenu spécifique à la vue -->
        </main>
    </body>
</html>
