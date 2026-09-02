CREATE TABLE IF NOT EXISTS `Employes` (
  `Id` INT(11)  AUTO_INCREMENT  PRIMARY KEY ,
  `Nom` VARCHAR(255) NOT NULL,
  `Prenom` VARCHAR(255) NOT NULL,
  `Genre` VARCHAR(50) NOT NULL,
  `CIN` VARCHAR(255) NOT NULL,
  `Date_de_naissance` DATE NOT NULL,
  `Email` VARCHAR(255) NOT NULL,
  `Telephone` VARCHAR(255) NOT NULL,
  `Poste` VARCHAR(255) NOT NULL,
  `DEPARTEMENT` VARCHAR(255) NOT NULL,
  `RIB` VARCHAR(255) NOT NULL,
  `Mot_de_pass` VARCHAR(255) NOT NULL,
  `Adresse` VARCHAR(255) NOT NULL,
  `Situation` VARCHAR(255) NOT NULL
  
);
ALTER TABLE Employes ADD COLUMN is_first_login TINYINT(1) DEFAULT 1;
ALTER TABLE Employes ADD role ENUM('admin', 'employee') NOT NULL DEFAULT 'employee';

CREATE TABLE IF NOT EXISTS contrats (
    contrat_id INT AUTO_INCREMENT PRIMARY KEY,
    employe_id INT NOT NULL,
    salaire_base DECIMAL(10, 2) NOT NULL,
    avantages_financiers VARCHAR(100) NOT NULL,
    avantages_sociaux VARCHAR(100) NOT NULL,
    avantages_professionnels VARCHAR(100) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE,
    type_contrat VARCHAR(100) NOT NULL,
    FOREIGN KEY (employe_id) REFERENCES Employes(Id) ON DELETE CASCADE
);



CREATE TABLE IF NOT EXISTS presence (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    employe_id INT(11) NOT NULL,
    date_presence DATE NOT NULL,
    statut ENUM('✅', '❌') NOT NULL,
    FOREIGN KEY (employe_id) REFERENCES Employes(Id) ON DELETE CASCADE
);


CREATE TABLE conges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employe_id INT NOT NULL,
    type_conge VARCHAR(255) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut VARCHAR(50) DEFAULT 'En attente',
    date_demande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employe_id) REFERENCES Employes(Id)
);


CREATE TABLE IF NOT EXISTS `heures_supp` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employe_id` INT NOT NULL,
    `nombre_heures` INT NOT NULL,
    `taux_horaire` DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (`employe_id`) REFERENCES `Employes`(`Id`)
);
ALTER TABLE heures_supp ADD COLUMN mois INT;


CREATE TABLE IF NOT EXISTS `primes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employe_id` INT NOT NULL,
    `prime_anciennete` DECIMAL(10, 2) NOT NULL,
    `prime_performance` DECIMAL(10, 2) NOT NULL,
    `mois` INT NOT NULL,
    FOREIGN KEY (`employe_id`) REFERENCES `Employes`(`Id`),
    UNIQUE(`employe_id`, `mois`) 
);

CREATE TABLE IF NOT EXISTS fiches_de_paie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employe_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    mois VARCHAR(50) NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employe_id) REFERENCES Employes(Id)
);
ALTER TABLE fiches_de_paie ADD COLUMN date_archivage TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

CREATE TABLE employe_supprime (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employe_id INT,
    nom VARCHAR(255),
    prenom VARCHAR(255),
    cin VARCHAR(20),
    poste VARCHAR(255),
    raison TEXT,
    date_suppression TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE salaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employe_id INT NOT NULL,
    mois VARCHAR(20) NOT NULL,
    annee INT NOT NULL,
    salaire_net DECIMAL(10,2) NOT NULL,
    date_paie DATE NOT NULL,
    FOREIGN KEY (employe_id) REFERENCES Employes(id)
);

