<?php
/**
 * AnnonceValve — Annonce publiée sur la Valve par l'Apparitaire
 * Concept POO : Encapsulation, CRUD via PDO
 */
class AnnonceValve
{
    private ?int    $id;
    private string  $titre;
    private string  $contenu;
    private int     $auteurId;
    private ?string $dateExpiration;
    private string  $createdAt;

    public function __construct(array $data = [])
    {
        $this->id             = isset($data['id'])     ? (int)$data['id'] : null;
        $this->titre          = $data['titre']          ?? '';
        $this->contenu        = $data['contenu']        ?? '';
        $this->auteurId       = (int)($data['auteur_id'] ?? 0);
        $this->dateExpiration = $data['date_expiration'] ?? null;
        $this->createdAt      = $data['created_at']     ?? date('Y-m-d H:i:s');
    }

    public function getId(): ?int              { return $this->id; }
    public function getTitre(): string         { return $this->titre; }
    public function getContenu(): string       { return $this->contenu; }
    public function getAuteurId(): int         { return $this->auteurId; }
    public function getDateExpiration(): ?string { return $this->dateExpiration; }
    public function getCreatedAt(): string     { return $this->createdAt; }

    public function setTitre(string $v): void          { $this->titre          = $v; }
    public function setContenu(string $v): void        { $this->contenu        = $v; }
    public function setDateExpiration(?string $v): void { $this->dateExpiration = $v; }

    public function estActive(): bool
    {
        if (!$this->dateExpiration) return true;
        return strtotime($this->dateExpiration) >= strtotime('today');
    }

    public function sauvegarder(): bool
    {
        $pdo = BaseDeDonnees::getInstance();
        if ($this->id === null) {
            $stmt = $pdo->prepare(
                'INSERT INTO valve_annonces (titre, contenu, auteur_id, date_expiration)
                 VALUES (:titre, :contenu, :auteur, :exp)'
            );
            $ok = $stmt->execute([
                ':titre'   => $this->titre,
                ':contenu' => $this->contenu,
                ':auteur'  => $this->auteurId,
                ':exp'     => $this->dateExpiration,
            ]);
            if ($ok) $this->id = (int)$pdo->lastInsertId();
            return $ok;
        }
        $stmt = $pdo->prepare(
            'UPDATE valve_annonces SET titre = :titre, contenu = :contenu, date_expiration = :exp
             WHERE id = :id AND auteur_id = :auteur'
        );
        return $stmt->execute([
            ':titre'   => $this->titre,
            ':contenu' => $this->contenu,
            ':exp'     => $this->dateExpiration,
            ':id'      => $this->id,
            ':auteur'  => $this->auteurId,
        ]);
    }

    public static function supprimer(int $id, int $auteurId): bool
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare('DELETE FROM valve_annonces WHERE id = :id AND auteur_id = :auteur');
        return $stmt->execute([':id' => $id, ':auteur' => $auteurId]);
    }

    public static function toutes(): array
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->query(
            'SELECT va.*, u.prenom, u.nom
             FROM valve_annonces va JOIN users u ON va.auteur_id = u.id
             ORDER BY va.created_at DESC'
        );
        return array_map(fn($r) => new self($r), $stmt->fetchAll());
    }

    public static function trouverParId(int $id): ?self
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM valve_annonces WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch();
        return $row ? new self($row) : null;
    }
}
