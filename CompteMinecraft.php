<?php

class CompteMinecraft {
    private ?int $idCompte;
    private string $pseudo;
    private string $email;
    private float $prix;
    private ?string $description;

    public function __construct(string $pseudo = '', string $email = '', float $prix = 0.0, ?string $description = null, ?int $idCompte = null) {
        $this->pseudo = trim($pseudo);
        $this->email = trim($email);
        $this->prix = $prix;
        $this->description = $description ? trim($description) : null;
        $this->idCompte = $idCompte;
    }

    public function getIdCompte(): ?int {
        return $this->idCompte;
    }

    public function getPseudo(): string {
        return $this->pseudo;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getPrix(): float {
        return $this->prix;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setPseudo(string $pseudo): void {
        $this->pseudo = trim($pseudo);
    }

    public function setEmail(string $email): void {
        $this->email = trim($email);
    }

    public function setPrix(float $prix): void {
        if ($prix >= 0) {
            $this->prix = $prix;
        }
    }

    public function setDescription(?string $description): void {
        $this->description = $description ? trim($description) : null;
    }

    public function __toString(): string {
        return "Compte ID: " . ($this->idCompte ?? 'N/A') . ", Pseudo: " . htmlspecialchars($this->pseudo) . ", Email: " . htmlspecialchars($this->email) . ", Prix: " . number_format($this->prix, 2, ',', ' ') . " €";
    }

    public function enregistrer(PDO $db): bool {

        $sql = "INSERT INTO ComptesMinecraft (pseudo, email, prix, description) VALUES (:pseudo, :email, :prix, :description)";
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':pseudo', $this->pseudo, PDO::PARAM_STR);
            $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindParam(':prix', $this->prix); 
            $stmt->bindParam(':description', $this->description, $this->description === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

            $success = $stmt->execute();
            if ($success) {
                $this->idCompte = (int)$db->lastInsertId();
            }
            $stmt->closeCursor(); 
            return $success;
        } catch (PDOException $e) {
            error_log("Erreur lors de l'enregistrement du compte : " . $e->getMessage());

            return false;
        }
    }

    public function modifier(PDO $db): bool {
        if ($this->idCompte === null) {
            error_log("Tentative de modification d'un CompteMinecraft sans ID.");
            return false;
        }
        $sql = "UPDATE ComptesMinecraft SET pseudo = :pseudo, email = :email, prix = :prix, description = :description WHERE idCompte = :idCompte";
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':pseudo', $this->pseudo, PDO::PARAM_STR);
            $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindParam(':prix', $this->prix);
            $stmt->bindParam(':description', $this->description, $this->description === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindParam(':idCompte', $this->idCompte, PDO::PARAM_INT); 

            $success = $stmt->execute();
            $stmt->closeCursor();
            return $success;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { 
                 error_log("Erreur lors de la modification du compte (Email potentiellement dupliqué - ID: {$this->idCompte}) : " . $e->getMessage());
            } else {
                error_log("Erreur lors de la modification du compte (ID: {$this->idCompte}) : " . $e->getMessage());
            }
            return false;
        }
    }

    public static function supprimer(PDO $db, int $id): bool {
        if ($id <= 0) return false; 

        $sql = "DELETE FROM ComptesMinecraft WHERE idCompte = :idCompte";
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idCompte', $id, PDO::PARAM_INT);
            $stmt->execute();
            $rowCount = $stmt->rowCount();
            $stmt->closeCursor();
            return $rowCount > 0;
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression du compte (ID: {$id}): " . $e->getMessage());
            return false;
        }
    }

    public static function findById(PDO $db, int $id): ?CompteMinecraft {
         if ($id <= 0) return null; 

        $sql = "SELECT * FROM ComptesMinecraft WHERE idCompte = :idCompte";
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idCompte', $id, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($data) {
                return new CompteMinecraft(
                    $data['pseudo'],
                    $data['email'],
                    (float)$data['prix'], 
                    $data['description'], 
                    (int)$data['idCompte']  
                );
            } else {
                return null; 
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la recherche du compte par ID (ID: {$id}) : " . $e->getMessage());
            return null;
        }
    }

    public static function findAll(PDO $db): array {
        $sql = "SELECT * FROM ComptesMinecraft ORDER BY pseudo ASC";
        $comptes = []; 
        try {
            $stmt = $db->query($sql);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);

            while ($data = $stmt->fetch()) {
                $comptes[] = new CompteMinecraft(
                    $data['pseudo'],
                    $data['email'],
                    (float)$data['prix'],
                    $data['description'],
                    (int)$data['idCompte']
                );
            }
            $stmt->closeCursor();
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de tous les comptes : " . $e->getMessage());
        }
        return $comptes;
    }
}
?>
