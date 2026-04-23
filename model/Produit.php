<?php
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
