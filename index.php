<?php
require_once('donnees.php');
require_once('CompteMinecraft.php');

$zonePrincipale = "";
$message = "";
$erreurs = [];
$corps = '';

$connection = connecter();

if (!$connection) {
    $zonePrincipale = "<p class='alert alert-danger'>Erreur critique: Impossible de se connecter à la base de données...</p>";
    if (file_exists("squelette.php")) { include("squelette.php"); } else { echo $zonePrincipale; }
    exit;
}

$action = $_GET['action'] ?? 'accueil';

try {
    switch ($action) {
        case "accueil":
            $zonePrincipale = "<div class='jumbotron text-center'>";
            $zonePrincipale .= "<h2>Bienvenue sur Notre Boutique de Comptes Minecraft !</h2>";
            $zonePrincipale .= "<p>Explorez notre sélection de comptes Minecraft uniques et prêts à l'emploi.</p>";
            if (file_exists("images/minecraft_banner.jpg")) { 
                 $zonePrincipale .= "<img src='images/minecraft_banner.jpg' alt='Bannière Minecraft' class='img-responsive img-thumbnail accueil-image'>";
            } else {
                 $zonePrincipale .= "<p class='text-muted'>(Image d'accueil non trouvée dans le dossier /images/)</p>";
            }
            $zonePrincipale .= "<p style='margin-top: 20px;'>Que vous soyez un nouveau joueur ou un vétéran cherchant un nouveau départ, nous avons le compte qu'il vous faut !</p>";
			$zonePrincipale .= "<h4>Pourquoi choisir nos comptes ?</h4>";
			$zonePrincipale .= "<ul class='list-unstyled text-left' style='display: inline-block;'>";
			$zonePrincipale .= "<li><span class='glyphicon glyphicon-ok text-success'></span> Variété de comptes</li>";
			$zonePrincipale .= "<li><span class='glyphicon glyphicon-lock text-success'></span> Transactions sécurisées</li>";
			$zonePrincipale .= "<li><span class='glyphicon glyphicon-eur text-success'></span> Prix compétitifs</li>";
			$zonePrincipale .= "<li><span class='glyphicon glyphicon-play text-success'></span> Prêt à jouer immédiatement</li>";
			$zonePrincipale .= "</ul>";
            $zonePrincipale .= "<p style='margin-top: 30px;'><a href='index.php?action=afficher' class='btn btn-primary btn-lg'><span class='glyphicon glyphicon-list'></span> Voir les comptes disponibles</a></p>";
            $zonePrincipale .= "</div>";
            break;
        case "afficher":
            $message = $_GET['message'] ?? '';
            if ($message) {
                 $zonePrincipale .= "<p class='alert alert-success'>" . htmlspecialchars($message) . "</p>";
            }

            $zonePrincipale .= "<h2>Liste des Comptes Minecraft à vendre</h2>";
            $comptes = CompteMinecraft::findAll($connection);

            if (empty($comptes)) {
                $zonePrincipale .= "<p class='alert alert-info'>Aucun compte Minecraft n'est actuellement enregistré.</p>";
            } else {
                $zonePrincipale .= "<table class='table table-striped table-bordered'>";
                $zonePrincipale .= "<thead class='thead-dark'><tr><th>ID</th><th>Pseudo</th><th>Email</th><th>Prix</th><th>Description</th><th>Actions</th></tr></thead>";
                $zonePrincipale .= "<tbody>";
                foreach ($comptes as $compte) {
                    $zonePrincipale .= "<tr>";
                    $zonePrincipale .= "<td>" . $compte->getIdCompte() . "</td>";
                    $zonePrincipale .= "<td>" . htmlspecialchars($compte->getPseudo()) . "</td>";
                    $zonePrincipale .= "<td>" . htmlspecialchars($compte->getEmail()) . "</td>";
                    $zonePrincipale .= "<td>" . number_format($compte->getPrix(), 2, ',', ' ') . " €</td>";
                    $zonePrincipale .= "<td>" . nl2br(htmlspecialchars($compte->getDescription() ?? '')) . "</td>";
                    $zonePrincipale .= "<td>";
                    $zonePrincipale .= "<a href='index.php?action=modifier&id=" . $compte->getIdCompte() . "' class='btn btn-sm btn-warning mr-1' title='Modifier'><span class='glyphicon glyphicon-pencil'></span></a> ";
                    $zonePrincipale .= "<a href='index.php?action=supprimer&id=" . $compte->getIdCompte() . "' class='btn btn-sm btn-danger' title='Supprimer'><span class='glyphicon glyphicon-trash'></span></a>";
                    $zonePrincipale .= "</td>";
                    $zonePrincipale .= "</tr>";
                }
                $zonePrincipale .= "</tbody></table>";
            }
             $zonePrincipale .= '<p><a href="index.php?action=saisir" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> Ajouter un compte</a></p>';
            break;

        case "saisir":
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['compte_valider'])) {
                $pseudo = trim($_POST['pseudo'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $prix_str = trim($_POST['prix'] ?? '');
                $description = isset($_POST['description']) && $_POST['description'] !== '' ? trim($_POST['description']) : null;

                $erreurs = []; 
                if (empty($pseudo)) $erreurs['pseudo'] = "Le pseudo est obligatoire.";
                if (empty($email)) $erreurs['email'] = "L'email est obligatoire.";
                elseif (!validerEmail($email)) $erreurs['email'] = "Format d'email invalide.";
                if ($prix_str === '') $erreurs['prix'] = "Le prix est obligatoire."; 
                elseif (!validerPrix($prix_str)) $erreurs['prix'] = "Le prix doit être un nombre positif ou zéro.";

                if (empty($erreurs)) {
                    $prix = (float)$prix_str; 
                    $nouveauCompte = new CompteMinecraft($pseudo, $email, $prix, $description);

                    if ($nouveauCompte->enregistrer($connection)) {
                        header('Location: index.php?action=afficher&message=Compte ajouté avec succès (ID: ' . $nouveauCompte->getIdCompte() . ')');
                        exit; 
                    } else {
                         $erreurs['general'] = "Erreur lors de l'ajout du compte. L'email existe peut-être déjà ou une autre erreur s'est produite.";
                         include("formulaireCompte.html"); 
                         $zonePrincipale = "<h2>Ajouter un nouveau Compte Minecraft</h2>";
                         $zonePrincipale .= "<p class='alert alert-danger'>" . htmlspecialchars($erreurs['general']) . "</p>" . $corps; 
                    }
                } else {
                     include("formulaireCompte.html"); 
                     $zonePrincipale = "<h2>Ajouter un nouveau Compte Minecraft</h2>";
                     $zonePrincipale .= "<p class='alert alert-warning'>Veuillez corriger les erreurs ci-dessous.</p>" . $corps; 
                }

            } else {
                 include("formulaireCompte.html"); 
                 $zonePrincipale = "<h2>Ajouter un nouveau Compte Minecraft</h2>" . $corps; 
            }
            break;

        case "modifier":
            $idCompte = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

            if ($idCompte && $idCompte > 0) {
                $compte = CompteMinecraft::findById($connection, $idCompte); 
                if ($compte) {
                    include("formulaireCompte.html");
                    $zonePrincipale = $corps; 
                } else {
                    $zonePrincipale = "<p class='alert alert-warning'>Compte avec l'ID {$idCompte} non trouvé.</p>";
                    $zonePrincipale .= '<p><a href="index.php?action=afficher" class="btn btn-secondary">Retour à la liste</a></p>';
                }
            } else {
                 $zonePrincipale = "<p class='alert alert-danger'>ID de compte invalide pour la modification.</p>";
                 $zonePrincipale .= '<p><a href="index.php?action=afficher" class="btn btn-secondary">Retour à la liste</a></p>';
            }
            break;

        case "update":
             if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['compte_valider'])) {
                $idCompte = filter_input(INPUT_POST, 'idCompte', FILTER_VALIDATE_INT);

                if (!$idCompte || $idCompte <= 0) {
                    $zonePrincipale = "<p class='alert alert-danger'>ID de compte invalide pour la mise à jour.</p>";
                    $zonePrincipale .= '<p><a href="index.php?action=afficher" class="btn btn-secondary">Retour à la liste</a></p>';
                    break;
                }

                $pseudo = trim($_POST['pseudo'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $prix_str = trim($_POST['prix'] ?? '');
                $description = isset($_POST['description']) && $_POST['description'] !== '' ? trim($_POST['description']) : null;

                $erreurs = [];
                if (empty($pseudo)) $erreurs['pseudo'] = "Le pseudo est obligatoire.";
                if (empty($email)) $erreurs['email'] = "L'email est obligatoire.";
                elseif (!validerEmail($email)) $erreurs['email'] = "Format d'email invalide.";
                if ($prix_str === '') $erreurs['prix'] = "Le prix est obligatoire.";
                elseif (!validerPrix($prix_str)) $erreurs['prix'] = "Le prix doit être un nombre positif ou zéro.";

                if (empty($erreurs)) {
                    $prix = (float)$prix_str;
                    $compteAModifier = new CompteMinecraft($pseudo, $email, $prix, $description, $idCompte);

                    if ($compteAModifier->modifier($connection)) { 
                         header('Location: index.php?action=afficher&message=Compte ID ' . $idCompte . ' modifié avec succès.');
                         exit;
                    } else {
                        $erreurs['general'] = "Erreur lors de la modification du compte ID {$idCompte}. L'email existe peut-être déjà pour un autre compte ou une autre erreur s'est produite.";
                        $compte = $compteAModifier; 
                        include("formulaireCompte.html");
                        $zonePrincipale = "<p class='alert alert-danger'>" . htmlspecialchars($erreurs['general']) . "</p>" . $corps; 
                    }
                } else {
                     $compte = new CompteMinecraft($pseudo, $email, (float)$prix_str, $description, $idCompte);
                     include("formulaireCompte.html");
                     $zonePrincipale = "<p class='alert alert-warning'>Veuillez corriger les erreurs ci-dessous.</p>" . $corps; 
                }
            } else {
                 header('Location: index.php?action=afficher');
                 exit;
            }
            break;

        case "supprimer":
            $idCompte = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

            if ($idCompte && $idCompte > 0) {
                $compte = CompteMinecraft::findById($connection, $idCompte);
                if ($compte) {
                     $detailsCompte = htmlspecialchars($compte->getPseudo()) . ' (ID: ' . $compte->getIdCompte() . ')';
                     $zonePrincipale = '<form action="index.php?action=delete" method="post">
                                    <input type="hidden" name="idCompte" value="' . $idCompte . '"/>
                                    <h3>Confirmation de suppression</h3>
                                    <p class="alert alert-warning">Êtes-vous sûr de vouloir supprimer le compte : <strong>' . $detailsCompte . '</strong> ?</p>
                                    <p>Cette action est irréversible.</p>
                                    <p>
                                        <button type="submit" name="confirm_delete" class="btn btn-danger">Confirmer la suppression</button>
                                        <a href="index.php?action=afficher" class="btn btn-secondary">Annuler</a>
                                    </p>
                                </form>';
                } else {
                    $zonePrincipale = "<p class='alert alert-warning'>Compte avec l'ID {$idCompte} non trouvé pour la suppression.</p>";
                    $zonePrincipale .= '<p><a href="index.php?action=afficher" class="btn btn-secondary">Retour à la liste</a></p>';
                }
            } else {
                $zonePrincipale = "<p class='alert alert-danger'>ID de compte invalide pour la suppression.</p>";
                $zonePrincipale .= '<p><a href="index.php?action=afficher" class="btn btn-secondary">Retour à la liste</a></p>';
            }
            break;

        case "delete":
             if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
                 $idCompte = filter_input(INPUT_POST, 'idCompte', FILTER_VALIDATE_INT);

                if ($idCompte && $idCompte > 0) {
                    if (CompteMinecraft::supprimer($connection, $idCompte)) { 
                         header('Location: index.php?action=afficher&message=Compte ID ' . $idCompte . ' supprimé avec succès.');
                         exit;
                    } else {
                         $zonePrincipale = "<p class='alert alert-danger'>Erreur lors de la suppression du compte ID {$idCompte}. Il a peut-être déjà été supprimé ou une autre erreur s'est produite.</p>";
                         $zonePrincipale .= '<p><a href="index.php?action=afficher" class="btn btn-secondary">Retour à la liste</a></p>';
                    }
                } else {
                    $zonePrincipale = "<p class='alert alert-warning'>ID de compte invalide reçu pour la suppression.</p>";
                    $zonePrincipale .= '<p><a href="index.php?action=afficher" class="btn btn-secondary">Retour à la liste</a></p>';
                }
            } else {
                 header('Location: index.php?action=afficher');
                 exit;
            }
            break;

         case "tester":
            $zonePrincipale = "<p class='alert alert-success'>Test de connexion à la base de données : Succès ! (Connexion établie au chargement de la page)</p>";
            try {
                $stmt = $connection->query("SELECT 1");
                $zonePrincipale .= "<p class='alert alert-info'>Test de requête simple (SELECT 1) : Succès !</p>";
                $stmt->closeCursor();
            } catch (PDOException $e) {
                $zonePrincipale .= "<p class='alert alert-danger'>Test de requête simple (SELECT 1) : Échec ! Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            break;

        case "about":
            $zonePrincipale = "<h2>À propos de ce projet</h2>";
            $zonePrincipale .= "<p>Ce site a été réalisé dans le cadre du TP/Devoir - Programmation PHP.</p>";
            $zonePrincipale .= "<ul>";
            $zonePrincipale .= "<li><strong>Étudiant :</strong> LE QUANG HUY</li>"; 
            $zonePrincipale .= "<li><strong>Numéro Étudiant :</strong> 22114184</li>"; 
            $zonePrincipale .= "<li><strong>Groupe TP :</strong> 2A</li>";
            $zonePrincipale .= "</ul>";
            $zonePrincipale .= "<h4>Fonctionnalités réalisées :</h4>";
            $zonePrincipale .= "<ul>";
            $zonePrincipale .= "<li><span class='glyphicon glyphicon-home'></span> Page d'accueil (introduction et présentation)</li>";
            $zonePrincipale .= "<li><span class='glyphicon glyphicon-list'></span> Affichage de la liste des comptes Minecraft (Read)</li>";
            $zonePrincipale .= "<li><span class='glyphicon glyphicon-plus'></span> Ajout d'un nouveau compte (Create)</li>";
            $zonePrincipale .= "<li><span class='glyphicon glyphicon-pencil'></span> Modification d'un compte existant (Update)</li>";
            $zonePrincipale .= "<li><span class='glyphicon glyphicon-trash'></span> Suppression d'un compte avec confirmation (Delete)</li>";
            $zonePrincipale .= "<li><span class='glyphicon glyphicon-user'></span> Utilisation d'une classe PHP (<code>CompteMinecraft</code>)</li>";
            $zonePrincipale .= "<li><span class='glyphicon glyphicon-transfer'></span> Interaction avec BDD MySQL via PDO et requêtes préparées</li>";
            $zonePrincipale .= "<li><span class='glyphicon glyphicon-warning-sign'></span> Validation simple des données côté serveur</li>";
            $zonePrincipale .= "<li><span class='glyphicon glyphicon-cog'></span> Structure avec contrôleur frontal et squelette</li>";
            $zonePrincipale .= "<li><span class='glyphicon glyphicon-info-sign'></span> Page 'À propos' (celle-ci)</li>";
            $zonePrincipale .= "</ul>";
            $zonePrincipale .= '<p><a href="index.php?action=accueil" class="btn btn-primary">Retour à l\'accueil</a></p>';
            break;

        default:
            $zonePrincipale = "<p class='alert alert-danger'>Action non valide demandée ('" . htmlspecialchars($action) . "').</p>";
            $zonePrincipale .= '<p><a href="index.php?action=accueil" class="btn btn-secondary">Retour à l\'accueil</a></p>';
            break;
    }
} catch (PDOException $e) {
    error_log("Erreur PDO non gérée dans index.php: " . $e->getMessage());
    $zonePrincipale = "<p class='alert alert-danger'>Une erreur inattendue est survenue lors de l'interaction avec la base de données. Veuillez réessayer plus tard. Code: PDO</p>";
} catch (Exception $e) {
     error_log("Erreur générale non gérée dans index.php: " . $e->getMessage());
     $zonePrincipale = "<p class='alert alert-danger'>Une erreur inattendue est survenue. Veuillez réessayer plus tard. Code: GEN</p>";
} finally {
    $connection = null;
}

if (file_exists("squelette.php")) {
    include("squelette.php");
} else {
    echo "<h1>Gestion des Comptes Minecraft</h1><hr>";
    echo $zonePrincipale;
    echo "<hr><p><strong>Erreur: Fichier squelette.php manquant.</strong></p>";
}

?>
