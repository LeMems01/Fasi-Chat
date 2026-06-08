<?php
/**
 * Assistant — Partage les privilèges Enseignant
 * Concept POO : Héritage (extends Enseignant)
 */
class Assistant extends Enseignant
{
    public function getRoleLabel(): string   { return 'Assistant'; }
    public function getRoleCouleur(): string { return '#7c3aed'; }
}
