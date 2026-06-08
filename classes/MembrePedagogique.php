<?php
/**
 * MembrePedagogique — Acteurs pédagogiques (Etudiant, Enseignant, Assistant)
 * Concept POO : Classe abstraite, Héritage
 */
abstract class MembrePedagogique extends Utilisateur
{
    protected ?int $promotionId;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->promotionId = isset($data['promotion_id']) ? (int)$data['promotion_id'] : null;
    }

    public function getPromotionId(): ?int { return $this->promotionId; }

    public function getPromotion(): ?array
    {
        if (!$this->promotionId) return null;
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM promotions WHERE id = :id');
        $stmt->execute([':id' => $this->promotionId]);
        return $stmt->fetch() ?: null;
    }

    public function getCours(): array
    {
        $pdo = BaseDeDonnees::getInstance();
        if ($this->role === 'etudiant') {
            $stmt = $pdo->prepare(
                'SELECT c.* FROM cours c WHERE c.promotion_id = :pid ORDER BY c.titre'
            );
            $stmt->execute([':pid' => $this->promotionId]);
        } else {
            $stmt = $pdo->prepare(
                'SELECT c.* FROM cours c
                 JOIN cours_enseignants ce ON c.id = ce.cours_id
                 WHERE ce.user_id = :uid ORDER BY c.titre'
            );
            $stmt->execute([':uid' => $this->id]);
        }
        return $stmt->fetchAll();
    }
}
