<?php
/**
 * Modèles du domaine commerce / commandes (fichier unique).
 * — {@see Commande} : table `commande`
 * — {@see LigneCommande} : table `commande_produit`
 * — {@see Produit} : table `produit` (persistance {@see ProduitController})
 */

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

/**
 * Entité ligne de commande — table `commande_produit`.
 */
class LigneCommande {
    private int $idcommande;
    private int $idproduit;
    private int $quantite;
    private float $prix_unitaire;

    public function __construct(int $idcommande, int $idproduit, int $quantite, float $prix_unitaire) {
        $this->idcommande = $idcommande;
        $this->idproduit = $idproduit;
        $this->quantite = $quantite;
        $this->prix_unitaire = $prix_unitaire;
    }

    public static function fromRow(array $r): self {
        return new self(
            (int) ($r['idcommande'] ?? 0),
            (int) ($r['idproduit'] ?? 0),
            (int) ($r['quantite'] ?? 0),
            (float) ($r['prix_unitaire'] ?? 0)
        );
    }

    public function getIdcommande(): int {
        return $this->idcommande;
    }

    public function getIdproduit(): int {
        return $this->idproduit;
    }

    public function getQuantite(): int {
        return $this->quantite;
    }

    public function getPrixUnitaire(): float {
        return $this->prix_unitaire;
    }
}

/**
 * Entité domaine — table `produit` (référence, prix, stock, vendeur).
 * La persistance et les requêtes sont dans {@see ProduitController}.
 */
class Produit {
    private ?int $idproduit;
    private string $reference;
    private string $designation;
    private ?string $description;
    private float $prix_unitaire;
    private int $stock;
    private int $id_vendeur;
    private int $actif;
    private ?string $created_at;

    public function __construct(
        string $reference,
        string $designation,
        ?string $description,
        float $prix_unitaire,
        int $stock,
        int $id_vendeur,
        int $actif = 1,
        ?int $idproduit = null,
        ?string $created_at = null
    ) {
        $this->reference = $reference;
        $this->designation = $designation;
        $this->description = $description;
        $this->prix_unitaire = $prix_unitaire;
        $this->stock = $stock;
        $this->id_vendeur = $id_vendeur;
        $this->actif = $actif;
        $this->idproduit = $idproduit;
        $this->created_at = $created_at;
    }

    public static function fromRow(array $r): self {
        return new self(
            (string) ($r['reference'] ?? ''),
            (string) ($r['designation'] ?? ''),
            isset($r['description']) ? (string) $r['description'] : null,
            (float) ($r['prix_unitaire'] ?? 0),
            (int) ($r['stock'] ?? 0),
            (int) ($r['id_vendeur'] ?? 0),
            (int) ($r['actif'] ?? 1),
            isset($r['idproduit']) ? (int) $r['idproduit'] : null,
            isset($r['created_at']) ? (string) $r['created_at'] : null
        );
    }

    public function getIdproduit(): ?int {
        return $this->idproduit;
    }

    public function getReference(): string {
        return $this->reference;
    }

    public function getDesignation(): string {
        return $this->designation;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function getPrixUnitaire(): float {
        return $this->prix_unitaire;
    }

    public function getStock(): int {
        return $this->stock;
    }

    public function getIdVendeur(): int {
        return $this->id_vendeur;
    }

    public function getActif(): int {
        return $this->actif;
    }

    public function getCreatedAt(): ?string {
        return $this->created_at;
    }
}
