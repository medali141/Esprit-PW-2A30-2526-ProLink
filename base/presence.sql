CREATE TABLE `presence` (
  `id_presence`      INT AUTO_INCREMENT PRIMARY KEY,
  `id_participation` INT NOT NULL,
  `id_event`         INT NOT NULL,
  `date_scan`        DATE NOT NULL,
  `heure_scan`       TIME NOT NULL,
  `scanned_by`       INT DEFAULT NULL,          -- id admin
  UNIQUE KEY `unique_scan_par_jour` (`id_participation`, `date_scan`),
  FOREIGN KEY (`id_participation`) REFERENCES `participation`(`id_participation`) ON DELETE CASCADE,
  FOREIGN KEY (`id_event`)         REFERENCES `evenement`(`id_event`)             ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;