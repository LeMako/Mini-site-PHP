<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Comptes Minecraft - Devoir TW3</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="Ycontainer">
            <div class="Ymain">
                <?php echo $zonePrincipale; ?>
            </div>
            <div class="Ysidebar">
                <h4>Menu</h4>
                 <ul class="nav nav-pills nav-stacked">
                    <li><a href="index.php?action=accueil"><span class="glyphicon glyphicon-home"></span> Accueil</a></li>
                    <li><a href="index.php?action=afficher"><span class="glyphicon glyphicon-list"></span> Afficher les comptes</a></li>
                    <li><a href="index.php?action=saisir"><span class="glyphicon glyphicon-plus"></span> Ajouter un compte</a></li>
                    <li><hr></li>
                    <li><a href="index.php?action=tester"><span class="glyphicon glyphicon-transfer"></span> Tester la connexion BDD</a></li>
                    <li><a href="index.php?action=about"><span class="glyphicon glyphicon-info-sign"></span> À propos</a></li>
                 </ul>
            </div>
        </div>
        <hr>
        <footer>
            <p class="text-center">Devoir TW3 - PHP/MySQL - Université de Caen - LE QUANG HUY - 22114184</p>
        </footer>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>
</html>
