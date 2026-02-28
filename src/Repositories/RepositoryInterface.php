<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Repository Interface
 *
 * Defines standard data access methods
 */
interface RepositoryInterface
{
    /**
     * Find a record by ID
     *
     * @param int $id
     * @return object|null
     */
    public function find(int $id): ?object;

    /**
     * Find a record by ID or throw exception
     *
     * @param int $id
     * @return object
     * @throws \App\Exceptions\NotFoundException
     */
    public function findOrFail(int $id): object;

    /**
     * Get all records
     *
     * @param array $filters
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getAll(array $filters = [], int $page = 1, int $limit = 10): array;

    /**
     * Create a new record
     *
     * @param array $data
     * @return int Record ID
     */
    public function create(array $data): int;

    /**
     * Update an existing record
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a record
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Count records matching criteria
     *
     * @param array $conditions
     * @return int
     */
    public function count(array $conditions = []): int;

    /**
     * Check if record exists
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool;
}
