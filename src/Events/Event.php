<?php

declare(strict_types=1);

namespace App\Events;

/**
 * Base Event Class
 *
 * All events should extend this class
 */
abstract class Event
{
    protected string $name;
    protected array $payload = [];
    protected float $timestamp;

    public function __construct(array $payload = [])
    {
        $this->payload = $payload;
        $this->timestamp = microtime(true);
        $this->name = static::class;
    }

    /**
     * Get event name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get event payload
     *
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * Get event timestamp
     *
     * @return float
     */
    public function getTimestamp(): float
    {
        return $this->timestamp;
    }

    /**
     * Get specific payload value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->payload[$key] ?? $default;
    }
}
