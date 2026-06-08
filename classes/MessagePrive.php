<?php
/**
 * MessagePrive — Message entre deux utilisateurs (non visible par les autres)
 * Concept POO : Héritage, Polymorphisme
 */
class MessagePrive extends Message
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->typeMessage = 'prive';
    }

    public function getTypeVisibilite(): string { return 'prive'; }

    public function estVisiblePar(Utilisateur $u): bool
    {
        return $u->getId() === $this->expediteurId
            || $u->getId() === $this->destinataireId;
    }
}
