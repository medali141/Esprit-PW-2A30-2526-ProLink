<?php
/**
 * Contrôleur achats / catalogue — requêtes et règles sur la table `produit`.
 */
require_once __DIR__ . '/../config.php';

class ProduitController {
<<<<<<< HEAD
    private ?bool $hasPhotoColumn = null;

    /** @var bool|null null = unchecked */
    private ?bool $catalogueSchemaReady = null;

    /** Crée la table categorie, la colonne produit.idcategorie et la clé étrangère si la base ne les a pas encore. */
    public function ensureCatalogueSchema(): void {
        if ($this->catalogueSchemaReady === true) {
            return;
        }
        $db = Config::getConnexion();
        try {
            $db->exec(
                'CREATE TABLE IF NOT EXISTS categorie (
                  idcategorie int(11) NOT NULL AUTO_INCREMENT,
                  code varchar(40) NOT NULL,
                  libelle varchar(150) NOT NULL,
                  ordre int(11) NOT NULL DEFAULT 0,
                  PRIMARY KEY (idcategorie),
                  UNIQUE KEY code (code)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );
            $n = (int) ($db->query('SELECT COUNT(*) FROM categorie')->fetchColumn());
            if ($n === 0) {
                $ins = $db->prepare('INSERT INTO categorie (idcategorie, code, libelle, ordre) VALUES (?,?,?,?)');
                $rows = [
                    [1, 'generale', 'Générale', 0],
                    [2, 'peripheriques', 'Périphériques - saisie et audio', 10],
                    [3, 'pc', 'Ordinateurs bureau et portables', 20],
                    [4, 'telephones', 'Smartphones et téléphones mobiles', 30],
                    [5, 'tablettes', 'Tablettes tactiles', 40],
                    [6, 'chaises', 'Sièges et mobilier de bureau', 50],
                    [7, 'accessoires', 'Connectique et accessoires', 60],
                ];
                foreach ($rows as $r) {
                    try {
                        $ins->execute($r);
                    } catch (Throwable $e) {
                        // ligne déjà présente
                    }
                }
            }
            $chk = $db->query("SHOW COLUMNS FROM produit LIKE 'idcategorie'");
            $hasCol = $chk && $chk->fetch(PDO::FETCH_ASSOC);
            if (!$hasCol) {
                $db->exec('ALTER TABLE produit ADD COLUMN idcategorie INT(11) NOT NULL DEFAULT 1 AFTER description');
            }
            try {
                $db->exec(
                    'UPDATE produit p LEFT JOIN categorie c ON c.idcategorie = p.idcategorie SET p.idcategorie = 1 WHERE c.idcategorie IS NULL'
                );
            } catch (Throwable $e) {
            }
            try {
                $db->exec(
                    'ALTER TABLE produit ADD CONSTRAINT fk_produit_catalogue_categorie FOREIGN KEY (idcategorie)
                     REFERENCES categorie (idcategorie) ON DELETE RESTRICT ON UPDATE CASCADE'
                );
            } catch (Throwable $e) {
                try {
                    $db->exec(
                        'ALTER TABLE produit ADD CONSTRAINT fk_produit_categorie FOREIGN KEY (idcategorie)
                         REFERENCES categorie (idcategorie) ON DELETE RESTRICT ON UPDATE CASCADE'
                    );
                } catch (Throwable $e2) {
                }
            }
        } catch (Throwable $e) {
        }
        $this->catalogueSchemaReady = true;
    }

    public function listAllAdmin(): array {
        return $this->listAllAdminFiltered('', 'date', 'desc', '', 0);
    }

    /** @return list<array<string,mixed>> */
    public function listCategories(): array {
        $this->ensureCatalogueSchema();
        $db = Config::getConnexion();
        $rows = $db->query('SELECT idcategorie, code, libelle, ordre FROM categorie ORDER BY ordre ASC, libelle ASC')
            ->fetchAll(PDO::FETCH_ASSOC);
        return $this->normalizeCategoryRows($rows);
    }

    /**
     * Validation commune formulaires admin produit (création / édition).
     *
     * @param list<array<string,mixed>> $categories
     * @return array{error: non-empty-string, data: null}|array{error: null, data: array<string, mixed>}
     */
    public function validateProduitPayload(array $post, array $categories): array {
        $reference = trim((string) ($post['reference'] ?? ''));
        $designation = trim((string) ($post['designation'] ?? ''));
        $description = trim((string) ($post['description'] ?? ''));
        $prixRaw = str_replace(',', '.', trim((string) ($post['prix_unitaire'] ?? '0')));
        $stockRaw = trim((string) ($post['stock'] ?? '0'));
        $stock = ctype_digit($stockRaw) ? (int) $stockRaw : -1;
        $id_vendeur = (int) ($post['id_vendeur'] ?? 0);
        $idcategorie = (int) ($post['idcategorie'] ?? 0);
        $actif = isset($post['actif']) ? 1 : 0;

        $allowedCats = [];
        foreach ($categories as $c) {
            $cid = (int) ($c['idcategorie'] ?? 0);
            if ($cid > 0) {
                $allowedCats[$cid] = true;
            }
        }

        if ($reference === '' || strlen($reference) > 50 || $designation === '' || strlen($designation) > 200 || $id_vendeur <= 0) {
            return ['error' => 'Référence, désignation et vendeur sont obligatoires.', 'data' => null];
        }
        if (!isset($allowedCats[$idcategorie])) {
            return ['error' => 'Catégorie invalide.', 'data' => null];
        }
        if (!is_numeric($prixRaw) || (float) $prixRaw < 0) {
            return ['error' => 'Prix invalide.', 'data' => null];
        }
        if ($stock < 0) {
            return ['error' => 'Stock invalide (entier positif).', 'data' => null];
        }

        return [
            'error' => null,
            'data' => [
                'reference' => $reference,
                'designation' => $designation,
                'description' => $description !== '' ? $description : null,
                'idcategorie' => $idcategorie,
                'prix_unitaire' => (float) $prixRaw,
                'stock' => $stock,
                'id_vendeur' => $id_vendeur,
                'actif' => $actif,
            ],
        ];
    }

    /**
     * Liste admin : recherche texte, tri, ordre, filtre visibilité catalogue, catégorie.
     *
     * @param string $q       sous-chaîne réf. / désignation / description / vendeur / catégorie
     * @param string $tri     id|reference|designation|prix|stock|actif|vendeur|date|categorie
     * @param string $ordre   asc|desc
     * @param string $actif   '' (tous) | '1' | '0'
     * @param int    $idcategorie 0 = toutes, sinon FK categorie
     */
    public function listAllAdminFiltered(string $q, string $tri, string $ordre, string $actif, int $idcategorie = 0): array {
        $this->ensureCatalogueSchema();
        $where = ['1=1'];
        $params = [];
        $this->appendProduitTextSearch($where, $params, trim($q), 'admin');
        if ($actif === '1' || $actif === '0') {
            $where[] = 'p.actif = :actif';
            $params['actif'] = (int) $actif;
        }
        if ($idcategorie > 0) {
            $where[] = 'p.idcategorie = :idc';
            $params['idc'] = $idcategorie;
        }
        $orderBy = $this->orderSqlProduitList($tri, true);
        $dir = strtoupper($ordre) === 'ASC' ? 'ASC' : 'DESC';
        $sql = "SELECT p.*, u.nom AS vendeur_nom, u.prenom AS vendeur_prenom, u.email AS vendeur_email,
                       cat.libelle AS categorie_libelle, cat.code AS categorie_code
                FROM produit p
                INNER JOIN user u ON u.iduser = p.id_vendeur
                INNER JOIN categorie cat ON cat.idcategorie = p.idcategorie
                WHERE " . implode(' AND ', $where) . "
                ORDER BY $orderBy $dir, p.idproduit DESC";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute($params);
        return $this->normalizeCategoryRows($st->fetchAll(PDO::FETCH_ASSOC), 'categorie_libelle', 'categorie_code');
    }

    public function listCatalogueActifs(): array {
        return $this->listCatalogueFiltered('', 'designation', 'asc', 0);
    }

    /**
     * Catalogue public : recherche + tri métier + filtre catégorie.
     *
     * @param string $tri designation|prix_asc|prix_desc|stock_asc|stock_desc|recent
     * @param int    $idcategorie 0 = toutes
     */
    public function listCatalogueFiltered(string $q, string $tri, string $ordre, int $idcategorie = 0): array {
        $this->ensureCatalogueSchema();
        $where = ['p.actif = 1'];
        $params = [];
        $this->appendProduitTextSearch($where, $params, trim($q), 'catalogue');
        if ($idcategorie > 0) {
            $where[] = 'p.idcategorie = :idc';
            $params['idc'] = $idcategorie;
        }
        $orderParts = $this->orderSqlCatalogue($tri, $ordre);
        $sql = "SELECT p.*, u.prenom AS vendeur_prenom, u.nom AS vendeur_nom,
                       cat.libelle AS categorie_libelle, cat.code AS categorie_code
                FROM produit p
                INNER JOIN user u ON u.iduser = p.id_vendeur
                INNER JOIN categorie cat ON cat.idcategorie = p.idcategorie
                WHERE " . implode(' AND ', $where) . "
                ORDER BY " . implode(', ', $orderParts);
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute($params);
        return $this->normalizeCategoryRows($st->fetchAll(PDO::FETCH_ASSOC), 'categorie_libelle', 'categorie_code');
    }

    public function listByVendeur(int $idVendeur): array {
        return $this->listByVendeurFiltered($idVendeur, '', 'date', 'desc', '', 0);
    }

    /**
     * @param string $tri id|reference|designation|prix|stock|actif|date|categorie
     * @param int    $idcategorie 0 = toutes
     */
    public function listByVendeurFiltered(int $idVendeur, string $q, string $tri, string $ordre, string $actif, int $idcategorie = 0): array {
        $this->ensureCatalogueSchema();
        $where = ['p.id_vendeur = :v'];
        $params = ['v' => $idVendeur];
        $this->appendProduitTextSearch($where, $params, trim($q), 'vendeur');
        if ($actif === '1' || $actif === '0') {
            $where[] = 'p.actif = :actif';
            $params['actif'] = (int) $actif;
        }
        if ($idcategorie > 0) {
            $where[] = 'p.idcategorie = :idc';
            $params['idc'] = $idcategorie;
        }
        $orderBy = $this->orderSqlProduitList($tri, false);
        $dir = strtoupper($ordre) === 'ASC' ? 'ASC' : 'DESC';
        $sql = "SELECT p.*, cat.libelle AS categorie_libelle, cat.code AS categorie_code
                FROM produit p
                INNER JOIN categorie cat ON cat.idcategorie = p.idcategorie
                WHERE " . implode(' AND ', $where) . " ORDER BY $orderBy $dir, p.idproduit DESC";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute($params);
        return $this->normalizeCategoryRows($st->fetchAll(PDO::FETCH_ASSOC), 'categorie_libelle', 'categorie_code');
    }

    /** Tri liste produits admin ou vue vendeur (sans colonne vendeur si $adminJoin false). */
    private function orderSqlProduitList(string $tri, bool $adminJoin): string {
        $map = [
            'id' => 'p.idproduit',
            'reference' => 'p.reference',
            'designation' => 'p.designation',
            'prix' => 'p.prix_unitaire',
            'stock' => 'p.stock',
            'actif' => 'p.actif',
            'date' => 'p.created_at',
            'categorie' => 'cat.libelle',
        ];
        if ($adminJoin) {
            $map['vendeur'] = 'u.nom';
        }
        if (!$adminJoin && $tri === 'vendeur') {
            return 'p.created_at';
        }
        return $map[$tri] ?? 'p.created_at';
    }

    /** @param list<string> $where */
    private function appendProduitTextSearch(array &$where, array &$params, string $qTrimmed, string $scope): void {
        if ($qTrimmed === '') {
            return;
        }
        $clause = match ($scope) {
            'admin' => '(p.reference LIKE :q OR p.designation LIKE :q OR IFNULL(p.description, \'\') LIKE :q
                OR u.nom LIKE :q OR u.prenom LIKE :q OR u.email LIKE :q
                OR cat.libelle LIKE :q OR cat.code LIKE :q)',
            'catalogue' => '(p.reference LIKE :q OR p.designation LIKE :q OR IFNULL(p.description, \'\') LIKE :q
                OR u.nom LIKE :q OR u.prenom LIKE :q OR cat.libelle LIKE :q OR cat.code LIKE :q)',
            default => '(p.reference LIKE :q OR p.designation LIKE :q OR IFNULL(p.description, \'\') LIKE :q
                OR cat.libelle LIKE :q OR cat.code LIKE :q)',
        };
        $where[] = $clause;
        $params['q'] = '%' . $qTrimmed . '%';
    }

    /** @return list<string> fragments ORDER BY */
    private function orderSqlCatalogue(string $tri, string $ordre): array {
        $dir = strtoupper($ordre) === 'DESC' ? 'DESC' : 'ASC';
        switch ($tri) {
            case 'prix_asc':
                return ['p.prix_unitaire ASC', 'p.designation ASC'];
            case 'prix_desc':
                return ['p.prix_unitaire DESC', 'p.designation ASC'];
            case 'stock_asc':
                return ['p.stock ASC', 'p.designation ASC'];
            case 'stock_desc':
                return ['p.stock DESC', 'p.designation ASC'];
            case 'recent':
                return ['p.created_at DESC', 'p.designation ASC'];
            case 'designation':
            default:
                return ['p.designation ' . $dir, 'p.idproduit DESC'];
        }
    }

    public function getById(int $id): ?array {
        $this->ensureCatalogueSchema();
        $sql = "SELECT p.*, cat.libelle AS categorie_libelle, cat.code AS categorie_code
                FROM produit p
                INNER JOIN categorie cat ON cat.idcategorie = p.idcategorie
                WHERE p.idproduit = :id LIMIT 1";
=======

    public function listAllAdmin(): array {
        $sql = "SELECT p.*, u.nom AS vendeur_nom, u.prenom AS vendeur_prenom, u.email AS vendeur_email
                FROM produit p
                INNER JOIN user u ON u.iduser = p.id_vendeur
                ORDER BY p.created_at DESC";
        $db = Config::getConnexion();
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listCatalogueActifs(): array {
        $sql = "SELECT p.*, u.prenom AS vendeur_prenom, u.nom AS vendeur_nom
                FROM produit p
                INNER JOIN user u ON u.iduser = p.id_vendeur
                WHERE p.actif = 1
                ORDER BY p.designation ASC";
        $db = Config::getConnexion();
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listByVendeur(int $idVendeur): array {
        $sql = "SELECT * FROM produit WHERE id_vendeur = :v ORDER BY created_at DESC";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute(['v' => $idVendeur]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $sql = "SELECT * FROM produit WHERE idproduit = :id LIMIT 1";
>>>>>>> formation
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute(['id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
<<<<<<< HEAD
        if ($row) {
            $row = $this->normalizeCategoryRows([$row], 'categorie_libelle', 'categorie_code')[0];
        }
        return $row ?: null;
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    private function normalizeCategoryRows(array $rows, string $labelKey = 'libelle', string $codeKey = 'code'): array {
        foreach ($rows as &$row) {
            $rawLabel = (string) ($row[$labelKey] ?? '');
            $code = (string) ($row[$codeKey] ?? '');
            $row[$labelKey] = $this->normalizeCategoryLabel($rawLabel, $code);
        }
        unset($row);
        return $rows;
    }

    private function normalizeCategoryLabel(string $label, string $code): string {
        $trimmed = trim($label);
        $codeNorm = strtolower(trim($code));
        $canonicalByCode = [
            'peripheriques' => 'Périphériques (claviers, souris, micros)',
            'pc' => 'PC & ordinateurs',
            'telephones' => 'Téléphones & smartphones',
            'tablettes' => 'Tablettes',
            'chaises' => 'Chaises & sièges',
            'accessoires' => 'Accessoires & câbles',
        ];

        if (isset($canonicalByCode[$codeNorm]) && $this->looksCorruptedText($trimmed)) {
            return $canonicalByCode[$codeNorm];
        }
        return $trimmed;
    }

    private function looksCorruptedText(string $text): bool {
        if ($text === '') return true;
        if (strpos($text, '?') !== false) return true;
        return preg_match('/Ã.|Â|�/u', $text) === 1;
    }

    public function add(array $data): void {
        $this->ensureCatalogueSchema();
        $hasPhoto = $this->hasPhotoColumn();
        $sql = $hasPhoto
            ? "INSERT INTO produit (reference, designation, description, idcategorie, prix_unitaire, stock, id_vendeur, actif, photo)
                VALUES (:reference, :designation, :description, :idcategorie, :prix_unitaire, :stock, :id_vendeur, :actif, :photo)"
            : "INSERT INTO produit (reference, designation, description, idcategorie, prix_unitaire, stock, id_vendeur, actif)
                VALUES (:reference, :designation, :description, :idcategorie, :prix_unitaire, :stock, :id_vendeur, :actif)";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $params = [
            'reference' => $data['reference'],
            'designation' => $data['designation'],
            'description' => $data['description'] ?? null,
            'idcategorie' => (int) ($data['idcategorie'] ?? 1),
=======
        return $row ?: null;
    }

    public function add(array $data): void {
        $sql = "INSERT INTO produit (reference, designation, description, prix_unitaire, stock, id_vendeur, actif)
                VALUES (:reference, :designation, :description, :prix_unitaire, :stock, :id_vendeur, :actif)";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute([
            'reference' => $data['reference'],
            'designation' => $data['designation'],
            'description' => $data['description'] ?? null,
>>>>>>> formation
            'prix_unitaire' => $data['prix_unitaire'],
            'stock' => (int) $data['stock'],
            'id_vendeur' => (int) $data['id_vendeur'],
            'actif' => isset($data['actif']) ? (int) (bool) $data['actif'] : 1,
<<<<<<< HEAD
        ];
        if ($hasPhoto) {
            $params['photo'] = $this->normalizePhotoPath($data['photo'] ?? null);
        }
        $st->execute($params);
    }

    public function update(int $id, array $data): void {
        $this->ensureCatalogueSchema();
        $hasPhoto = $this->hasPhotoColumn();
=======
        ]);
    }

    public function update(int $id, array $data): void {
>>>>>>> formation
        $sql = "UPDATE produit SET
                reference = :reference,
                designation = :designation,
                description = :description,
<<<<<<< HEAD
                idcategorie = :idcategorie,
                prix_unitaire = :prix_unitaire,
                stock = :stock,
                id_vendeur = :id_vendeur,
                actif = :actif" . ($hasPhoto ? ",
                photo = :photo" : "") . "
                WHERE idproduit = :id";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $params = [
=======
                prix_unitaire = :prix_unitaire,
                stock = :stock,
                id_vendeur = :id_vendeur,
                actif = :actif
                WHERE idproduit = :id";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute([
>>>>>>> formation
            'id' => $id,
            'reference' => $data['reference'],
            'designation' => $data['designation'],
            'description' => $data['description'] ?? null,
<<<<<<< HEAD
            'idcategorie' => (int) ($data['idcategorie'] ?? 1),
=======
>>>>>>> formation
            'prix_unitaire' => $data['prix_unitaire'],
            'stock' => (int) $data['stock'],
            'id_vendeur' => (int) $data['id_vendeur'],
            'actif' => (int) (bool) ($data['actif'] ?? 1),
<<<<<<< HEAD
        ];
        if ($hasPhoto) {
            $params['photo'] = $this->normalizePhotoPath($data['photo'] ?? null);
        }
        $st->execute($params);
=======
        ]);
>>>>>>> formation
    }

    public function setActif(int $id, int $actif): void {
        $db = Config::getConnexion();
        $st = $db->prepare("UPDATE produit SET actif = :a WHERE idproduit = :id");
        $st->execute(['a' => $actif ? 1 : 0, 'id' => $id]);
    }

    public function countOrdered(int $idProduit): int {
        $db = Config::getConnexion();
        $st = $db->prepare("SELECT COUNT(*) FROM commande_produit WHERE idproduit = :id");
        $st->execute(['id' => $idProduit]);
        return (int) $st->fetchColumn();
    }

    public function deleteHard(int $id): void {
        $db = Config::getConnexion();
        $st = $db->prepare("DELETE FROM produit WHERE idproduit = :id");
        $st->execute(['id' => $id]);
    }

    /** Comptes pouvant être vendeurs (FK user) — priorité entrepreneurs. */
    public function listVendeursForSelect(): array {
        $sql = "SELECT iduser, nom, prenom, email, type FROM user ORDER BY FIELD(type,'entrepreneur','admin','candidat'), nom, prenom";
        $db = Config::getConnexion();
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
<<<<<<< HEAD

    public function savePhotoUpload(array $file, ?string $currentPhoto = null): ?string {
        if (empty($file) || !isset($file['error'])) {
            return $this->normalizePhotoPath($currentPhoto);
        }
        if ((int) $file['error'] === UPLOAD_ERR_NO_FILE) {
            return $this->normalizePhotoPath($currentPhoto);
        }
        if ((int) $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Erreur lors de l’upload de la photo.');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Fichier photo invalide.');
        }
        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > 2 * 1024 * 1024) {
            throw new RuntimeException('Photo invalide (max 2 Mo).');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmp);
        $extMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        if (!isset($extMap[$mime])) {
            throw new RuntimeException('Format photo non supporté (JPG, PNG, WEBP, GIF).');
        }
        $ext = $extMap[$mime];

        $targetDir = __DIR__ . '/../view/uploads/products';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            throw new RuntimeException('Impossible de créer le dossier des photos.');
        }

        $filename = 'prod_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $targetPath = $targetDir . '/' . $filename;
        if (!move_uploaded_file($tmp, $targetPath)) {
            throw new RuntimeException('Impossible d’enregistrer la photo.');
        }

        $newPhoto = 'uploads/products/' . $filename;
        $this->deletePhotoFile($currentPhoto, $newPhoto);
        return $newPhoto;
    }

    public function deletePhotoFile(?string $photoPath, ?string $keepPhoto = null): void {
        $photoPath = $this->normalizePhotoPath($photoPath);
        $keepPhoto = $this->normalizePhotoPath($keepPhoto);
        if ($photoPath === null || $photoPath === $keepPhoto) {
            return;
        }
        if (strpos($photoPath, 'uploads/products/') !== 0) {
            return;
        }
        $full = realpath(__DIR__ . '/../view/' . $photoPath);
        $base = realpath(__DIR__ . '/../view/uploads/products');
        if (!$full || !$base || strpos($full, $base) !== 0 || !is_file($full)) {
            return;
        }
        @unlink($full);
    }

    private function normalizePhotoPath(?string $path): ?string {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');
        return strlen($path) <= 255 ? $path : null;
    }

    private function hasPhotoColumn(): bool {
        if ($this->hasPhotoColumn !== null) {
            return $this->hasPhotoColumn;
        }
        $db = Config::getConnexion();
        $st = $db->query("SHOW COLUMNS FROM produit LIKE 'photo'");
        $exists = (bool) $st->fetch(PDO::FETCH_ASSOC);
        if (!$exists) {
            try {
                $db->exec("ALTER TABLE produit ADD COLUMN photo VARCHAR(255) NULL AFTER actif");
                $exists = true;
            } catch (Throwable $e) {
                $exists = false;
            }
        }
        $this->hasPhotoColumn = $exists;
        return $this->hasPhotoColumn;
    }
=======
>>>>>>> formation
}
