<?php
/**
 * MembreAdministratif — Acteurs administratifs (Doyen, ViceDoyen, Apparitaire)
 * Concept POO : Classe abstraite, Héritage
 */
abstract class MembreAdministratif extends Utilisateur
{
    public function getRoleCouleur(): string { return '#7c3aed'; }

    public function getDashboardData(): array
    {
        $pdo = BaseDeDonnees::getInstance();
        $data = [];
        $stmt = $pdo->query('SELECT COUNT(*) AS total FROM users WHERE role IN ("enseignant","assistant")');
        $data['nb_enseignants'] = (int)$stmt->fetchColumn();
        $stmt = $pdo->query('SELECT COUNT(*) AS total FROM users WHERE role = "etudiant"');
        $data['nb_etudiants'] = (int)$stmt->fetchColumn();
        $stmt = $pdo->query('SELECT COUNT(*) AS total FROM cours');
        $data['nb_cours'] = (int)$stmt->fetchColumn();
        return $data;
    }
}
