<?php

declare(strict_types=1);

namespace App\Events;

use App\Services\LoggerService;

/**
 * Event Dispatcher
 *
 * Manages event listeners and dispatches events
 */
class EventDispatcher
{
    private static ?self $instance = null;
    private array $listeners = [];
    private LoggerService $logger;

    private function __construct()
    {
        $this->logger = new LoggerService();
    }

    /**
     * Get singleton instance
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Register an event listener
     *
     * @param string $eventClass Event class name
     * @param callable|string $listener Listener callback or class name
     * @param int $priority Higher priority listeners execute first (default 0)
     */
    public function listen(string $eventClass, callable|string $listener, int $priority = 0): void
    {
        if (!isset($this->listeners[$eventClass])) {
            $this->listeners[$eventClass] = [];
        }

        $this->listeners[$eventClass][] = [
            'listener' => $listener,
            'priority' => $priority,
        ];

        // Sort by priority (highest first)
        usort($this->listeners[$eventClass], function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });
    }

    /**
     * Dispatch an event to all registered listeners
     *
     * @param Event $event
     */
    public function dispatch(Event $event): void
    {
        $eventClass = get_class($event);

        if (!isset($this->listeners[$eventClass])) {
            return;
        }

        foreach ($this->listeners[$eventClass] as $listenerData) {
            $listener = $listenerData['listener'];

            try {
                if (is_callable($listener)) {
                    // Call closure or callable
                    $listener($event);
                } elseif (is_string($listener) && class_exists($listener)) {
                    // Instantiate and call listener class
                    $listenerInstance = new $listener();
                    if (method_exists($listenerInstance, 'handle')) {
                        $listenerInstance->handle($event);
                    }
                }
            } catch (\Exception $e) {
                // Log error but don't stop other listeners
                $this->logger->log('error', "Event listener error: " . $e->getMessage(), [
                    'event' => $eventClass,
                    'listener' => is_string($listener) ? $listener : 'closure',
                ]);
            }
        }
    }

    /**
     * Remove all listeners for an event
     *
     * @param string $eventClass
     */
    public function forget(string $eventClass): void
    {
        unset($this->listeners[$eventClass]);
    }

    /**
     * Get all registered listeners for an event
     *
     * @param string $eventClass
     * @return array
     */
    public function getListeners(string $eventClass): array
    {
        return $this->listeners[$eventClass] ?? [];
    }

    /**
     * Check if event has listeners
     *
     * @param string $eventClass
     * @return bool
     */
    public function hasListeners(string $eventClass): bool
    {
        return isset($this->listeners[$eventClass]) && !empty($this->listeners[$eventClass]);
    }
}
