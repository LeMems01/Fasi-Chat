<?php
/**
 * MessagePublic — Message entre étudiant et enseignant, visible par la promotion
 * Concept POO : Héritage, Polymorphisme
 */
class MessagePublic extends Message
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->typeMessage = 'public_promotion';
    }

    public function getTypeVisibilite(): string { return 'public_promotion'; }

    public function estVisiblePar(Utilisateur $u): bool
    {
        if ($u->getId() === $this->expediteurId || $u->getId() === $this->destinataireId) {
            return true;
        }
        // Visible par tous les étudiants de la même promotion
        if ($u->getRole() === 'etudiant' && method_exists($u, 'getPromotionId')) {
            return $u->getPromotionId() === $this->promotionId;
        }
        return false;
    }
}
