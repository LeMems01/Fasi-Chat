<?php
/**
 * Apparitaire — Gestion exclusive de la Valve
 * Concept POO : Héritage, contrôle d'accès explicite
 */
class Apparitaire extends MembreAdministratif
{
    public function getRoleLabel(): string   { return 'Apparitaire'; }
    public function getRoleCouleur(): string { return '#0284c7'; }
    public function peutGererValve(): bool  { return true; }

    public function getDashboardData(): array
    {
        $pdo  = BaseDeDonnees::getInstance();
        $data = parent::getDashboardData();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM valve_annonces WHERE auteur_id = :uid');
        $stmt->execute([':uid' => $this->id]);
        $data['mes_annonces'] = (int)$stmt->fetchColumn();
        $stmt = $pdo->query(
            "SELECT COUNT(*) FROM valve_annonces WHERE date_expiration IS NULL OR date_expiration >= CURDATE()"
        );
        $data['annonces_actives'] = (int)$stmt->fetchColumn();
        return $data;
    }
}
