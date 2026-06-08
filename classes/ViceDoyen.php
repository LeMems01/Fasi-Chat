<?php
/**
 * ViceDoyen — Mêmes droits de convocation que Doyen, peut affecter enseignants
 * Concept POO : Héritage + Interface Convocable (via AdminConvocable)
 */
class ViceDoyen extends AdminConvocable
{
    public function getRoleLabel(): string   { return 'Vice-Doyen'; }
    public function getRoleCouleur(): string { return '#ea580c'; }
    public function peutAffecter(): bool    { return true; }

    /** Affecte un enseignant à un cours */
    public function affecterEnseignant(int $enseignantId, int $coursId): bool
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO cours_enseignants (cours_id, user_id) VALUES (:cid, :uid)'
        );
        return $stmt->execute([':cid' => $coursId, ':uid' => $enseignantId]);
    }

    /** Retire un enseignant d'un cours */
    public function retirerEnseignant(int $enseignantId, int $coursId): bool
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare(
            'DELETE FROM cours_enseignants WHERE cours_id = :cid AND user_id = :uid'
        );
        return $stmt->execute([':cid' => $coursId, ':uid' => $enseignantId]);
    }
}
