<?php
require_once('config.php');

function connecter(): ?PDO
{
    $options = [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_PERSISTENT         => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];

    $dsn = DB_HOST . DB_NAME;

    try {
        $connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $connection;
    } catch (PDOException $e) {
        error_log("Connexion à MySQL impossible : " . $e->getMessage());
        return null;
    }
}

function validerEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validerPrix(mixed $prix): bool {
    return is_numeric($prix) && (float)$prix >= 0;
}
?>
