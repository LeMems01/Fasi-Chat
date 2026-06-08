<?php
/**
 * MurPedagogique — Espace de publications pour un cours donné
 * Concept POO : Encapsulation, contrôle d'accès
 */
class MurPedagogique
{
    private int $coursId;

    public function __construct(int $coursId)
    {
        $this->coursId = $coursId;
    }

    /**
     * Publie un message sur le mur — réservé aux enseignants et assistants
     */
    public function publier(Utilisateur $auteur, string $contenu): MessageMur
    {
        if (!in_array($auteur->getRole(), ['enseignant', 'assistant'])) {
            throw new RuntimeException('Seuls les enseignants et assistants peuvent publier sur le mur.');
        }
        $msg = new MessageMur([
            'expediteur_id' => $auteur->getId(),
            'cours_id'      => $this->coursId,
            'contenu'       => $contenu,
        ]);
        $msg->sauvegarder();
        return $msg;
    }

    /**
     * Récupère les publications du mur avec les infos de l'expéditeur
     */
    public function getPublications(): array
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->prepare(
            "SELECT m.*, u.prenom AS exp_prenom, u.nom AS exp_nom, u.role AS exp_role
             FROM messages m
             JOIN users u ON m.expediteur_id = u.id
             WHERE m.cours_id = :cid AND m.type_message = 'mur'
             ORDER BY m.created_at DESC"
        );
        $stmt->execute([':cid' => $this->coursId]);
        return $stmt->fetchAll();
    }
}
