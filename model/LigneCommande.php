<?php
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
