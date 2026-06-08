<?php
/**
 * Promotion — Groupe d'étudiants (ex: L2 Info 2025-2026)
 */
class Promotion
{
    private ?int   $id;
    private string $nom;
    private string $annee;

    public function __construct(array $data = [])
    {
        $this->id    = isset($data['id']) ? (int)$data['id'] : null;
        $this->nom   = $data['nom']   ?? '';
        $this->annee = $data['annee'] ?? '';
    }

    public function getId(): ?int    { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getAnnee(): string { return $this->annee; }
    public function getNomComplet(): string { return $this->nom . ' — ' . $this->annee; }

    public static function toutes(): array
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->query('SELECT * FROM promotions ORDER BY annee DESC, nom');
        return array_map(fn($r) => new self($r), $stmt->fetchAll());
    }

    public static function trouverParId(int $id): ?self
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM promotions WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch();
        return $row ? new self($row) : null;
    }
}
