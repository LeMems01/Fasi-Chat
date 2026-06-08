<?php
/**
 * GestionFichiers — Orchestrateur de l'upload et du traitement des fichiers
 * Concept POO : Encapsulation, délégation aux sous-classes de Fichier
 */
class GestionFichiers
{
    /**
     * Traite un fichier uploadé et retourne l'objet Fichier correspondant
     * @throws RuntimeException
     */
    public static function traiterUpload(array $fileData, int $messageId): ?Fichier
    {
        if ($fileData['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException(self::erreurUpload($fileData['error']));
        }
        if ($fileData['size'] > UPLOAD_MAX_SIZE) {
            throw new RuntimeException('Fichier trop volumineux. Maximum autorisé : 20 Mo.');
        }
        if (!is_uploaded_file($fileData['tmp_name'])) {
            throw new RuntimeException('Fichier invalide.');
        }

        // Détection MIME sécurisée (pas via le navigateur)
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($fileData['tmp_name']);

        $type = self::detecterType($mimeType);
        if ($type === null) {
            throw new RuntimeException('Type de fichier non autorisé : ' . $mimeType);
        }

        // Nom de stockage sécurisé (UUID + extension)
        $ext      = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
        $ext      = preg_replace('/[^a-z0-9]/', '', $ext);
        $nomStocke = $type . 's/' . bin2hex(random_bytes(16)) . '.' . $ext;
        $destPath  = UPLOAD_DIR . $nomStocke;

        $dir = dirname($destPath);
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $data = [
            'message_id'   => $messageId,
            'nom_original' => basename($fileData['name']),
            'nom_stocke'   => $nomStocke,
            'type_mime'    => $mimeType,
            'taille'       => $fileData['size'],
            'type_fichier' => $type,
        ];

        $fichier = FichierFactory::creer($data);

        if (!$fichier->traiterFichier($fileData['tmp_name'], $destPath)) {
            throw new RuntimeException('Erreur lors du traitement du fichier.');
        }

        return $fichier;
    }

    private static function detecterType(string $mime): ?string
    {
        if (in_array($mime, ALLOWED_IMAGE_TYPES))    return 'image';
        if (in_array($mime, ALLOWED_VIDEO_TYPES))    return 'video';
        if (in_array($mime, ALLOWED_AUDIO_TYPES))    return 'audio';
        if (in_array($mime, ALLOWED_DOCUMENT_TYPES)) return 'document';
        return null;
    }

    private static function erreurUpload(int $code): string
    {
        return match($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux.',
            UPLOAD_ERR_PARTIAL   => 'Upload incomplet.',
            UPLOAD_ERR_NO_FILE   => 'Aucun fichier envoyé.',
            UPLOAD_ERR_NO_TMP_DIR=> 'Dossier temporaire manquant.',
            UPLOAD_ERR_CANT_WRITE=> 'Impossible d\'écrire le fichier.',
            default              => 'Erreur d\'upload inconnue.',
        };
    }

    public static function supprimer(string $nomStocke): bool
    {
        $path = UPLOAD_DIR . $nomStocke;
        return file_exists($path) ? unlink($path) : true;
    }
}
