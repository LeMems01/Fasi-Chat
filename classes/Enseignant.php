<?php
/**
 * Enseignant — Membre pédagogique
 * Concept POO : Héritage, Polymorphisme
 */
class Enseignant extends MembrePedagogique
{
    public function getRoleLabel(): string   { return 'Enseignant'; }
    public function getRoleCouleur(): string { return '#059669'; }

    public function getDashboardData(): array
    {
        $pdo  = BaseDeDonnees::getInstance();
        $data = [];

        // Convocations non lues
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM convocation_destinataires
             WHERE user_id = :uid AND lu = 0"
        );
        $stmt->execute([':uid' => $this->id]);
        $data['convocations_non_lues'] = (int)$stmt->fetchColumn();

        // Messages non lus
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM messages
             WHERE destinataire_id = :uid AND lu = 0"
        );
        $stmt->execute([':uid' => $this->id]);
        $data['messages_non_lus'] = (int)$stmt->fetchColumn();

        // Mes cours
        $data['cours'] = $this->getCours();
        $data['nb_cours'] = count($data['cours']);

        // Nb étudiants dans mes promos
        $stmt = $pdo->prepare(
            "SELECT COUNT(DISTINCT u.id) FROM users u
             JOIN cours c ON u.promotion_id = c.promotion_id
             JOIN cours_enseignants ce ON c.id = ce.cours_id
             WHERE ce.user_id = :uid AND u.role = 'etudiant'"
        );
        $stmt->execute([':uid' => $this->id]);
        $data['nb_etudiants'] = (int)$stmt->fetchColumn();

        return $data;
    }

    /** Étudiants affiliés à mes cours */
    public function getEtudiants(): array
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare(
            "SELECT DISTINCT u.* FROM users u
             JOIN cours c ON u.promotion_id = c.promotion_id
             JOIN cours_enseignants ce ON c.id = ce.cours_id
             WHERE ce.user_id = :uid AND u.role = 'etudiant'
             ORDER BY u.nom, u.prenom"
        );
        $stmt->execute([':uid' => $this->id]);
        return array_map(fn($r) => UserFactory::creer($r), $stmt->fetchAll());
    }

    /** Collègues enseignants/assistants */
    public function getCollègues(): array
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare(
            "SELECT * FROM users
             WHERE role IN ('enseignant','assistant') AND id != :uid
             ORDER BY nom, prenom"
        );
        $stmt->execute([':uid' => $this->id]);
        return array_map(fn($r) => UserFactory::creer($r), $stmt->fetchAll());
    }

    /** Convocations reçues */
    public function getConvocationsRecues(): array
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare(
            "SELECT c.*, cd.lu,
                    u.prenom AS exp_prenom, u.nom AS exp_nom, u.role AS exp_role
             FROM convocations c
             JOIN convocation_destinataires cd ON c.id = cd.convocation_id
             JOIN users u ON c.expediteur_id = u.id
             WHERE cd.user_id = :uid
             ORDER BY c.created_at DESC"
        );
        $stmt->execute([':uid' => $this->id]);
        return $stmt->fetchAll();
    }
}
