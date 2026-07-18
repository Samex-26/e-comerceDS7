<?php
// Interfaz común para servicios criptográficos (hashing y firma digital).
// Desacopla la lógica de negocio del algoritmo específico.
// Toda operación de transformación + verificación usa este mismo contrato.

interface CriptoServiceInterface
{
    public function procesar(string $dato): string;
    public function verificar(string $dato, string $resultadoEsperado): bool;
}
