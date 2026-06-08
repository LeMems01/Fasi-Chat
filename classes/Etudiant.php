<?php
/**
 * Etudiant — Membre pédagogique
 * Concept POO : Héritage, Polymorphisme (getRoleLabel)
 */
class Etudiant extends MembrePedagogique
{
    public function getRoleLabel(): string   { return 'Étudiant'; }
    public function getRoleCouleur(): string { return '#0891b2'; }

    public function getDashboardData(): array
    {
        $pdo = BaseDeDonnees::getInstance();
        $data = [];

        // Messages non lus
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM messages
             WHERE destinataire_id = :uid AND lu = 0 AND type_message = 'prive'"
        );
        $stmt->execute([':uid' => $this->id]);
        $data['messages_non_lus'] = (int)$stmt->fetchColumn();

        // Annonces Valve
        $stmt = $pdo->query(
            "SELECT COUNT(*) FROM valve_annonces
             WHERE (date_expiration IS NULL OR date_expiration >= CURDATE())"
        );
        $data['nb_annonces'] = (int)$stmt->fetchColumn();

        // Camarades de promotion
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM users WHERE promotion_id = :pid AND role = 'etudiant' AND id != :uid"
        );
        $stmt->execute([':pid' => $this->promotionId, ':uid' => $this->id]);
        $data['nb_camarades'] = (int)$stmt->fetchColumn();

        // Cours de la promotion
        $data['cours'] = $this->getCours();

        return $data;
    }

    /** Retourne les camarades de promotion */
    public function getCamarades(): array
    {
        if (!$this->promotionId) return [];
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare(
            "SELECT * FROM users WHERE promotion_id = :pid AND role = 'etudiant' AND id != :uid ORDER BY nom"
        );
        $stmt->execute([':pid' => $this->promotionId, ':uid' => $this->id]);
        return array_map(fn($r) => UserFactory::creer($r), $stmt->fetchAll());
    }

    /** Retourne les enseignants des cours de la promotion */
    public function getEnseignants(): array
    {
        if (!$this->promotionId) return [];
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare(
            "SELECT DISTINCT u.* FROM users u
             JOIN cours_enseignants ce ON u.id = ce.user_id
             JOIN cours c ON ce.cours_id = c.id
             WHERE c.promotion_id = :pid AND u.role IN ('enseignant','assistant')
             ORDER BY u.nom"
        );
        $stmt->execute([':pid' => $this->promotionId]);
        return array_map(fn($r) => UserFactory::creer($r), $stmt->fetchAll());
    }
}
