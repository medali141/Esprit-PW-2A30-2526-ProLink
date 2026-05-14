<?php
/**
 * Contrôleur commandes — tables `commande` et `commande_produit`.
 */
require_once __DIR__ . '/../config.php';
<<<<<<< HEAD
require_once __DIR__ . '/../model/CommerceRegles.php';

class CommandeController {
    private ?bool $hasProduitPhotoColumn = null;
    private ?bool $hasCommandeTelephoneColumn = null;
    private ?bool $hasUserPointsColumn = null;
    private ?bool $hasCommandePaymentModeColumn = null;

    public function listAllAdmin(): array {
        return $this->listAllAdminFiltered('', 'date', 'desc', '');
    }

    /**
     * @param string $q       id commande, email, nom acheteur, ville, adresse…
     * @param string $tri     id|date|montant|statut|ville
     * @param string $ordre   asc|desc
     * @param string $statut  '' ou statut exact (brouillon, payee…)
     */
    public function listAllAdminFiltered(string $q, string $tri, string $ordre, string $statut): array {
        $where = ['1=1'];
        $params = [];
        $q = trim($q);
        if ($q !== '') {
            $where[] = '(CAST(c.idcommande AS CHAR) LIKE :q OR u.email LIKE :q OR u.nom LIKE :q OR u.prenom LIKE :q
                OR CONCAT(TRIM(IFNULL(u.prenom,\'\')), \' \', TRIM(IFNULL(u.nom,\'\'))) LIKE :q
                OR c.ville LIKE :q OR IFNULL(c.adresse_livraison,\'\') LIKE :q OR IFNULL(c.code_postal,\'\') LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }
        $allowedStatuts = CommerceRegles::allowedStatuts();
        if ($statut !== '' && in_array($statut, $allowedStatuts, true)) {
            $where[] = 'c.statut = :st';
            $params['st'] = $statut;
        }
        $orderBy = $this->orderSqlCommande($tri);
        $dir = strtoupper($ordre) === 'ASC' ? 'ASC' : 'DESC';
        $sql = "SELECT c.*, u.prenom, u.nom, u.email,
                       COALESCE(agg.nb_articles, 0) AS nb_articles,
                       COALESCE(agg.nb_lignes, 0) AS nb_lignes
                FROM commande c
                INNER JOIN user u ON u.iduser = c.id_acheteur
                LEFT JOIN (
                    SELECT idcommande, SUM(quantite) AS nb_articles, COUNT(*) AS nb_lignes
                    FROM commande_produit
                    GROUP BY idcommande
                ) agg ON agg.idcommande = c.idcommande
                WHERE " . implode(' AND ', $where) . "
                ORDER BY $orderBy $dir, c.idcommande DESC";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listByAcheteur(int $idAcheteur): array {
        return $this->listByAcheteurFiltered($idAcheteur, '', 'date', 'desc', '');
    }

    public function listByAcheteurFiltered(int $idAcheteur, string $q, string $tri, string $ordre, string $statut): array {
        $where = ['c.id_acheteur = :a'];
        $params = ['a' => $idAcheteur];
        $q = trim($q);
        if ($q !== '') {
            $where[] = '(CAST(c.idcommande AS CHAR) LIKE :q OR c.ville LIKE :q OR IFNULL(c.adresse_livraison,\'\') LIKE :q
                OR IFNULL(c.numero_suivi,\'\') LIKE :q OR CAST(c.montant_total AS CHAR) LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }
        $allowedStatuts = CommerceRegles::allowedStatuts();
        if ($statut !== '' && in_array($statut, $allowedStatuts, true)) {
            $where[] = 'c.statut = :st';
            $params['st'] = $statut;
        }
        $orderBy = $this->orderSqlCommande($tri);
        $dir = strtoupper($ordre) === 'ASC' ? 'ASC' : 'DESC';
        $sql = "SELECT c.*, COALESCE(agg.nb_articles, 0) AS nb_articles, COALESCE(agg.nb_lignes, 0) AS nb_lignes
                FROM commande c
                LEFT JOIN (
                    SELECT idcommande, SUM(quantite) AS nb_articles, COUNT(*) AS nb_lignes
                    FROM commande_produit
                    GROUP BY idcommande
                ) agg ON agg.idcommande = c.idcommande
                WHERE " . implode(' AND ', $where) . "
                ORDER BY $orderBy $dir, c.idcommande DESC";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    private function orderSqlCommande(string $tri): string {
        $map = [
            'id' => 'c.idcommande',
            'date' => 'c.date_commande',
            'montant' => 'c.montant_total',
            'statut' => 'c.statut',
            'ville' => 'c.ville',
        ];
        return $map[$tri] ?? 'c.date_commande';
    }

=======

class CommandeController {

    public function listAllAdmin(): array {
        $sql = "SELECT c.*, u.prenom, u.nom, u.email
                FROM commande c
                INNER JOIN user u ON u.iduser = c.id_acheteur
                ORDER BY c.date_commande DESC";
        $db = Config::getConnexion();
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listByAcheteur(int $idAcheteur): array {
        $sql = "SELECT * FROM commande WHERE id_acheteur = :a ORDER BY date_commande DESC";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute(['a' => $idAcheteur]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

>>>>>>> formation
    public function getById(int $id): ?array {
        $sql = "SELECT c.*, u.prenom, u.nom, u.email
                FROM commande c
                INNER JOIN user u ON u.iduser = c.id_acheteur
                WHERE c.idcommande = :id LIMIT 1";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute(['id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getLignes(int $idCommande): array {
<<<<<<< HEAD
        $photoSelect = $this->hasProduitPhotoColumn() ? 'p.photo' : 'NULL AS photo';
        $sql = "SELECT cp.*, p.reference, p.designation, p.id_vendeur, " . $photoSelect . ",
=======
        $sql = "SELECT cp.*, p.reference, p.designation, p.id_vendeur,
>>>>>>> formation
                       uv.prenom AS v_prenom, uv.nom AS v_nom
                FROM commande_produit cp
                INNER JOIN produit p ON p.idproduit = cp.idproduit
                INNER JOIN user uv ON uv.iduser = p.id_vendeur
                WHERE cp.idcommande = :id
                ORDER BY p.designation";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute(['id' => $idCommande]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
<<<<<<< HEAD
     * Frise de suivi logistique pour l’espace client (étapes lisibles après commande / paiement).
     *
     * @param array<string,mixed> $cmd Ligne `commande` + champs joint acheteur si besoin
     * @return array{cancelled:bool, message?:string, delivery_hint?:?string, steps:list<array<string,mixed>>}
     */
    public function getTrackingTimeline(array $cmd): array {
        $st = (string) ($cmd['statut'] ?? '');
        if ($st === 'annulee') {
            return [
                'cancelled' => true,
                'message' => 'Cette commande a été annulée. Aucune livraison ne sera effectuée.',
                'delivery_hint' => null,
                'steps' => [],
            ];
        }

        $phases = [
            [
                'key' => 'recue',
                'title' => 'Commande enregistrée',
                'subtitle' => 'Votre demande est enregistrée dans notre système.',
            ],
            [
                'key' => 'paiement',
                'title' => 'Paiement',
                'subtitle' => 'Validation du règlement avant préparation logistique.',
            ],
            [
                'key' => 'stock',
                'title' => 'Stock et disponibilité',
                'subtitle' => 'Contrôle des quantités et réservation des articles.',
            ],
            [
                'key' => 'preparation',
                'title' => 'Préparation et enlèvement',
                'subtitle' => 'Emballage, étiquetage et mise à disposition pour transport.',
            ],
            [
                'key' => 'transit',
                'title' => 'En cours de livraison',
                'subtitle' => 'Le colis est en cours d’acheminement vers votre adresse.',
            ],
            [
                'key' => 'livree',
                'title' => 'Livraison effectuée',
                'subtitle' => 'Le colis est arrivé à destination ou la livraison est finalisée.',
            ],
        ];

        $currentIdx = 0;
        switch ($st) {
            case 'brouillon':
                $currentIdx = 0;
                break;
            case 'en_attente_paiement':
                $currentIdx = 1;
                break;
            case 'payee':
                $currentIdx = 2;
                break;
            case 'en_preparation':
                $currentIdx = 3;
                break;
            case 'expediee':
                $currentIdx = 4;
                break;
            case 'livree':
                $currentIdx = 5;
                break;
            default:
                $currentIdx = 0;
        }

        $steps = [];
        foreach ($phases as $i => $p) {
            if ($st === 'livree') {
                $state = 'done';
            } elseif ($i < $currentIdx) {
                $state = 'done';
            } elseif ($i === $currentIdx) {
                $state = 'current';
            } else {
                $state = 'pending';
            }

            $meta = null;
            if ($p['key'] === 'paiement') {
                if ($st === 'en_attente_paiement') {
                    $meta = 'En attente de réception du paiement.';
                } elseif ($currentIdx > 1 || $st === 'livree' || in_array($st, ['payee', 'en_preparation', 'expediee'], true)) {
                    $meta = 'Paiement confirmé — passage en logistique.';
                }
            }
            if ($p['key'] === 'stock' && $st === 'payee') {
                $meta = 'Les articles sont disponibles ; préparation du colis à venir.';
            }
            if ($p['key'] === 'preparation' && $st === 'en_preparation') {
                $meta = 'Contrôle qualité et mise à disposition pour enlèvement en entrepôt.';
            }
            if ($p['key'] === 'transit') {
                $ns = trim((string) ($cmd['numero_suivi'] ?? ''));
                if ($st === 'expediee') {
                    $meta = $ns !== '' ? ('En cours de livraison — suivi transporteur : ' . $ns) : 'Colis pris en charge par le transporteur, acheminement en cours.';
                }
                if ($st === 'livree' && $ns !== '') {
                    $meta = 'Transport : ' . $ns;
                }
            }
            if ($p['key'] === 'livree' && $st === 'livree') {
                $de = (string) ($cmd['date_livraison_effective'] ?? '');
                $meta = $de !== '' ? ('Livraison enregistrée le ' . substr($de, 0, 16)) : 'Commande clôturée côté livraison.';
            }

            $steps[] = array_merge($p, ['state' => $state, 'meta' => $meta]);
        }

        $prevu = $cmd['date_livraison_prevue'] ?? null;
        $deliveryHint = null;
        if ($prevu !== null && $prevu !== '' && $st !== 'livree' && $st !== 'annulee') {
            $deliveryHint = 'Livraison estimée : ' . substr((string) $prevu, 0, 10);
        }

        return [
            'cancelled' => false,
            'delivery_hint' => $deliveryHint,
            'steps' => $steps,
        ];
    }

    /**
     * @param array<int,int> $cart idproduit => quantite
     * @param array{
     *   adresse_livraison:string,
     *   code_postal:string,
     *   ville:string,
     *   pays?:string,
     *   telephone_livraison?:string,
     *   notes?:string,
     *   use_points?:bool,
     *   payment_method?:string,
     *   card_verified?:bool
     * } $livraison
=======
     * @param array<int,int> $cart idproduit => quantite
     * @param array{adresse_livraison:string,code_postal:string,ville:string,pays?:string,notes?:string} $livraison
>>>>>>> formation
     */
    public function createFromCart(int $acheteurId, array $cart, array $livraison): int {
        if (empty($cart)) {
            throw new InvalidArgumentException('Panier vide');
        }
<<<<<<< HEAD
        $usePoints = !empty($livraison['use_points']);
        $paymentMethod = strtolower(trim((string) ($livraison['payment_method'] ?? 'cash_on_delivery')));
        $cardVerified = !empty($livraison['card_verified']);
        $allowedPaymentMethods = ['card', 'cash_on_delivery'];
        if (!in_array($paymentMethod, $allowedPaymentMethods, true)) {
            throw new InvalidArgumentException('Mode de paiement invalide');
        }
        $livraison = $this->normalizeLivraisonData($livraison);
        $hasTel = $this->hasCommandeTelephoneColumn();
        $hasPoints = $this->hasUserPointsColumn();
        $hasPaymentMode = $this->hasCommandePaymentModeColumn();
=======
>>>>>>> formation

        $db = Config::getConnexion();
        $db->beginTransaction();

        try {
            $total = 0.0;
            $resolved = [];

            foreach ($cart as $idProduit => $qte) {
                $idProduit = (int) $idProduit;
                $qte = (int) $qte;
                if ($idProduit <= 0 || $qte <= 0) {
                    continue;
                }

                $st = $db->prepare("SELECT idproduit, prix_unitaire, stock, actif FROM produit WHERE idproduit = :id FOR UPDATE");
                $st->execute(['id' => $idProduit]);
                $p = $st->fetch(PDO::FETCH_ASSOC);
                if (!$p || !(int) $p['actif']) {
                    throw new RuntimeException('Produit indisponible : #' . $idProduit);
                }
                if ((int) $p['stock'] < $qte) {
                    throw new RuntimeException('Stock insuffisant pour le produit #' . $idProduit);
                }

                $pu = (float) $p['prix_unitaire'];
                $lineTotal = $pu * $qte;
                $total += $lineTotal;
                $resolved[] = ['id' => $idProduit, 'qte' => $qte, 'pu' => $pu];
            }

            if (empty($resolved)) {
                throw new InvalidArgumentException('Panier invalide');
            }

<<<<<<< HEAD
            $discountAmount = 0.0;
            $spentPoints = 0;
            if ($hasPoints && $usePoints) {
                $stPts = $db->prepare("SELECT points_fidelite FROM user WHERE iduser = :id FOR UPDATE");
                $stPts->execute(['id' => $acheteurId]);
                $ptsRow = $stPts->fetch(PDO::FETCH_ASSOC);
                $availablePoints = max(0, (int) ($ptsRow['points_fidelite'] ?? 0));
                if ($availablePoints > 0 && $total > 0) {
                    $maxSpendableByTotal = (int) floor(($total / CommerceRegles::DINAR_PER_POINT) + 1e-9);
                    $spentPoints = min($availablePoints, $maxSpendableByTotal);
                    $discountAmount = CommerceRegles::dinarFromPoints($spentPoints);
                }
            }
            $finalTotal = max(0.0, round($total - $discountAmount, 2));
            // Si les points couvrent integralement la commande, aucun paiement externe n'est requis.
            if ($finalTotal <= 0.00001) {
                $initialStatut = 'payee';
            } else {
                if ($paymentMethod === 'card') {
                    $initialStatut = $cardVerified ? 'payee' : 'en_attente_paiement';
                } else {
                    $initialStatut = 'en_attente_paiement';
                }
            }

            $insertColumns = "id_acheteur, statut, montant_total, notes, adresse_livraison, code_postal, ville, pays";
            $insertValues = ":acheteur, :st, :total, :notes, :adr, :cp, :ville, :pays";
            if ($hasTel) {
                $insertColumns .= ", telephone_livraison";
                $insertValues .= ", :tel";
            }
            if ($hasPaymentMode) {
                $insertColumns .= ", mode_paiement";
                $insertValues .= ", :pm";
            }
            $ins = $db->prepare("INSERT INTO commande ($insertColumns) VALUES ($insertValues)");
            $params = [
                'acheteur' => $acheteurId,
                'st' => $initialStatut,
                'total' => $finalTotal,
                'notes' => $livraison['notes'],
                'adr' => $livraison['adresse_livraison'],
                'cp' => $livraison['code_postal'],
                'ville' => $livraison['ville'],
                'pays' => $livraison['pays'],
            ];
            if ($hasTel) {
                $params['tel'] = $livraison['telephone_livraison'];
            }
            if ($hasPaymentMode) {
                $params['pm'] = $paymentMethod;
            }
            $ins->execute($params);
=======
            $pays = $livraison['pays'] ?? 'Tunisie';
            $notes = $livraison['notes'] ?? null;

            $ins = $db->prepare(
                "INSERT INTO commande (id_acheteur, statut, montant_total, notes, adresse_livraison, code_postal, ville, pays)
                 VALUES (:acheteur, 'en_attente_paiement', :total, :notes, :adr, :cp, :ville, :pays)"
            );
            $ins->execute([
                'acheteur' => $acheteurId,
                'total' => round($total, 2),
                'notes' => $notes,
                'adr' => $livraison['adresse_livraison'],
                'cp' => $livraison['code_postal'],
                'ville' => $livraison['ville'],
                'pays' => $pays,
            ]);
>>>>>>> formation
            $idCommande = (int) $db->lastInsertId();

            $insL = $db->prepare(
                "INSERT INTO commande_produit (idcommande, idproduit, quantite, prix_unitaire)
                 VALUES (:idc, :idp, :qte, :pu)"
            );
            $upd = $db->prepare("UPDATE produit SET stock = stock - :q WHERE idproduit = :id");

            foreach ($resolved as $line) {
                $insL->execute([
                    'idc' => $idCommande,
                    'idp' => $line['id'],
                    'qte' => $line['qte'],
                    'pu' => $line['pu'],
                ]);
                $upd->execute(['q' => $line['qte'], 'id' => $line['id']]);
            }

<<<<<<< HEAD
            if ($hasPoints) {
                // Les points utilises sont debités a la commande.
                // Les points gagnés sont credites uniquement a la livraison.
                if ($spentPoints > 0) {
                    $upPts = $db->prepare("UPDATE user SET points_fidelite = GREATEST(0, COALESCE(points_fidelite, 0) + :p) WHERE iduser = :id");
                    $upPts->execute(['p' => -$spentPoints, 'id' => $acheteurId]);
                    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user']) && (int) ($_SESSION['user']['iduser'] ?? 0) === $acheteurId) {
                        $_SESSION['user']['points_fidelite'] = max(0, (int) ($_SESSION['user']['points_fidelite'] ?? 0) - $spentPoints);
                    }
                }
            }

=======
>>>>>>> formation
            $db->commit();
            return $idCommande;
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function updateMeta(
        int $id,
        string $statut,
        ?string $numeroSuivi,
        ?string $datePrevue,
        ?string $dateEffective,
        ?string $notes
    ): void {
<<<<<<< HEAD
        $allowed = CommerceRegles::allowedStatuts();
        if (!in_array($statut, $allowed, true)) {
            throw new InvalidArgumentException('Statut invalide');
        }
        $numeroSuivi = trim((string) $numeroSuivi);
        if ($numeroSuivi !== '') {
            if (strlen($numeroSuivi) > 120) {
                throw new InvalidArgumentException('Numéro de suivi trop long');
            }
            if (!preg_match('/^[A-Za-z0-9 _\\-\\/\\.]+$/', $numeroSuivi)) {
                throw new InvalidArgumentException('Numéro de suivi invalide');
            }
        }
        $datePrevue = $this->normalizeDateOnly($datePrevue, 'Date de livraison prévue invalide');
        $dateEffective = $this->normalizeDateTime($dateEffective, 'Date de livraison effective invalide');
        if ($datePrevue !== null && $dateEffective !== null && substr($dateEffective, 0, 10) < $datePrevue) {
            throw new InvalidArgumentException('La livraison effective ne peut pas être avant la date prévue');
        }
        $notes = trim((string) $notes);
        if (strlen($notes) > 500) {
            throw new InvalidArgumentException('Notes : maximum 500 caractères');
        }
=======
        $allowed = [
            'brouillon', 'en_attente_paiement', 'payee', 'en_preparation',
            'expediee', 'livree', 'annulee',
        ];
        if (!in_array($statut, $allowed, true)) {
            throw new InvalidArgumentException('Statut invalide');
        }
>>>>>>> formation

        $db = Config::getConnexion();
        $db->beginTransaction();

        try {
<<<<<<< HEAD
            $st = $db->prepare("SELECT statut, id_acheteur, montant_total FROM commande WHERE idcommande = :id FOR UPDATE");
=======
            $st = $db->prepare("SELECT statut FROM commande WHERE idcommande = :id FOR UPDATE");
>>>>>>> formation
            $st->execute(['id' => $id]);
            $old = $st->fetch(PDO::FETCH_ASSOC);
            if (!$old) {
                throw new RuntimeException('Commande introuvable');
            }
            $oldStatut = $old['statut'];
<<<<<<< HEAD
            if (!CommerceRegles::canTransitionStatut((string) $oldStatut, $statut)) {
                throw new InvalidArgumentException('Transition de statut non autorisée');
            }
=======
>>>>>>> formation

            if ($statut === 'annulee' && $oldStatut !== 'annulee') {
                $this->restoreStockForCommande($db, $id);
            }

<<<<<<< HEAD
=======
            if ($oldStatut === 'annulee' && $statut !== 'annulee') {
                $this->decrementStockForCommande($db, $id);
            }

>>>>>>> formation
            $up = $db->prepare(
                "UPDATE commande SET
                    statut = :st,
                    numero_suivi = :ns,
                    date_livraison_prevue = :dp,
                    date_livraison_effective = :de,
                    notes = :notes
                 WHERE idcommande = :id"
            );
            $up->execute([
                'st' => $statut,
<<<<<<< HEAD
                'ns' => $numeroSuivi !== '' ? $numeroSuivi : null,
                'dp' => $datePrevue,
                'de' => $dateEffective,
                'notes' => $notes,
                'id' => $id,
            ]);

            // Credit des points fidelite seulement lors du passage vers "livree".
            if ($oldStatut !== 'livree' && $statut === 'livree') {
                $this->creditLoyaltyPointsOnDelivery(
                    $db,
                    (int) ($old['id_acheteur'] ?? 0),
                    (float) ($old['montant_total'] ?? 0)
                );
            }

=======
                'ns' => ($numeroSuivi !== null && $numeroSuivi !== '') ? $numeroSuivi : null,
                'dp' => ($datePrevue !== null && $datePrevue !== '') ? $datePrevue : null,
                'de' => ($dateEffective !== null && $dateEffective !== '') ? $dateEffective : null,
                'notes' => $notes ?? '',
                'id' => $id,
            ]);

>>>>>>> formation
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    private function restoreStockForCommande(PDO $db, int $idCommande): void {
        $st = $db->prepare("SELECT idproduit, quantite FROM commande_produit WHERE idcommande = :id");
        $st->execute(['id' => $idCommande]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        $upd = $db->prepare("UPDATE produit SET stock = stock + :q WHERE idproduit = :idp");
        foreach ($rows as $r) {
            $upd->execute(['q' => (int) $r['quantite'], 'idp' => (int) $r['idproduit']]);
        }
    }

<<<<<<< HEAD
    private function creditLoyaltyPointsOnDelivery(PDO $db, int $idAcheteur, float $montantTotal): void {
        if ($idAcheteur <= 0 || !$this->hasUserPointsColumn()) {
            return;
        }
        $earnedPoints = CommerceRegles::pointsFromAmount(max(0.0, $montantTotal));
        if ($earnedPoints <= 0) {
            return;
        }
        $upPts = $db->prepare("UPDATE user SET points_fidelite = GREATEST(0, COALESCE(points_fidelite, 0) + :p) WHERE iduser = :id");
        $upPts->execute(['p' => $earnedPoints, 'id' => $idAcheteur]);
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user']) && (int) ($_SESSION['user']['iduser'] ?? 0) === $idAcheteur) {
            $_SESSION['user']['points_fidelite'] = max(0, (int) ($_SESSION['user']['points_fidelite'] ?? 0) + $earnedPoints);
        }
    }

    /**
     * @param array{adresse_livraison?:string,code_postal?:string,ville?:string,pays?:string,telephone_livraison?:string,notes?:?string} $livraison
     * @return array{adresse_livraison:string,code_postal:string,ville:string,pays:string,telephone_livraison:?string,notes:?string}
     */
    private function normalizeLivraisonData(array $livraison): array {
        $adr = trim((string) ($livraison['adresse_livraison'] ?? ''));
        $cp = trim((string) ($livraison['code_postal'] ?? ''));
        $ville = trim((string) ($livraison['ville'] ?? ''));
        $pays = trim((string) ($livraison['pays'] ?? 'Tunisie'));
        $tel = trim((string) ($livraison['telephone_livraison'] ?? ''));
        $notes = trim((string) ($livraison['notes'] ?? ''));

        if (strlen($adr) < 5 || strlen($adr) > 300) {
            throw new InvalidArgumentException('Adresse : entre 5 et 300 caractères');
        }
        if (strlen($cp) < 2 || strlen($cp) > 20 || !preg_match('/^[\\w\\s-]+$/u', $cp)) {
            throw new InvalidArgumentException('Code postal invalide');
        }
        if (strlen($ville) < 2 || strlen($ville) > 100) {
            throw new InvalidArgumentException('Ville : entre 2 et 100 caractères');
        }
        if ($pays === '') {
            $pays = 'Tunisie';
        }
        if (strlen($pays) > 100) {
            throw new InvalidArgumentException('Pays : maximum 100 caractères');
        }
        if (!preg_match('/^\d{8}$/', $tel)) {
            throw new InvalidArgumentException('Téléphone livraison invalide (8 chiffres)');
        }
        if (strlen($notes) > 500) {
            throw new InvalidArgumentException('Notes : maximum 500 caractères');
        }

        return [
            'adresse_livraison' => $adr,
            'code_postal' => $cp,
            'ville' => $ville,
            'pays' => $pays,
            'telephone_livraison' => $tel,
            'notes' => $notes !== '' ? $notes : null,
        ];
    }

    private function normalizeDateOnly(?string $value, string $error): ?string {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (!preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $value)) {
            throw new InvalidArgumentException($error);
        }
        [$y, $m, $d] = array_map('intval', explode('-', $value));
        if (!checkdate($m, $d, $y)) {
            throw new InvalidArgumentException($error);
        }
        return $value;
    }

    private function normalizeDateTime(?string $value, string $error): ?string {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $value)) {
            $value .= ' 00:00:00';
        }
        $value = str_replace('T', ' ', $value);
        if (preg_match('/^\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}$/', $value)) {
            $value .= ':00';
        }
        if (!preg_match('/^\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}$/', $value)) {
            throw new InvalidArgumentException($error);
        }
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $value);
        if (!$dt || $dt->format('Y-m-d H:i:s') !== $value) {
            throw new InvalidArgumentException($error);
        }
        return $value;
=======
    private function decrementStockForCommande(PDO $db, int $idCommande): void {
        $st = $db->prepare("SELECT idproduit, quantite FROM commande_produit WHERE idcommande = :id");
        $st->execute(['id' => $idCommande]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        $sel = $db->prepare("SELECT stock FROM produit WHERE idproduit = :id FOR UPDATE");
        $upd = $db->prepare("UPDATE produit SET stock = stock - :q WHERE idproduit = :idp");
        foreach ($rows as $r) {
            $q = (int) $r['quantite'];
            $idp = (int) $r['idproduit'];
            $sel->execute(['id' => $idp]);
            $stock = (int) $sel->fetchColumn();
            if ($stock < $q) {
                throw new RuntimeException('Stock insuffisant pour réactiver la commande');
            }
            $upd->execute(['q' => $q, 'idp' => $idp]);
        }
>>>>>>> formation
    }

    public function countAll(): int {
        $db = Config::getConnexion();
        return (int) $db->query("SELECT COUNT(*) FROM commande")->fetchColumn();
    }

<<<<<<< HEAD
    /** @return array{total_commandes:int,a_payer:int,en_cours:int,livrees:int,ca_total:float,ca_mois:float,panier_moyen:float} */
    public function getCommerceKpis(): array {
        $db = Config::getConnexion();
        $row = $db->query(
            "SELECT
                COUNT(*) AS total_commandes,
                SUM(CASE WHEN statut = 'en_attente_paiement' THEN 1 ELSE 0 END) AS a_payer,
                SUM(CASE WHEN statut IN ('payee','en_preparation','expediee') THEN 1 ELSE 0 END) AS en_cours,
                SUM(CASE WHEN statut = 'livree' THEN 1 ELSE 0 END) AS livrees,
                COALESCE(SUM(montant_total), 0) AS ca_total,
                COALESCE(SUM(CASE WHEN date_commande >= DATE_FORMAT(NOW(), '%Y-%m-01') THEN montant_total ELSE 0 END), 0) AS ca_mois,
                COALESCE(AVG(montant_total), 0) AS panier_moyen
             FROM commande"
        )->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_commandes' => (int) ($row['total_commandes'] ?? 0),
            'a_payer' => (int) ($row['a_payer'] ?? 0),
            'en_cours' => (int) ($row['en_cours'] ?? 0),
            'livrees' => (int) ($row['livrees'] ?? 0),
            'ca_total' => (float) ($row['ca_total'] ?? 0),
            'ca_mois' => (float) ($row['ca_mois'] ?? 0),
            'panier_moyen' => (float) ($row['panier_moyen'] ?? 0),
        ];
    }

    /** @return list<array<string,mixed>> */
    public function topProduitsVendus(int $limit = 5): array {
        $limit = max(1, min(20, $limit));
        $sql = "SELECT p.idproduit, p.reference, p.designation,
                       SUM(cp.quantite) AS qte_vendue,
                       SUM(cp.quantite * cp.prix_unitaire) AS ca_produit
                FROM commande_produit cp
                INNER JOIN produit p ON p.idproduit = cp.idproduit
                GROUP BY p.idproduit, p.reference, p.designation
                ORDER BY qte_vendue DESC, ca_produit DESC
                LIMIT " . $limit;
        $db = Config::getConnexion();
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

=======
>>>>>>> formation
    public function countProduitsActifs(): int {
        $db = Config::getConnexion();
        return (int) $db->query("SELECT COUNT(*) FROM produit WHERE actif = 1")->fetchColumn();
    }

    /** Commandes contenant au moins un produit du vendeur. */
    public function listByVendeur(int $idVendeur): array {
<<<<<<< HEAD
        return $this->listByVendeurFiltered($idVendeur, '', 'date', 'desc', '');
    }

    public function listByVendeurFiltered(int $idVendeur, string $q, string $tri, string $ordre, string $statut): array {
        $where = ['agg.idcommande IS NOT NULL'];
        $params = ['v' => $idVendeur];
        $q = trim($q);
        if ($q !== '') {
            $where[] = '(CAST(c.idcommande AS CHAR) LIKE :q OR u.email LIKE :q OR u.nom LIKE :q OR u.prenom LIKE :q
                OR CONCAT(TRIM(IFNULL(u.prenom,\'\')), \' \', TRIM(IFNULL(u.nom,\'\'))) LIKE :q
                OR c.ville LIKE :q OR IFNULL(c.numero_suivi,\'\') LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }
        $allowedStatuts = CommerceRegles::allowedStatuts();
        if ($statut !== '' && in_array($statut, $allowedStatuts, true)) {
            $where[] = 'c.statut = :st';
            $params['st'] = $statut;
        }
        $orderBy = $this->orderSqlCommande($tri);
        $dir = strtoupper($ordre) === 'ASC' ? 'ASC' : 'DESC';
        $sql = "SELECT c.*, u.prenom, u.nom, u.email,
                       COALESCE(agg.nb_articles_vendeur, 0) AS nb_articles_vendeur,
                       COALESCE(agg.nb_lignes_vendeur, 0) AS nb_lignes_vendeur,
                       COALESCE(agg.montant_vendeur, 0) AS montant_vendeur
                FROM commande c
                INNER JOIN user u ON u.iduser = c.id_acheteur
                LEFT JOIN (
                    SELECT cp.idcommande,
                           SUM(cp.quantite) AS nb_articles_vendeur,
                           COUNT(cp.idproduit) AS nb_lignes_vendeur,
                           SUM(cp.quantite * cp.prix_unitaire) AS montant_vendeur
                    FROM commande_produit cp
                    INNER JOIN produit p ON p.idproduit = cp.idproduit
                    WHERE p.id_vendeur = :v
                    GROUP BY cp.idcommande
                ) agg ON agg.idcommande = c.idcommande
                WHERE " . implode(' AND ', $where) . "
                ORDER BY $orderBy $dir, c.idcommande DESC";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Passe la commande en « expediee » lorsque l’enlèvement au dépôt est terminé (parcours simulé côté acheteur).
     * Retourne true si statut mis à jour (ou déjà expediee/livree), false sinon.
     */
    public function markAsInDeliveryByAcheteur(int $idCommande, int $idAcheteur): bool {
        $db = Config::getConnexion();
        $db->beginTransaction();
        try {
            $sel = $db->prepare("SELECT id_acheteur, statut FROM commande WHERE idcommande = :id FOR UPDATE");
            $sel->execute(['id' => $idCommande]);
            $row = $sel->fetch(PDO::FETCH_ASSOC);
            if (!$row || (int) $row['id_acheteur'] !== $idAcheteur) {
                $db->rollBack();
                return false;
            }

            $current = (string) ($row['statut'] ?? '');
            if ($current === 'expediee' || $current === 'livree') {
                $db->commit();
                return true;
            }
            if (!in_array($current, ['payee', 'en_preparation'], true)) {
                $db->rollBack();
                return false;
            }

            $up = $db->prepare("UPDATE commande SET statut = 'expediee' WHERE idcommande = :id");
            $up->execute(['id' => $idCommande]);
            $db->commit();
            return true;
        } catch (Throwable $e) {
            $db->rollBack();
            return false;
        }
    }

    /**
     * Confirme le paiement carte apres verification (OTP + 2FA), cote acheteur.
     */
    public function confirmCardPayment(int $idCommande, int $idAcheteur): bool {
        $db = Config::getConnexion();
        $hasPaymentMode = $this->hasCommandePaymentModeColumn();
        $db->beginTransaction();
        try {
            $modeSelect = $hasPaymentMode ? ", mode_paiement" : "";
            $st = $db->prepare("SELECT id_acheteur, statut" . $modeSelect . " FROM commande WHERE idcommande = :id FOR UPDATE");
            $st->execute(['id' => $idCommande]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row || (int) ($row['id_acheteur'] ?? 0) !== $idAcheteur) {
                $db->rollBack();
                return false;
            }
            $currentStatut = (string) ($row['statut'] ?? '');
            if ($hasPaymentMode) {
                $mode = (string) ($row['mode_paiement'] ?? '');
                if ($mode !== 'card') {
                    $db->rollBack();
                    return false;
                }
            }
            if ($currentStatut === 'payee' || $currentStatut === 'en_preparation' || $currentStatut === 'expediee' || $currentStatut === 'livree') {
                $db->commit();
                return true;
            }
            if ($currentStatut !== 'en_attente_paiement') {
                $db->rollBack();
                return false;
            }
            $up = $db->prepare("UPDATE commande SET statut = 'payee' WHERE idcommande = :id");
            $up->execute(['id' => $idCommande]);
            $db->commit();
            return true;
        } catch (Throwable $e) {
            $db->rollBack();
            return false;
        }
    }

    private function hasProduitPhotoColumn(): bool {
        if ($this->hasProduitPhotoColumn !== null) {
            return $this->hasProduitPhotoColumn;
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
        $this->hasProduitPhotoColumn = $exists;
        return $this->hasProduitPhotoColumn;
    }

    private function hasCommandeTelephoneColumn(): bool {
        if ($this->hasCommandeTelephoneColumn !== null) {
            return $this->hasCommandeTelephoneColumn;
        }
        $db = Config::getConnexion();
        $st = $db->query("SHOW COLUMNS FROM commande LIKE 'telephone_livraison'");
        $exists = (bool) $st->fetch(PDO::FETCH_ASSOC);
        if (!$exists) {
            try {
                $db->exec("ALTER TABLE commande ADD COLUMN telephone_livraison VARCHAR(20) NULL AFTER pays");
                $exists = true;
            } catch (Throwable $e) {
                $exists = false;
            }
        }
        $this->hasCommandeTelephoneColumn = $exists;
        return $this->hasCommandeTelephoneColumn;
    }

    private function hasUserPointsColumn(): bool {
        if ($this->hasUserPointsColumn !== null) {
            return $this->hasUserPointsColumn;
        }
        $db = Config::getConnexion();
        $st = $db->query("SHOW COLUMNS FROM user LIKE 'points_fidelite'");
        $exists = (bool) $st->fetch(PDO::FETCH_ASSOC);
        if (!$exists) {
            try {
                $db->exec("ALTER TABLE user ADD COLUMN points_fidelite INT NOT NULL DEFAULT 0 AFTER age");
                $exists = true;
            } catch (Throwable $e) {
                $exists = false;
            }
        }
        $this->hasUserPointsColumn = $exists;
        return $this->hasUserPointsColumn;
    }

    private function hasCommandePaymentModeColumn(): bool {
        if ($this->hasCommandePaymentModeColumn !== null) {
            return $this->hasCommandePaymentModeColumn;
        }
        $db = Config::getConnexion();
        $st = $db->query("SHOW COLUMNS FROM commande LIKE 'mode_paiement'");
        $exists = (bool) $st->fetch(PDO::FETCH_ASSOC);
        if (!$exists) {
            try {
                $db->exec("ALTER TABLE commande ADD COLUMN mode_paiement VARCHAR(30) NOT NULL DEFAULT 'cash_on_delivery' AFTER pays");
                $exists = true;
            } catch (Throwable $e) {
                $exists = false;
            }
        }
        $this->hasCommandePaymentModeColumn = $exists;
        return $this->hasCommandePaymentModeColumn;
    }
=======
        $sql = "SELECT DISTINCT c.*, u.prenom, u.nom, u.email
                FROM commande c
                INNER JOIN user u ON u.iduser = c.id_acheteur
                INNER JOIN commande_produit cp ON cp.idcommande = c.idcommande
                INNER JOIN produit p ON p.idproduit = cp.idproduit
                WHERE p.id_vendeur = :v
                ORDER BY c.date_commande DESC";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute(['v' => $idVendeur]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
>>>>>>> formation
}
