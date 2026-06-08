<?php
/**
 * Video — Fichier vidéo (validation et stockage)
 * Concept POO : Héritage, Polymorphisme
 */
class Video extends Fichier
{
    public function getTypeLabel(): string { return 'Vidéo'; }
    public function getIcone(): string     { return 'fa-video'; }

    public function traiterFichier(string $tmpPath, string $destPath): bool
    {
        if (!in_array($this->typeMime, ALLOWED_VIDEO_TYPES)) return false;
        return copy($tmpPath, $destPath);
    }
}
