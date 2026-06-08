<?php
/**
 * Valve — Espace d'annonces institutionnelles
 * Concept POO : Encapsulation, vérification de rôle avant écriture
 */
class Valve
{
    /**
     * Publie une annonce — vérifie que l'acteur est Apparitaire
     * @throws RuntimeException
     */
    public static function publier(Utilisateur $auteur, array $data): AnnonceValve
    {
        if (!$auteur->peutGererValve()) {
            throw new RuntimeException('Accès refusé : seul l\'Apparitaire peut publier sur la Valve.');
        }
        $annonce = new AnnonceValve([
            'auteur_id'       => $auteur->getId(),
            'titre'           => $data['titre'],
            'contenu'         => $data['contenu'],
            'date_expiration' => $data['date_expiration'] ?? null,
        ]);
        $annonce->sauvegarder();
        return $annonce;
    }

    public static function modifier(Utilisateur $auteur, int $id, array $data): bool
    {
        if (!$auteur->peutGererValve()) {
            throw new RuntimeException('Accès refusé.');
        }
        $annonce = AnnonceValve::trouverParId($id);
        if (!$annonce) throw new RuntimeException('Annonce introuvable.');
        $annonce->setTitre($data['titre']);
        $annonce->setContenu($data['contenu']);
        $annonce->setDateExpiration($data['date_expiration'] ?? null);
        return $annonce->sauvegarder();
    }

    public static function supprimer(Utilisateur $auteur, int $id): bool
    {
        if (!$auteur->peutGererValve()) {
            throw new RuntimeException('Accès refusé.');
        }
        return AnnonceValve::supprimer($id, $auteur->getId());
    }

    public static function listerActives(): array
    {
        $pdo  = BaseDeDonnees::getInstance();
        $stmt = $pdo->query(
            "SELECT va.*, u.prenom, u.nom
             FROM valve_annonces va JOIN users u ON va.auteur_id = u.id
             WHERE va.date_expiration IS NULL OR va.date_expiration >= CURDATE()
             ORDER BY va.created_at DESC"
        );
        return array_map(fn($r) => new AnnonceValve($r), $stmt->fetchAll());
    }
}
