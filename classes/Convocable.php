<?php
/**
 * Convocable — Interface pour les acteurs pouvant envoyer des convocations
 * Concept POO : Interface (contrat)
 * Implémenté par : Doyen, ViceDoyen (via AdminConvocable)
 */
interface Convocable
{
    public function convoquer(array $details): int;
    public function getConvocationsEnvoyees(): array;
}
