<?php
// Implementación de CriptoServiceInterface para firma digital de ventas.
// Usa hash_hmac('sha256', ...) con la clave secreta definida en config.php.
// El método verificar() usa hash_equals() para prevenir timing attacks.

class FirmaDigitalService implements CriptoServiceInterface
{
    public function procesar(string $dato): string
    {
        return hash_hmac('sha256', $dato, SECRET_KEY);
    }

    public function verificar(string $dato, string $resultadoEsperado): bool
    {
        $firmaCalculada = $this->procesar($dato);
        return hash_equals($resultadoEsperado, $firmaCalculada);
    }
}
