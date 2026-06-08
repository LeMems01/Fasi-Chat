<?php
/**
 * FichierFactory — Fabrique le bon type de fichier
 * Concept POO : Factory Pattern
 */
class FichierFactory
{
    public static function creer(array $data): Fichier
    {
        return match($data['type_fichier']) {
            'image'    => new Image($data),
            'video'    => new Video($data),
            'document' => new Document($data),
            'audio'    => new Audio($data),
            default    => throw new InvalidArgumentException("Type fichier inconnu : {$data['type_fichier']}"),
        };
    }
}
