
DROP TABLE IF EXISTS ComptesMinecraft;

CREATE TABLE ComptesMinecraft (
    idCompte INT AUTO_INCREMENT PRIMARY KEY,        
    pseudo VARCHAR(100) NOT NULL,                 
    email VARCHAR(255) NOT NULL UNIQUE,           
    prix DECIMAL(10, 2) NOT NULL CHECK (prix >= 0), 
    description TEXT NULL,
    dateAjout TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO `ComptesMinecraft` (`pseudo`, `email`, `prix`, `description`) VALUES
('NotchFanBoy', 'fanboy@example.com', 15.50, 'Compte original, peu joué. Quelques constructions basiques.'),
('CreeperHunter', 'hunter@mail.net', 25.00, 'Compte avec accès serveur Hypixel VIP.\nBeaucoup de ressources accumulées.'),
('RedstoneMaster', 'master@domain.org', 30.00, NULL),
('PixelPioneer', 'pioneer@email.fr', 10.00, 'Compte récent, idéal pour débuter.'),
('AdminCraft', 'admin@craft.local', 50.75, 'Ancien compte admin d\'un petit serveur (fermé). Possède des items rares (legacy).');

INSERT INTO `ComptesMinecraft` (`pseudo`, `email`, `prix`, `description`) VALUES
('BuilderPro', 'builder@example.org', 22.00, 'Focus sur la construction créative.');
