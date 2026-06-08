<?php
/**
 * Audio — Message vocal / fichier audio
 * Concept POO : Héritage, Polymorphisme
 */
class Audio extends Fichier
{
    public function getTypeLabel(): string { return 'Audio'; }
    public function getIcone(): string     { return 'fa-microphone'; }

    public function traiterFichier(string $tmpPath, string $destPath): bool
    {
        if (!in_array($this->typeMime, ALLOWED_AUDIO_TYPES)) return false;
        return copy($tmpPath, $destPath);
    }
}
