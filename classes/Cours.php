<?php
/**
 * Cours — Représente un cours associé à une promotion
 * Concept POO : Encapsulation, interaction BDD via PDO
 */
class Cours
{
    private ?int    $id;
    private string  $titre;
    private ?string $description;
    private int     $promotionId;
    private string  $createdAt;

    public function __construct(array $data = [])
    {
        $this->id          = isset($data['id']) ? (int)$data['id'] : null;
        $this->titre       = $data['titre']        ?? '';
        $this->description = $data['description']  ?? null;
        $this->promotionId = (int)($data['promotion_id'] ?? 0);
        $this->createdAt   = $data['created_at']   ?? date('Y-m-d H:i:s');
    }

    public function getId(): ?int         { return $this->id; }
    public function getTitre(): string    { return $this->titre; }
    public function getDescription(): ?string { return $this->description; }
    public function getPromotionId(): int { return $this->promotionId; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public static function trouverParId(int $id): ?self
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM cours WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch();
        return $row ? new self($row) : null;
    }

    public static function tous(): array
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->query(
            'SELECT c.*, p.nom AS promo_nom
             FROM cours c JOIN promotions p ON c.promotion_id = p.id
             ORDER BY p.nom, c.titre'
        );
        return array_map(fn($r) => new self($r), $stmt->fetchAll());
    }

    public function getEnseignants(): array
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare(
            'SELECT u.* FROM users u
             JOIN cours_enseignants ce ON u.id = ce.user_id
             WHERE ce.cours_id = :cid ORDER BY u.nom'
        );
        $stmt->execute([':cid' => $this->id]);
        return array_map(fn($r) => UserFactory::creer($r), $stmt->fetchAll());
    }
}
