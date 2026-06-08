<?php
/**
 * Fichier — Classe abstraite pour la gestion des fichiers multimédias
 * Concept POO : Classe abstraite, Encapsulation
 */
abstract class Fichier
{
    protected ?int   $id;
    protected int    $messageId;
    protected string $nomOriginal;
    protected string $nomStocke;
    protected string $typeMime;
    protected int    $taille;
    protected string $typeFichier;

    public function __construct(array $data = [])
    {
        $this->id          = isset($data['id'])         ? (int)$data['id']         : null;
        $this->messageId   = (int)($data['message_id'] ?? 0);
        $this->nomOriginal = $data['nom_original']       ?? '';
        $this->nomStocke   = $data['nom_stocke']         ?? '';
        $this->typeMime    = $data['type_mime']          ?? '';
        $this->taille      = (int)($data['taille']       ?? 0);
        $this->typeFichier = $data['type_fichier']       ?? '';
    }

    /* ---- Méthodes abstraites ---- */
    abstract public function getTypeLabel(): string;
    abstract public function traiterFichier(string $tmpPath, string $destPath): bool;
    abstract public function getIcone(): string;

    /* ---- Getters ---- */
    public function getId(): ?int           { return $this->id; }
    public function getMessageId(): int     { return $this->messageId; }
    public function getNomOriginal(): string { return $this->nomOriginal; }
    public function getNomStocke(): string  { return $this->nomStocke; }
    public function getTypeMime(): string   { return $this->typeMime; }
    public function getTaille(): int        { return $this->taille; }
    public function getTypeFichier(): string { return $this->typeFichier; }

    public function setMessageId(int $id): void { $this->messageId = $id; }

    public function getTailleFormatee(): string
    {
        if ($this->taille >= 1048576) return round($this->taille / 1048576, 2) . ' Mo';
        if ($this->taille >= 1024)    return round($this->taille / 1024, 2)    . ' Ko';
        return $this->taille . ' o';
    }

    public function getUrl(): string
    {
        return 'uploads/' . $this->nomStocke;
    }

    /* ---- Persistance ---- */
    public function sauvegarder(): bool
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO fichiers (message_id, nom_original, nom_stocke, type_mime, taille, type_fichier)
             VALUES (:mid, :norig, :nstocke, :mime, :taille, :type)'
        );
        $ok = $stmt->execute([
            ':mid'     => $this->messageId,
            ':norig'   => $this->nomOriginal,
            ':nstocke' => $this->nomStocke,
            ':mime'    => $this->typeMime,
            ':taille'  => $this->taille,
            ':type'    => $this->typeFichier,
        ]);
        if ($ok) $this->id = (int)BaseDeDonnees::getInstance()->lastInsertId();
        return $ok;
    }

    public static function getParMessage(int $messageId): array
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM fichiers WHERE message_id = :mid');
        $stmt->execute([':mid' => $messageId]);
        return array_map(fn($r) => FichierFactory::creer($r), $stmt->fetchAll());
    }
}
