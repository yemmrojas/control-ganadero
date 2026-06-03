<?php

namespace App\Domain\Contracts;

use App\Application\DTOs\SmaRequestData;

/**
 * Contrato para repositorio de consultas SMA.
 * 
 * Define las operaciones de persistencia sin exponer detalles de implementación.
 * La implementación vive en Infrastructure.
 */
interface QueryRepositoryInterface
{
    /**
     * Guarda una nueva consulta con sus cruces detectados.
     * 
     * @param SmaRequestData $requestData Parámetros de la consulta
     * @param array $crossovers Array de cruces detectados
     * @return int ID de la consulta guardada
     */
    public function save(SmaRequestData $requestData, array $crossovers): int;

    /**
     * Obtiene una consulta por su ID con sus cruces.
     * 
     * @param int $id ID de la consulta
     * @return object|null Objeto con los datos de la consulta o null si no existe
     */
    public function findById(int $id): ?object;

    /**
     * Obtiene todas las consultas ordenadas por fecha de creación descendente.
     * 
     * @return iterable Colección de consultas
     */
    public function getAllOrdered(): iterable;

    /**
     * Verifica si existe una consulta con el ID dado.
     * 
     * @param int $id ID de la consulta
     * @return bool True si existe, false en caso contrario
     */
    public function exists(int $id): bool;
}
