<?php
/**
 * Budgets achats et indicateurs fournisseurs (vendeurs).
 */
require_once __DIR__ . '/../config.php';

class AchatsCommerceController {
    private ?bool $budgetTableReady = null;

    /** Statuts pris en compte pour l’engagement (montants validés après paiement). */
    private const STATUTS_ENGAGES = ['payee', 'en_preparation', 'expediee', 'livree'];

    private function ensureBudgetTable(): void {
        if ($this->budgetTableReady === true) {
            return;
        }
        $db = Config::getConnexion();
        $db->exec(
            "CREATE TABLE IF NOT EXISTS `budget_achat` (
              `idbudget` int(11) NOT NULL AUTO_INCREMENT,
              `libelle` varchar(150) NOT NULL,
              `annee` int(11) NOT NULL,
              `idcategorie` int(11) DEFAULT NULL COMMENT 'NULL = enveloppe globale',
              `montant_alloue` decimal(12,2) NOT NULL DEFAULT 0.00,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`idbudget`),
              KEY `idx_budget_annee` (`annee`),
              KEY `idx_budget_categorie` (`idcategorie`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        try {
            $db->exec(
                "ALTER TABLE `budget_achat`
                 ADD CONSTRAINT `fk_budget_categorie`
                 FOREIGN KEY (`idcategorie`) REFERENCES `categorie` (`idcategorie`)
                 ON DELETE SET NULL ON UPDATE CASCADE"
            );
        } catch (Throwable $e) {
            // Déjà présente ou catégorie absente — ignoré.
        }
        $this->budgetTableReady = true;
    }

    /** @return list<array<string,mixed>> */
    public function listCategories(): array {
        $db = Config::getConnexion();
        $st = $db->query('SELECT idcategorie, code, libelle, ordre FROM categorie ORDER BY ordre ASC, libelle ASC');
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return list<array<string,mixed>> */
    public function listBudgets(int $annee): array {
        $this->ensureBudgetTable();
        $db = Config::getConnexion();
        $st = $db->prepare(
            'SELECT b.*, cat.libelle AS categorie_libelle, cat.code AS categorie_code
             FROM budget_achat b
             LEFT JOIN categorie cat ON cat.idcategorie = b.idcategorie
             WHERE b.annee = :y
             ORDER BY b.idcategorie IS NULL DESC, cat.ordre ASC, b.libelle ASC'
        );
        $st->execute(['y' => $annee]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function addBudget(string $libelle, int $annee, ?int $idcategorie, float $montant): void {
        $this->ensureBudgetTable();
        $libelle = trim($libelle);
        if ($libelle === '' || strlen($libelle) > 150) {
            throw new InvalidArgumentException('Libellé budget invalide');
        }
        if ($annee < 2000 || $annee > 2100) {
            throw new InvalidArgumentException('Année invalide');
        }
        if ($montant < 0 || $montant > 999999999.99) {
            throw new InvalidArgumentException('Montant invalide');
        }
        $idcategorie = $idcategorie !== null && $idcategorie > 0 ? $idcategorie : null;
        if ($idcategorie !== null) {
            $db = Config::getConnexion();
            $chk = $db->prepare('SELECT 1 FROM categorie WHERE idcategorie = :id LIMIT 1');
            $chk->execute(['id' => $idcategorie]);
            if (!$chk->fetchColumn()) {
                throw new InvalidArgumentException('Catégorie inconnue');
            }
        }
        $db = Config::getConnexion();
        $ins = $db->prepare(
            'INSERT INTO budget_achat (libelle, annee, idcategorie, montant_alloue) VALUES (:l, :a, :c, :m)'
        );
        $ins->execute([
            'l' => $libelle,
            'a' => $annee,
            'c' => $idcategorie,
            'm' => round($montant, 2),
        ]);
    }

    public function deleteBudget(int $idbudget): void {
        $this->ensureBudgetTable();
        if ($idbudget <= 0) {
            throw new InvalidArgumentException('Budget invalide');
        }
        $db = Config::getConnexion();
        $st = $db->prepare('DELETE FROM budget_achat WHERE idbudget = :id LIMIT 1');
        $st->execute(['id' => $idbudget]);
    }

    /**
     * Montants engagés / réalisés pour une année (global ou par catégorie).
     *
     * @return array{engage:float,realise:float}
     */
    public function montantsCommandesPourPeriode(int $annee, ?int $idcategorie): array {
        return [
            'engage' => $this->sumMontantEngageAnnee($annee, $idcategorie),
            'realise' => $this->sumMontantRealiseAnnee($annee, $idcategorie),
        ];
    }

    private function sumMontantEngageAnnee(int $annee, ?int $idcategorie): float {
        $db = Config::getConnexion();
        $stEng = implode(',', array_map(fn ($s) => $db->quote($s), self::STATUTS_ENGAGES));

        if ($idcategorie === null) {
            $sql = "SELECT COALESCE(SUM(c.montant_total), 0)
                    FROM commande c
                    WHERE YEAR(c.date_commande) = :y AND c.statut IN ($stEng)";
            $q = $db->prepare($sql);
            $q->execute(['y' => $annee]);
            return (float) $q->fetchColumn();
        }

        $sql = "SELECT COALESCE(SUM(cp.quantite * cp.prix_unitaire), 0)
                FROM commande c
                INNER JOIN commande_produit cp ON cp.idcommande = c.idcommande
                INNER JOIN produit p ON p.idproduit = cp.idproduit AND p.idcategorie = :cat
                WHERE YEAR(c.date_commande) = :y AND c.statut IN ($stEng)";
        $q = $db->prepare($sql);
        $q->execute(['y' => $annee, 'cat' => $idcategorie]);
        return (float) $q->fetchColumn();
    }

    private function sumMontantRealiseAnnee(int $annee, ?int $idcategorie): float {
        $db = Config::getConnexion();
        if ($idcategorie === null) {
            $sql = "SELECT COALESCE(SUM(c.montant_total), 0)
                    FROM commande c
                    WHERE YEAR(c.date_commande) = :y AND c.statut = 'livree'";
            $q = $db->prepare($sql);
            $q->execute(['y' => $annee]);
            return (float) $q->fetchColumn();
        }

        $sql = "SELECT COALESCE(SUM(cp.quantite * cp.prix_unitaire), 0)
                FROM commande c
                INNER JOIN commande_produit cp ON cp.idcommande = c.idcommande
                INNER JOIN produit p ON p.idproduit = cp.idproduit AND p.idcategorie = :cat
                WHERE YEAR(c.date_commande) = :y AND c.statut = 'livree'";
        $q = $db->prepare($sql);
        $q->execute(['y' => $annee, 'cat' => $idcategorie]);
        return (float) $q->fetchColumn();
    }

    /**
     * Récapitulatif pour chaque ligne budget + totaux.
     *
     * @return array{rows:list<array<string,mixed>>,totaux:array<string,float>}
     */
    public function getBudgetDashboard(int $annee): array {
        $budgets = $this->listBudgets($annee);
        $rows = [];
        $sumAlloue = 0.0;

        foreach ($budgets as $b) {
            $idCat = isset($b['idcategorie']) && $b['idcategorie'] !== null ? (int) $b['idcategorie'] : null;
            $m = $this->montantsCommandesPourPeriode($annee, $idCat);
            $alloue = (float) ($b['montant_alloue'] ?? 0);
            $engage = $m['engage'];
            $realise = $m['realise'];
            $reste = $alloue - $engage;
            $pctEngage = $alloue > 0 ? min(100, round(100 * $engage / $alloue, 1)) : 0.0;

            $rows[] = array_merge($b, [
                'montant_engage' => $engage,
                'montant_realise' => $realise,
                'montant_reste' => $reste,
                'pct_engage' => $pctEngage,
                'alerte_depassement' => $engage > $alloue + 1e-6,
            ]);
            $sumAlloue += $alloue;
        }

        $plateforme = $this->montantsCommandesPourPeriode($annee, null);

        return [
            'rows' => $rows,
            'totaux' => [
                'alloue_enveloppes' => $sumAlloue,
                'engage_plateforme' => $plateforme['engage'],
                'realise_plateforme' => $plateforme['realise'],
            ],
        ];
    }

    /**
     * Indicateurs fournisseurs : commandes, CA lignes, ponctualité des livraisons clôturées.
     *
     * @return list<array<string,mixed>>
     */
    public function getFournisseurIndicateurs(): array {
        $db = Config::getConnexion();
        $sql = "
            SELECT
                v.id_vendeur,
                u.prenom,
                u.nom,
                u.email,
                COUNT(*) AS nb_commandes,
                COALESCE(SUM(v.ca_cmd), 0) AS ca_vendeur,
                SUM(v.flag_eval) AS nb_evalues,
                SUM(v.flag_ok) AS nb_a_temps,
                SUM(v.flag_late) AS nb_en_retard
            FROM (
                SELECT
                    p.id_vendeur,
                    c.idcommande,
                    SUM(cp.quantite * cp.prix_unitaire) AS ca_cmd,
                    MAX(CASE
                        WHEN c.statut = 'livree'
                             AND c.date_livraison_prevue IS NOT NULL
                             AND c.date_livraison_effective IS NOT NULL
                        THEN 1 ELSE 0 END) AS flag_eval,
                    MAX(CASE
                        WHEN c.statut = 'livree'
                             AND c.date_livraison_prevue IS NOT NULL
                             AND c.date_livraison_effective IS NOT NULL
                             AND DATE(c.date_livraison_effective) <= c.date_livraison_prevue
                        THEN 1 ELSE 0 END) AS flag_ok,
                    MAX(CASE
                        WHEN c.statut = 'livree'
                             AND c.date_livraison_prevue IS NOT NULL
                             AND c.date_livraison_effective IS NOT NULL
                             AND DATE(c.date_livraison_effective) > c.date_livraison_prevue
                        THEN 1 ELSE 0 END) AS flag_late
                FROM commande c
                INNER JOIN commande_produit cp ON cp.idcommande = c.idcommande
                INNER JOIN produit p ON p.idproduit = cp.idproduit
                WHERE c.statut NOT IN ('annulee','brouillon','en_attente_paiement')
                GROUP BY p.id_vendeur, c.idcommande
            ) v
            INNER JOIN user u ON u.iduser = v.id_vendeur
            GROUP BY v.id_vendeur, u.prenom, u.nom, u.email
            ORDER BY ca_vendeur DESC, nb_commandes DESC
        ";

        $rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as &$r) {
            $nbEval = (int) ($r['nb_evalues'] ?? 0);
            $nbOk = (int) ($r['nb_a_temps'] ?? 0);
            $nbLate = (int) ($r['nb_en_retard'] ?? 0);
            $nbCmd = (int) ($r['nb_commandes'] ?? 0);

            $pctPonctuel = $nbEval > 0 ? round(100 * $nbOk / $nbEval, 1) : null;
            $score = $pctPonctuel !== null
                ? (int) round($pctPonctuel)
                : max(45, min(85, 55 + $nbCmd * 4));

            $badge = 'En cours';
            if ($pctPonctuel !== null) {
                if ($pctPonctuel >= 90) {
                    $badge = 'Excellent';
                } elseif ($pctPonctuel >= 70) {
                    $badge = 'Bon';
                } else {
                    $badge = 'À surveiller';
                }
            }

            $r['pct_ponctuel'] = $pctPonctuel;
            $r['score_global'] = min(100, $score);
            $r['badge'] = $badge;
        }
        unset($r);

        return $rows;
    }
}
