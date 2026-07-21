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
        $digits = preg_replace('/[^0-9]/', '', trim($valor));
        if (strlen($digits) === 8) {
            return substr($digits, 0, 4) . '-' . substr($digits, 4);
        }
        return $digits;
    }

    public static function html(string $valor): string
    {
        // Para descripciones que pueden llevar HTML básico
        $allowed = '<p><br><strong><em><ul><ol><li><table><tr><td><th><h1><h2><h3><h4><h5><h6>';
        return trim(strip_tags($valor, $allowed));
    }

    public static function nombrePropio(string $valor): string
    {
        $valor = trim(preg_replace('/\s+/', ' ', $valor));
        $valor = mb_convert_case($valor, MB_CASE_LOWER, 'UTF-8');
        $valor = mb_convert_case($valor, MB_CASE_TITLE, 'UTF-8');
        return $valor;
    }

    public static function capitalizar(string $valor): string
    {
        $valor = trim($valor);
        if ($valor === '') return $valor;
        return mb_strtoupper(mb_substr($valor, 0, 1)) . mb_substr($valor, 1);
    }
}
