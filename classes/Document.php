<?php
/**
 * Document — Fichier document (PDF, Word, Excel, etc.)
 * Concept POO : Héritage, Polymorphisme
 */
class Document extends Fichier
{
    public function getTypeLabel(): string { return 'Document'; }

    public function getIcone(): string
    {
        return match(true) {
            str_contains($this->typeMime, 'pdf')          => 'fa-file-pdf',
            str_contains($this->typeMime, 'word')         => 'fa-file-word',
            str_contains($this->typeMime, 'excel') || str_contains($this->typeMime, 'spreadsheet') => 'fa-file-excel',
            str_contains($this->typeMime, 'powerpoint') || str_contains($this->typeMime, 'presentation') => 'fa-file-powerpoint',
            default => 'fa-file-alt',
        };
    }

    public function traiterFichier(string $tmpPath, string $destPath): bool
    {
        if (!in_array($this->typeMime, ALLOWED_DOCUMENT_TYPES)) return false;
        return copy($tmpPath, $destPath);
    }
}
