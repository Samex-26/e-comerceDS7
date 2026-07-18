<?php
// Clase para sanitización de datos de entrada.
// Solo limpia/normaliza; no valida (esa responsabilidad es de Validator).

class Sanitizer
{
    public static function texto(string $valor): string
    {
        return trim(strip_tags($valor));
    }

    public static function email(string $valor): string
    {
        return trim(strtolower($valor));
    }

    public static function entero(mixed $valor): int
    {
        return (int) $valor;
    }

    public static function decimal(mixed $valor): float
    {
        return (float) str_replace(',', '.', $valor);
    }

    public static function url(string $valor): string
    {
        return trim(strip_tags($valor));
    }

    public static function alias(string $valor): string
    {
        // Solo letras, números, guiones y underscores
        return preg_replace('/[^a-zA-Z0-9\-_]/', '', trim($valor));
    }

    public static function telefono(string $valor): string
    {
        // Solo dígitos, signo +, guiones, espacios y paréntesis
        return preg_replace('/[^0-9\+\-\(\) ]/', '', trim($valor));
    }

    public static function html(string $valor): string
    {
        // Para descripciones que pueden llevar HTML básico
        $allowed = '<p><br><strong><em><ul><ol><li><table><tr><td><th><h1><h2><h3><h4><h5><h6>';
        return trim(strip_tags($valor, $allowed));
    }
}
