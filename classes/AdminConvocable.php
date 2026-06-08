<?php
/**
 * AdminConvocable — Classe abstraite pour Doyen et ViceDoyen
 * Partage les droits de convocation SANS duplication de code
 * Concept POO : Héritage + Interface (Convocable)
 */
abstract class AdminConvocable extends MembreAdministratif implements Convocable
{
    public function peutEnvoyerConvocation(): bool { return true; }

    /**
     * Envoie une convocation collective à tous les enseignants et assistants
     * @return int ID de la convocation créée
     */
    public function convoquer(array $details): int
    {
        $pdo = BaseDeDonnees::getInstance();

        // Insérer la convocation
        $stmt = $pdo->prepare(
            'INSERT INTO convocations (expediteur_id, objet, date_reunion, lieu, message_explicatif)
             VALUES (:exp_id, :objet, :date_reunion, :lieu, :msg)'
        );
        $stmt->execute([
            ':exp_id'       => $this->id,
            ':objet'        => $details['objet'],
            ':date_reunion' => $details['date_reunion'],
            ':lieu'         => $details['lieu'],
            ':msg'          => $details['message_explicatif'] ?? null,
        ]);
        $convocId = (int)$pdo->lastInsertId();

        // Récupérer tous les enseignants et assistants
        $stmt = $pdo->query(
            "SELECT id FROM users WHERE role IN ('enseignant','assistant')"
        );
        $destinataires = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Insérer les destinataires
        $stmtDest = $pdo->prepare(
            'INSERT INTO convocation_destinataires (convocation_id, user_id) VALUES (:cid, :uid)'
        );
        foreach ($destinataires as $uid) {
            $stmtDest->execute([':cid' => $convocId, ':uid' => (int)$uid]);
        }

        return $convocId;
    }

    public function getConvocationsEnvoyees(): array
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare(
            'SELECT c.*, u.prenom, u.nom
             FROM convocations c
             JOIN users u ON c.expediteur_id = u.id
             WHERE c.expediteur_id = :uid
             ORDER BY c.created_at DESC'
        );
        $stmt->execute([':uid' => $this->id]);
        return $stmt->fetchAll();
    }
}
