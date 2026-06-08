<?php
/**
 * Utilisateur — Classe abstraite mère de tous les acteurs
 * Concept POO : Classe abstraite, Encapsulation, Héritage
 */
abstract class Utilisateur
{
    protected int    $id;
    protected string $nom;
    protected string $prenom;
    protected string $email;
    protected string $password;
    protected string $role;
    protected string $createdAt;
    protected ?string $avatarUrl;

    public function __construct(array $data)
    {
        $this->id        = (int)($data['id'] ?? 0);
        $this->nom       = $data['nom']       ?? '';
        $this->prenom    = $data['prenom']    ?? '';
        $this->email     = $data['email']     ?? '';
        $this->password  = $data['password']  ?? '';
        $this->role      = $data['role']      ?? '';
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->avatarUrl = $data['avatar_url'] ?? null;
    }

    /* ---- Méthodes abstraites ---- */
    abstract public function getRoleLabel(): string;
    abstract public function getRoleCouleur(): string;
    abstract public function getDashboardData(): array;

    /* ---- Getters ---- */
    public function getId(): int       { return $this->id; }
    public function getNom(): string   { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }
    public function getEmail(): string { return $this->email; }
    public function getRole(): string  { return $this->role; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getAvatarUrl(): ?string { return $this->avatarUrl; }

    public function getNomComplet(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    public function getInitiales(): string
    {
        $p = mb_strtoupper(mb_substr($this->prenom, 0, 1));
        $n = mb_strtoupper(mb_substr($this->nom, 0, 1));
        return $p . $n;
    }

    /* ---- Sécurité ---- */
    public function verifierMotDePasse(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    /* ---- Droits (surchargés dans sous-classes) ---- */
    public function peutEnvoyerConvocation(): bool { return false; }
    public function peutGererValve(): bool         { return false; }
    public function peutAffecter(): bool           { return false; }
    public function peutVoirMur(): bool            { return true; }

    /* ---- Persistance ---- */
    public static function trouverParId(int $id): ?static
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch();
        if (!$row) return null;
        return UserFactory::creer($row);
    }

    public static function trouverParEmail(string $email): ?static
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $row  = $stmt->fetch();
        if (!$row) return null;
        return UserFactory::creer($row);
    }

    public static function tousParRole(string $role): array
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE role = :role ORDER BY nom, prenom');
        $stmt->execute([':role' => $role]);
        return array_map(fn($r) => UserFactory::creer($r), $stmt->fetchAll());
    }
}
