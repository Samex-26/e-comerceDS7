<?php
// Helper para carga y aplicación del idioma según el usuario autenticado.
// Expone el array de traducciones a las vistas.

class IdiomaHelper
{
    private static ?array $lang = null;

    /**
     * Carga el array de traducciones según el idioma del usuario en sesión.
     */
    public static function cargar(): array
    {
        if (self::$lang !== null) {
            return self::$lang;
        }

        $codigo = self::getCodigo();
        $file = BASE_PATH . '/lang/' . $codigo . '.php';

        if (file_exists($file)) {
            self::$lang = require $file;
        } else {
            self::$lang = require BASE_PATH . '/lang/es.php';
        }

        return self::$lang;
    }

    /**
     * Obtiene el código de idioma activo (sesión o por defecto 'es').
     */
    public static function getCodigo(): string
    {
        if (isset($_SESSION['idioma_codigo'])) {
            return $_SESSION['idioma_codigo'];
        }
        return 'es';
    }
}
