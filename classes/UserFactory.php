<?php
/**
 * UserFactory — Fabrique le bon type d'utilisateur selon le rôle
 * Concept POO : Factory Pattern, Polymorphisme
 */
class UserFactory
{
    public static function creer(array $data): Utilisateur
    {
        return match($data['role']) {
            'etudiant'   => new Etudiant($data),
            'enseignant' => new Enseignant($data),
            'assistant'  => new Assistant($data),
            'doyen'      => new Doyen($data),
            'vice_doyen' => new ViceDoyen($data),
            'apparitaire'=> new Apparitaire($data),
            default      => throw new InvalidArgumentException("Rôle inconnu : {$data['role']}"),
        };
    }
}
