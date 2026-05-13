<?php
/**
 * Entité domaine — table `categorie` (catalogue achats).
 */
class Categorie {
    private int $idcategorie;
    private string $code;
    private string $libelle;
    private int $ordre;

    public function __construct(int $idcategorie, string $code, string $libelle, int $ordre = 0) {
        $this->idcategorie = $idcategorie;
        $this->code = $code;
        $this->libelle = $libelle;
        $this->ordre = $ordre;
    }

    public static function fromRow(array $r): self {
        return new self(
            (int) ($r['idcategorie'] ?? 0),
            (string) ($r['code'] ?? ''),
            (string) ($r['libelle'] ?? ''),
            (int) ($r['ordre'] ?? 0)
        );
    }

    public function getIdcategorie(): int {
        return $this->idcategorie;
    }

    public function getCode(): string {
        return $this->code;
    }

    public function getLibelle(): string {
        return $this->libelle;
    }

    public function getOrdre(): int {
        return $this->ordre;
    }
}
