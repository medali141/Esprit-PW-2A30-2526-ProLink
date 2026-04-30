<?php
/**
 * Contrôleur achats / catalogue — requêtes et règles sur la table `produit`.
 */
require_once __DIR__ . '/../config.php';

class ProduitController {
    private ?bool $hasPhotoColumn = null;

    public function listAllAdmin(): array {
        return $this->listAllAdminFiltered('', 'date', 'desc', '', 0);
    }

    /** @return list<array<string,mixed>> */
    public function listCategories(): array {
        $db = Config::getConnexion();
        return $db->query('SELECT idcategorie, code, libelle, ordre FROM categorie ORDER BY ordre ASC, libelle ASC')
            ->fetchAll(PDO::FETCH_ASSOC);
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
        $where = ['1=1'];
        $params = [];
        $q = trim($q);
        if ($q !== '') {
            $where[] = '(p.reference LIKE :q OR p.designation LIKE :q OR IFNULL(p.description, \'\') LIKE :q
                OR u.nom LIKE :q OR u.prenom LIKE :q OR u.email LIKE :q
                OR cat.libelle LIKE :q OR cat.code LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }
        if ($actif === '1' || $actif === '0') {
            $where[] = 'p.actif = :actif';
            $params['actif'] = (int) $actif;
        }
        if ($idcategorie > 0) {
            $where[] = 'p.idcategorie = :idc';
            $params['idc'] = $idcategorie;
        }
        $orderBy = $this->orderSqlProduitAdmin($tri);
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
        return $st->fetchAll(PDO::FETCH_ASSOC);
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
        $where = ['p.actif = 1'];
        $params = [];
        $q = trim($q);
        if ($q !== '') {
            $where[] = '(p.reference LIKE :q OR p.designation LIKE :q OR IFNULL(p.description, \'\') LIKE :q
                OR u.nom LIKE :q OR u.prenom LIKE :q OR cat.libelle LIKE :q OR cat.code LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }
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
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listByVendeur(int $idVendeur): array {
        return $this->listByVendeurFiltered($idVendeur, '', 'date', 'desc', '', 0);
    }

    /**
     * @param string $tri id|reference|designation|prix|stock|actif|date|categorie
     * @param int    $idcategorie 0 = toutes
     */
    public function listByVendeurFiltered(int $idVendeur, string $q, string $tri, string $ordre, string $actif, int $idcategorie = 0): array {
        $where = ['p.id_vendeur = :v'];
        $params = ['v' => $idVendeur];
        $q = trim($q);
        if ($q !== '') {
            $where[] = '(p.reference LIKE :q OR p.designation LIKE :q OR IFNULL(p.description, \'\') LIKE :q
                OR cat.libelle LIKE :q OR cat.code LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }
        if ($actif === '1' || $actif === '0') {
            $where[] = 'p.actif = :actif';
            $params['actif'] = (int) $actif;
        }
        if ($idcategorie > 0) {
            $where[] = 'p.idcategorie = :idc';
            $params['idc'] = $idcategorie;
        }
        $orderBy = $this->orderSqlProduitSolo($tri);
        $dir = strtoupper($ordre) === 'ASC' ? 'ASC' : 'DESC';
        $sql = "SELECT p.*, cat.libelle AS categorie_libelle, cat.code AS categorie_code
                FROM produit p
                INNER JOIN categorie cat ON cat.idcategorie = p.idcategorie
                WHERE " . implode(' AND ', $where) . " ORDER BY $orderBy $dir, p.idproduit DESC";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    private function orderSqlProduitAdmin(string $tri): string {
        $map = [
            'id' => 'p.idproduit',
            'reference' => 'p.reference',
            'designation' => 'p.designation',
            'prix' => 'p.prix_unitaire',
            'stock' => 'p.stock',
            'actif' => 'p.actif',
            'vendeur' => 'u.nom',
            'date' => 'p.created_at',
            'categorie' => 'cat.libelle',
        ];
        return $map[$tri] ?? 'p.created_at';
    }

    private function orderSqlProduitSolo(string $tri): string {
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
        return $map[$tri] ?? 'p.created_at';
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
        $sql = "SELECT p.*, cat.libelle AS categorie_libelle, cat.code AS categorie_code
                FROM produit p
                INNER JOIN categorie cat ON cat.idcategorie = p.idcategorie
                WHERE p.idproduit = :id LIMIT 1";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute(['id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function add(array $data): void {
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
            'prix_unitaire' => $data['prix_unitaire'],
            'stock' => (int) $data['stock'],
            'id_vendeur' => (int) $data['id_vendeur'],
            'actif' => isset($data['actif']) ? (int) (bool) $data['actif'] : 1,
        ];
        if ($hasPhoto) {
            $params['photo'] = $this->normalizePhotoPath($data['photo'] ?? null);
        }
        $st->execute($params);
    }

    public function update(int $id, array $data): void {
        $hasPhoto = $this->hasPhotoColumn();
        $sql = "UPDATE produit SET
                reference = :reference,
                designation = :designation,
                description = :description,
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
            'id' => $id,
            'reference' => $data['reference'],
            'designation' => $data['designation'],
            'description' => $data['description'] ?? null,
            'idcategorie' => (int) ($data['idcategorie'] ?? 1),
            'prix_unitaire' => $data['prix_unitaire'],
            'stock' => (int) $data['stock'],
            'id_vendeur' => (int) $data['id_vendeur'],
            'actif' => (int) (bool) ($data['actif'] ?? 1),
        ];
        if ($hasPhoto) {
            $params['photo'] = $this->normalizePhotoPath($data['photo'] ?? null);
        }
        $st->execute($params);
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
}
