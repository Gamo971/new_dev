<?php

declare(strict_types=1);

namespace App\Router;

use App\Controllers\ClientController;
use App\Controllers\ContactController;
use App\Controllers\MissionController;
use App\Controllers\TacheController;

class Router
{
    private array $routes = [];

    public function __construct()
    {
        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        // Routes pour les clients
        $this->addRoute('GET', '/api/clients', [ClientController::class, 'index']);
        $this->addRoute('GET', '/api/clients/(\d+)', [ClientController::class, 'show']);
        $this->addRoute('POST', '/api/clients', [ClientController::class, 'store']);
        $this->addRoute('PUT', '/api/clients/(\d+)', [ClientController::class, 'update']);
        $this->addRoute('DELETE', '/api/clients/(\d+)', [ClientController::class, 'delete']);
        $this->addRoute('GET', '/api/clients/search', [ClientController::class, 'search']);
        $this->addRoute('GET', '/api/clients/statistiques', [ClientController::class, 'statistiques']);

        // Routes pour les contacts
        $this->addRoute('GET', '/api/contacts', [ContactController::class, 'index']);
        $this->addRoute('GET', '/api/contacts/(\d+)', [ContactController::class, 'show']);
        $this->addRoute('POST', '/api/contacts', [ContactController::class, 'store']);
        $this->addRoute('PUT', '/api/contacts/(\d+)', [ContactController::class, 'update']);
        $this->addRoute('DELETE', '/api/contacts/(\d+)', [ContactController::class, 'delete']);
        $this->addRoute('GET', '/api/contacts/client/(\d+)', [ContactController::class, 'findByClient']);
        $this->addRoute('GET', '/api/contacts/search', [ContactController::class, 'search']);
        $this->addRoute('GET', '/api/contacts/statistiques', [ContactController::class, 'statistiques']);

        // Routes pour les missions
        $this->addRoute('GET', '/api/missions', [MissionController::class, 'index']);
        $this->addRoute('GET', '/api/missions/(\d+)', [MissionController::class, 'show']);
        $this->addRoute('POST', '/api/missions', [MissionController::class, 'store']);
        $this->addRoute('PUT', '/api/missions/(\d+)', [MissionController::class, 'update']);
        $this->addRoute('DELETE', '/api/missions/(\d+)', [MissionController::class, 'delete']);
        $this->addRoute('GET', '/api/missions/client/(\d+)', [MissionController::class, 'findByClient']);
        $this->addRoute('GET', '/api/missions/statut/([^/]+)', [MissionController::class, 'findByStatut']);
        $this->addRoute('GET', '/api/missions/search', [MissionController::class, 'search']);
        $this->addRoute('GET', '/api/missions/retard', [MissionController::class, 'enRetard']);
        $this->addRoute('GET', '/api/missions/statistiques', [MissionController::class, 'statistiques']);

        // Routes pour les tâches
        $this->addRoute('GET', '/api/taches', [TacheController::class, 'index']);
        $this->addRoute('GET', '/api/taches/(\d+)', [TacheController::class, 'show']);
        $this->addRoute('POST', '/api/taches', [TacheController::class, 'store']);
        $this->addRoute('PUT', '/api/taches/(\d+)', [TacheController::class, 'update']);
        $this->addRoute('DELETE', '/api/taches/(\d+)', [TacheController::class, 'delete']);
        $this->addRoute('GET', '/api/taches/mission/(\d+)', [TacheController::class, 'findByMission']);
        $this->addRoute('GET', '/api/taches/statut/([^/]+)', [TacheController::class, 'findByStatut']);
        $this->addRoute('GET', '/api/taches/assigne/([^/]+)', [TacheController::class, 'findByAssigne']);
        $this->addRoute('GET', '/api/taches/search', [TacheController::class, 'search']);
        $this->addRoute('GET', '/api/taches/retard', [TacheController::class, 'enRetard']);
        $this->addRoute('GET', '/api/taches/statistiques', [TacheController::class, 'statistiques']);
    }

    private function addRoute(string $method, string $pattern, array $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getCurrentUri();

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchRoute($route['pattern'], $uri)) {
                $this->executeHandler($route['handler'], $uri);
                return;
            }
        }

        $this->handleNotFound();
    }

    private function getCurrentUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'];
        
        // Supprimer les paramètres de requête
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        return $uri;
    }

    private function matchRoute(string $pattern, string $uri): bool
    {
        // Le pattern est déjà une regex, il suffit de l'encadrer
        $regex = '#^' . $pattern . '$#';
        
        return preg_match($regex, $uri) === 1;
    }

    private function executeHandler(array $handler, string $uri): void
    {
        [$controllerClass, $method] = $handler;
        
        // Extraire les paramètres de l'URI
        $params = $this->extractParams($uri);
        
        // Créer une instance du contrôleur
        $controller = $this->createController($controllerClass);
        
        // Appeler la méthode du contrôleur
        if (method_exists($controller, $method)) {
            call_user_func_array([$controller, $method], $params);
        } else {
            $this->handleNotFound();
        }
    }

    private function extractParams(string $uri): array
    {
        $params = [];
        
        // Trouver la route correspondante pour extraire les paramètres
        $method = $_SERVER['REQUEST_METHOD'];
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method) {
                $regex = '#^' . $route['pattern'] . '$#';
                if (preg_match($regex, $uri, $matches)) {
                    // Supprimer le premier élément (correspondance complète)
                    array_shift($matches);
                    // Convertir les paramètres numériques en int
                    $params = array_map(function($param) {
                        return is_numeric($param) ? (int) $param : $param;
                    }, $matches);
                    break;
                }
            }
        }
        
        return $params;
    }

    private function createController(string $controllerClass)
    {
        // Créer les dépendances nécessaires
        $database = $this->createDatabase();
        
        // Créer les repositories
        $clientRepository = new \App\Repositories\ClientRepository($database);
        $contactRepository = new \App\Repositories\ContactRepository($database);
        $missionRepository = new \App\Repositories\MissionRepository($database);
        $tacheRepository = new \App\Repositories\TacheRepository($database);
        
        // Créer le contrôleur avec les bonnes dépendances
        return match ($controllerClass) {
            ClientController::class => new ClientController($clientRepository),
            ContactController::class => new ContactController($contactRepository),
            MissionController::class => new MissionController($missionRepository),
            TacheController::class => new TacheController($tacheRepository),
            default => throw new \Exception("Contrôleur non trouvé: {$controllerClass}")
        };
    }

    private function createDatabase(): \App\Database\Database
    {
        // Charger les variables d'environnement
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
        if (file_exists(__DIR__ . '/../../.env')) {
            $dotenv->load();
        }
        
        $databasePath = $_ENV['DATABASE_PATH'] ?? __DIR__ . '/../../storage/database.sqlite';
        return new \App\Database\Database($databasePath);
    }

    private function handleNotFound(): void
    {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => 'Route non trouvée'
        ], JSON_UNESCAPED_UNICODE);
    }
}
