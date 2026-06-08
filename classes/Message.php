<?php
/**
 * Message — Classe abstraite mère de tous les messages
 * Concept POO : Classe abstraite, Polymorphisme
 */
abstract class Message
{
    protected ?int   $id;
    protected int    $expediteurId;
    protected ?int   $destinataireId;
    protected ?int   $coursId;
    protected ?int   $promotionId;
    protected string $contenu;
    protected string $typeMessage;
    protected int    $lu;
    protected string $createdAt;

    public function __construct(array $data = [])
    {
        $this->id             = isset($data['id'])             ? (int)$data['id']             : null;
        $this->expediteurId   = (int)($data['expediteur_id']   ?? 0);
        $this->destinataireId = isset($data['destinataire_id']) ? (int)$data['destinataire_id'] : null;
        $this->coursId        = isset($data['cours_id'])        ? (int)$data['cours_id']        : null;
        $this->promotionId    = isset($data['promotion_id'])    ? (int)$data['promotion_id']    : null;
        $this->contenu        = $data['contenu']     ?? '';
        $this->typeMessage    = $data['type_message'] ?? '';
        $this->lu             = (int)($data['lu']    ?? 0);
        $this->createdAt      = $data['created_at']  ?? date('Y-m-d H:i:s');
    }

    /* ---- Méthodes abstraites ---- */
    abstract public function getTypeVisibilite(): string;
    abstract public function estVisiblePar(Utilisateur $u): bool;

    /* ---- Persistance commune ---- */
    public function sauvegarder(): int
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO messages
             (expediteur_id, destinataire_id, cours_id, promotion_id, contenu, type_message)
             VALUES (:exp, :dest, :cours, :promo, :contenu, :type)'
        );
        $stmt->execute([
            ':exp'    => $this->expediteurId,
            ':dest'   => $this->destinataireId,
            ':cours'  => $this->coursId,
            ':promo'  => $this->promotionId,
            ':contenu'=> $this->contenu,
            ':type'   => $this->typeMessage,
        ]);
        $this->id = (int)$pdo->lastInsertId();
        return $this->id;
    }

    /* ---- Getters / Setters ---- */
    public function getId(): ?int            { return $this->id; }
    public function getExpediteurId(): int   { return $this->expediteurId; }
    public function getDestinatireId(): ?int { return $this->destinataireId; }
    public function getCoursId(): ?int       { return $this->coursId; }
    public function getPromotionId(): ?int   { return $this->promotionId; }
    public function getContenu(): string     { return $this->contenu; }
    public function getTypeMessage(): string { return $this->typeMessage; }
    public function isLu(): bool             { return $this->lu === 1; }
    public function getCreatedAt(): string   { return $this->createdAt; }

    public function setExpediteurId(int $id): void    { $this->expediteurId   = $id; }
    public function setDestinatireId(?int $id): void  { $this->destinataireId = $id; }
    public function setCoursId(?int $id): void        { $this->coursId        = $id; }
    public function setPromotionId(?int $id): void    { $this->promotionId    = $id; }
    public function setContenu(string $c): void       { $this->contenu        = $c; }

    /* ---- Marquer comme lu ---- */
    public static function marquerLu(int $messageId, int $userId): void
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare(
            'UPDATE messages SET lu = 1 WHERE id = :id AND destinataire_id = :uid'
        );
        $stmt->execute([':id' => $messageId, ':uid' => $userId]);
    }
}
