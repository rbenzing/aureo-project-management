<?php

declare(strict_types=1);

use App\Core\Database;
use App\Services\LoggerService;
use App\Services\SecurityService;
use App\Services\SettingsService;
use DI\Container;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

/**
 * Dependency Injection Container Configuration
 *
 * This file configures the PHP-DI container with all application services,
 * defining how dependencies should be resolved and injected.
 */

$containerBuilder = new ContainerBuilder();

// Enable compilation in production for better performance
if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
    $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}

$containerBuilder->addDefinitions([
    // Core Services
    Database::class => function () {
        // Use factory method for clean DI instantiation
        return Database::create();
    },

    // Logger Service
    LoggerService::class => function (ContainerInterface $c) {
        return new LoggerService();
    },

    // Settings Service (autowired - Setting model will be instantiated automatically)
    SettingsService::class => function (ContainerInterface $c) {
        return new SettingsService();
    },

    // Security Service
    SecurityService::class => function (ContainerInterface $c) {
        return new SecurityService(
            $c->get(SettingsService::class),
            $c->get(Database::class)
        );
    },

    // Middleware
    \App\Middleware\AuthMiddleware::class => function (ContainerInterface $c) {
        return new \App\Middleware\AuthMiddleware();
    },

    \App\Middleware\CsrfMiddleware::class => function (ContainerInterface $c) {
        return new \App\Middleware\CsrfMiddleware();
    },

    \App\Middleware\SessionMiddleware::class => function (ContainerInterface $c) {
        return new \App\Middleware\SessionMiddleware();
    },

    \App\Middleware\ActivityMiddleware::class => function (ContainerInterface $c) {
        return new \App\Middleware\ActivityMiddleware();
    },

    // Models
    \App\Models\User::class => function (ContainerInterface $c) {
        return new \App\Models\User();
    },

    \App\Models\Project::class => function (ContainerInterface $c) {
        return new \App\Models\Project();
    },

    \App\Models\Task::class => function (ContainerInterface $c) {
        return new \App\Models\Task();
    },

    \App\Models\Milestone::class => function (ContainerInterface $c) {
        return new \App\Models\Milestone();
    },

    \App\Models\Sprint::class => function (ContainerInterface $c) {
        return new \App\Models\Sprint();
    },

    // Controllers
    \App\Controllers\AuthController::class => function (ContainerInterface $c) {
        return new \App\Controllers\AuthController(
            $c->get(\App\Middleware\AuthMiddleware::class),
            $c->get(\App\Models\User::class),
            $c->get(SecurityService::class)
        );
    },

    \App\Controllers\DashboardController::class => function (ContainerInterface $c) {
        return new \App\Controllers\DashboardController(
            $c->get(\App\Middleware\AuthMiddleware::class),
            $c->get(\App\Models\User::class),
            $c->get(\App\Models\Project::class),
            $c->get(\App\Models\Task::class),
            $c->get(\App\Models\Milestone::class),
            $c->get(\App\Models\Sprint::class)
        );
    },

    // Additional controllers will be added as they are refactored
]);

// Build and return the container
return $containerBuilder->build();
