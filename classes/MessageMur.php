<?php
/**
 * MessageMur — Publication sur le mur pédagogique d'un cours
 * Concept POO : Héritage, Polymorphisme
 */
class MessageMur extends Message
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->typeMessage = 'mur';
    }

    public function getTypeVisibilite(): string { return 'mur'; }

    public function estVisiblePar(Utilisateur $u): bool
    {
        if (!$this->coursId) return false;
        $pdo = BaseDeDonnees::getInstance();

        if ($u->getRole() === 'etudiant') {
            // Visible si le cours appartient à la promotion de l'étudiant
            $stmt = $pdo->prepare(
                'SELECT COUNT(*) FROM cours c WHERE c.id = :cid AND c.promotion_id = :pid'
            );
            $stmt->execute([':cid' => $this->coursId, ':pid' => $u->getPromotionId()]);
            return (int)$stmt->fetchColumn() > 0;
        }

        if (in_array($u->getRole(), ['enseignant', 'assistant'])) {
            $stmt = $pdo->prepare(
                'SELECT COUNT(*) FROM cours_enseignants WHERE cours_id = :cid AND user_id = :uid'
            );
            $stmt->execute([':cid' => $this->coursId, ':uid' => $u->getId()]);
            return (int)$stmt->fetchColumn() > 0;
        }

        return false;
    }
}
