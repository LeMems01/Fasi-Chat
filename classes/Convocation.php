<?php
/**
 * Convocation — Message administratif spécial (réunion)
 * Concept POO : Classe distincte, encapsulation
 */
class Convocation
{
    private ?int   $id;
    private int    $expediteurId;
    private string $objet;
    private string $dateReunion;
    private string $lieu;
    private ?string $messageExplicatif;
    private string $createdAt;
    private array  $destinataires = [];

    public function __construct(array $data = [])
    {
        $this->id                = isset($data['id']) ? (int)$data['id'] : null;
        $this->expediteurId      = (int)($data['expediteur_id'] ?? 0);
        $this->objet             = $data['objet']              ?? '';
        $this->dateReunion       = $data['date_reunion']       ?? '';
        $this->lieu              = $data['lieu']               ?? '';
        $this->messageExplicatif = $data['message_explicatif'] ?? null;
        $this->createdAt         = $data['created_at']         ?? date('Y-m-d H:i:s');
    }

    /* ---- Getters ---- */
    public function getId(): ?int              { return $this->id; }
    public function getExpediteurId(): int     { return $this->expediteurId; }
    public function getObjet(): string         { return $this->objet; }
    public function getDateReunion(): string   { return $this->dateReunion; }
    public function getLieu(): string          { return $this->lieu; }
    public function getMessageExplicatif(): ?string { return $this->messageExplicatif; }
    public function getCreatedAt(): string     { return $this->createdAt; }
    public function getDestinataires(): array  { return $this->destinataires; }

    /* ---- Setters ---- */
    public function setObjet(string $v): void            { $this->objet             = $v; }
    public function setDateReunion(string $v): void      { $this->dateReunion       = $v; }
    public function setLieu(string $v): void             { $this->lieu              = $v; }
    public function setMessageExplicatif(?string $v): void { $this->messageExplicatif = $v; }
    public function setExpediteurId(int $v): void        { $this->expediteurId      = $v; }

    /* ---- Récupération par ID ---- */
    public static function trouverParId(int $id): ?self
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare(
            'SELECT c.*, u.prenom AS exp_prenom, u.nom AS exp_nom, u.role AS exp_role
             FROM convocations c JOIN users u ON c.expediteur_id = u.id
             WHERE c.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? new self($row) : null;
    }

    /* ---- Marquer comme lue ---- */
    public static function marquerLue(int $convocId, int $userId): void
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare(
            'UPDATE convocation_destinataires SET lu = 1
             WHERE convocation_id = :cid AND user_id = :uid'
        );
        $stmt->execute([':cid' => $convocId, ':uid' => $userId]);
    }

    public function getDateReunionFormatee(): string
    {
        return date('d/m/Y à H\hi', strtotime($this->dateReunion));
    }
}
