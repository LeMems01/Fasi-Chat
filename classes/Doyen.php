<?php
/**
 * Doyen — Droits les plus élevés, peut convoquer
 * Concept POO : Héritage + Interface Convocable (via AdminConvocable)
 */
class Doyen extends AdminConvocable
{
    public function getRoleLabel(): string   { return 'Doyen'; }
    public function getRoleCouleur(): string { return '#dc2626'; }
}
