<?php
/**
 * Entité domaine — table `commande` (acheteur, statut, livraison, montant).
 * La persistance est dans {@see CommandeController}.
 */
class Commande {
    private ?int $idcommande;
    private int $id_acheteur;
    private string $statut;
    private float $montant_total;
    private string $notes;
    private string $adresse_livraison;
    private string $code_postal;
    private string $ville;
    private string $pays;
    private ?string $date_commande;
    private ?string $numero_suivi;
    private ?string $date_livraison_prevue;
    private ?string $date_livraison_effective;

    public function __construct(
        int $id_acheteur,
        string $statut,
        float $montant_total,
        string $notes,
        string $adresse_livraison,
        string $code_postal,
        string $ville,
        string $pays = 'Tunisie',
        ?int $idcommande = null,
        ?string $date_commande = null,
        ?string $numero_suivi = null,
        ?string $date_livraison_prevue = null,
        ?string $date_livraison_effective = null
    ) {
        $this->id_acheteur = $id_acheteur;
        $this->statut = $statut;
        $this->montant_total = $montant_total;
        $this->notes = $notes;
        $this->adresse_livraison = $adresse_livraison;
        $this->code_postal = $code_postal;
        $this->ville = $ville;
        $this->pays = $pays;
        $this->idcommande = $idcommande;
        $this->date_commande = $date_commande;
        $this->numero_suivi = $numero_suivi;
        $this->date_livraison_prevue = $date_livraison_prevue;
        $this->date_livraison_effective = $date_livraison_effective;
    }

    public static function fromRow(array $r): self {
        return new self(
            (int) ($r['id_acheteur'] ?? 0),
            (string) ($r['statut'] ?? 'brouillon'),
            (float) ($r['montant_total'] ?? 0),
            (string) ($r['notes'] ?? ''),
            (string) ($r['adresse_livraison'] ?? ''),
            (string) ($r['code_postal'] ?? ''),
            (string) ($r['ville'] ?? ''),
            (string) ($r['pays'] ?? 'Tunisie'),
            isset($r['idcommande']) ? (int) $r['idcommande'] : null,
            isset($r['date_commande']) ? (string) $r['date_commande'] : null,
            isset($r['numero_suivi']) ? (string) $r['numero_suivi'] : null,
            isset($r['date_livraison_prevue']) ? (string) $r['date_livraison_prevue'] : null,
            isset($r['date_livraison_effective']) ? (string) $r['date_livraison_effective'] : null
        );
    }

    public function getIdcommande(): ?int {
        return $this->idcommande;
    }

    public function getIdAcheteur(): int {
        return $this->id_acheteur;
    }

    public function getStatut(): string {
        return $this->statut;
    }

    public function getMontantTotal(): float {
        return $this->montant_total;
    }

    public function getNotes(): string {
        return $this->notes;
    }

    public function getAdresseLivraison(): string {
        return $this->adresse_livraison;
    }

    public function getCodePostal(): string {
        return $this->code_postal;
    }

    public function getVille(): string {
        return $this->ville;
    }

    public function getPays(): string {
        return $this->pays;
    }

    public function getDateCommande(): ?string {
        return $this->date_commande;
    }

    public function getNumeroSuivi(): ?string {
        return $this->numero_suivi;
    }

    public function getDateLivraisonPrevue(): ?string {
        return $this->date_livraison_prevue;
    }

    public function getDateLivraisonEffective(): ?string {
        return $this->date_livraison_effective;
    }
}
