<?php
/**
 * MessageDoyenViceDoyen — Messages confidentiels entre Doyen et Vice-Doyen
 * Concept POO : Héritage, Polymorphisme, contrôle d'accès
 */
class MessageDoyenViceDoyen extends Message
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->typeMessage = 'doyen_vice_doyen';
    }

    public function getTypeVisibilite(): string { return 'doyen_vice_doyen'; }

    public function estVisiblePar(Utilisateur $u): bool
    {
        $roleAutorise = in_array($u->getRole(), ['doyen', 'vice_doyen']);
        $implique = $u->getId() === $this->expediteurId
                 || $u->getId() === $this->destinataireId;
        return $roleAutorise && $implique;
    }
}
