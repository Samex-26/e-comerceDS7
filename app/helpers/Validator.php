<?php
// Clase para validación de datos de entrada.
// Cada método retorna bool; el controlador decide cómo manejar el error.

class Validator
{
    public static function email(string $valor): bool
    {
        $valor = trim($valor);
        return filter_var($valor, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function noVacio(string $valor): bool
    {
        return trim($valor) !== '';
    }

    public static function numerico(mixed $valor): bool
    {
        return is_numeric($valor);
    }

    public static function rangoNumerico(float $valor, float $min, float $max): bool
    {
        return $valor >= $min && $valor <= $max;
    }

    public static function longitud(string $valor, int $min, int $max): bool
    {
        $len = mb_strlen(trim($valor));
        return $len >= $min && $len <= $max;
    }

    public static function entero(mixed $valor): bool
    {
        return filter_var($valor, FILTER_VALIDATE_INT) !== false;
    }

    public static function decimal(mixed $valor): bool
    {
        return preg_match('/^\d+(\.\d+)?$/', (string) $valor) === 1;
    }

    public static function url(string $valor): bool
    {
        return filter_var($valor, FILTER_VALIDATE_URL) !== false;
    }

    public static function minimo(float $valor, float $min): bool
    {
        return $valor >= $min;
    }

    public static function maximo(float $valor, float $max): bool
    {
        return $valor <= $max;
    }

    public static function enteroPositivo(mixed $valor): bool
    {
        return self::entero($valor) && (int) $valor > 0;
    }
}
