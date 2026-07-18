<?php
// Implementación de CriptoServiceInterface para hashing de contraseñas.
// Usa password_hash() con PASSWORD_BCRYPT y password_verify().

class PasswordHasherService implements CriptoServiceInterface
{
    public function procesar(string $dato): string
    {
        return password_hash($dato, PASSWORD_BCRYPT);
    }

    public function verificar(string $dato, string $resultadoEsperado): bool
    {
        return password_verify($dato, $resultadoEsperado);
    }
}
