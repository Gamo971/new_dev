<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Router\Router;

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv->load();
}

// Gestion des requêtes API
$uri = $_SERVER['REQUEST_URI'];
if (strpos($uri, '/api/') === 0) {
    // Activer l'affichage des erreurs pour le débogage
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
    
    try {
        $router = new Router();
        $router->handleRequest();
    } catch (\Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Interface web pour la gestion des missions, tâches, clients et contacts
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionnaire de Missions - Cabinet Jarry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .tab.active { background-color: #3b82f6; color: white; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <header class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        <i class="fas fa-briefcase text-blue-600 mr-3"></i>
                        Gestionnaire de Missions
                    </h1>
                    <p class="text-gray-600 mt-2">Cabinet Jarry - Conseil en Entreprise</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Version 1.0.0</div>
                    <div class="text-sm text-gray-500"><?= date('d/m/Y H:i') ?></div>
                </div>
            </div>
        </header>

        <!-- Navigation Tabs -->
        <nav class="bg-white rounded-lg shadow-md p-4 mb-8">
            <div class="flex flex-wrap gap-2">
                <button class="tab px-4 py-2 rounded-lg border border-gray-300 hover:bg-blue-50 transition-colors active" onclick="showTab('missions')">
                    <i class="fas fa-tasks mr-2"></i>Missions
                </button>
                <button class="tab px-4 py-2 rounded-lg border border-gray-300 hover:bg-blue-50 transition-colors" onclick="showTab('taches')">
                    <i class="fas fa-list-check mr-2"></i>Tâches
                </button>
                <button class="tab px-4 py-2 rounded-lg border border-gray-300 hover:bg-blue-50 transition-colors" onclick="showTab('clients')">
                    <i class="fas fa-building mr-2"></i>Clients
                </button>
                <button class="tab px-4 py-2 rounded-lg border border-gray-300 hover:bg-blue-50 transition-colors" onclick="showTab('contacts')">
                    <i class="fas fa-address-book mr-2"></i>Contacts
                </button>
                <button class="tab px-4 py-2 rounded-lg border border-gray-300 hover:bg-blue-50 transition-colors" onclick="showTab('statistiques')">
                    <i class="fas fa-chart-bar mr-2"></i>Statistiques
                </button>
            </div>
        </nav>

        <!-- Contenu des onglets -->
        <main>
            <!-- Onglet Missions -->
            <div id="missions" class="tab-content active">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-tasks text-blue-600 mr-2"></i>Gestion des Missions
                        </h2>
                        <button onclick="openMissionModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Nouvelle Mission
                        </button>
                    </div>
                    
                    <!-- Filtres et recherche -->
                    <div class="mb-6 flex flex-wrap gap-4">
                        <div class="flex-1 min-w-64">
                            <input type="text" id="missionSearch" placeholder="Rechercher une mission..." 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <select id="missionStatutFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Tous les statuts</option>
                            <option value="en_attente">En attente</option>
                            <option value="en_cours">En cours</option>
                            <option value="en_pause">En pause</option>
                            <option value="terminee">Terminée</option>
                            <option value="annulee">Annulée</option>
                        </select>
                        <select id="missionPrioriteFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Toutes les priorités</option>
                            <option value="basse">Basse</option>
                            <option value="normale">Normale</option>
                            <option value="haute">Haute</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>
                    
                    <!-- Liste des missions -->
                    <div id="missionsList" class="space-y-4">
                        <!-- Les missions seront chargées ici -->
                    </div>
                </div>
            </div>

            <!-- Onglet Tâches -->
            <div id="taches" class="tab-content">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-list-check text-green-600 mr-2"></i>Gestion des Tâches
                        </h2>
                        <button onclick="openTacheModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Nouvelle Tâche
                        </button>
                    </div>
                    
                    <!-- Filtres et recherche -->
                    <div class="mb-6">
                        <!-- Barre de recherche -->
                        <div class="mb-4">
                            <input type="text" id="tacheSearch" placeholder="Rechercher une tâche..." 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>
                        
                        <!-- Filtres par statut -->
                        <div class="mb-4">
                            <label class="text-sm font-semibold text-gray-700 mb-2 block">
                                <i class="fas fa-filter mr-2"></i>Statut:
                            </label>
                            <div class="flex flex-wrap gap-2">
                                <label class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" class="tache-statut-filter mr-2" value="a_faire" checked>
                                    <span class="text-sm">📝 À faire</span>
                                </label>
                                <label class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" class="tache-statut-filter mr-2" value="en_cours" checked>
                                    <span class="text-sm">⚙️ En cours</span>
                                </label>
                                <label class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" class="tache-statut-filter mr-2" value="terminee">
                                    <span class="text-sm">✅ Terminée</span>
                                </label>
                                <label class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" class="tache-statut-filter mr-2" value="annulee">
                                    <span class="text-sm">❌ Annulée</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Filtres par priorité -->
                        <div>
                            <label class="text-sm font-semibold text-gray-700 mb-2 block">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Priorité:
                            </label>
                            <div class="flex flex-wrap gap-2">
                                <label class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" class="tache-priorite-filter mr-2" value="basse" checked>
                                    <span class="text-sm">🟢 Basse</span>
                                </label>
                                <label class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" class="tache-priorite-filter mr-2" value="normale" checked>
                                    <span class="text-sm">🟡 Normale</span>
                                </label>
                                <label class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" class="tache-priorite-filter mr-2" value="haute" checked>
                                    <span class="text-sm">🟠 Haute</span>
                                </label>
                                <label class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" class="tache-priorite-filter mr-2" value="urgente" checked>
                                    <span class="text-sm">🔴 Urgente</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Options de tri -->
                    <div class="mb-6 border-t border-b border-gray-200 py-4">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fas fa-sort text-green-600"></i>
                            <span class="text-sm font-semibold text-gray-700">Trier par:</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <!-- Tri par date -->
                            <button onclick="changeTachesSort('date_creation_desc')" 
                                    class="tache-sort-btn px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-blue-50 hover:border-blue-300 transition-colors flex items-center gap-2"
                                    data-sort="date_creation_desc">
                                <i class="fas fa-calendar-alt text-blue-600"></i>
                                <span>Plus récent</span>
                            </button>
                            <button onclick="changeTachesSort('date_creation_asc')" 
                                    class="tache-sort-btn px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-blue-50 hover:border-blue-300 transition-colors flex items-center gap-2"
                                    data-sort="date_creation_asc">
                                <i class="fas fa-calendar-alt text-blue-400"></i>
                                <span>Plus ancien</span>
                            </button>
                            
                            <!-- Tri par priorité -->
                            <button onclick="changeTachesSort('priorite_desc')" 
                                    class="tache-sort-btn px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-red-50 hover:border-red-300 transition-colors flex items-center gap-2"
                                    data-sort="priorite_desc">
                                <i class="fas fa-arrow-up text-red-600"></i>
                                <span>Priorité ↓</span>
                            </button>
                            <button onclick="changeTachesSort('priorite_asc')" 
                                    class="tache-sort-btn px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-green-50 hover:border-green-300 transition-colors flex items-center gap-2"
                                    data-sort="priorite_asc">
                                <i class="fas fa-arrow-down text-green-600"></i>
                                <span>Priorité ↑</span>
                            </button>
                            
                            <!-- Tri par échéance -->
                            <button onclick="changeTachesSort('echeance_asc')" 
                                    class="tache-sort-btn px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-orange-50 hover:border-orange-300 transition-colors flex items-center gap-2"
                                    data-sort="echeance_asc">
                                <i class="fas fa-clock text-orange-600"></i>
                                <span>Échéance proche</span>
                            </button>
                            <button onclick="changeTachesSort('echeance_desc')" 
                                    class="tache-sort-btn px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-orange-50 hover:border-orange-300 transition-colors flex items-center gap-2"
                                    data-sort="echeance_desc">
                                <i class="fas fa-clock text-orange-400"></i>
                                <span>Échéance lointaine</span>
                            </button>
                            
                            <!-- Tri par statut -->
                            <button onclick="changeTachesSort('statut')" 
                                    class="tache-sort-btn px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-purple-50 hover:border-purple-300 transition-colors flex items-center gap-2"
                                    data-sort="statut">
                                <i class="fas fa-tasks text-purple-600"></i>
                                <span>Par statut</span>
                            </button>
                            
                            <!-- Tri alphabétique -->
                            <button onclick="changeTachesSort('nom_asc')" 
                                    class="tache-sort-btn px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-indigo-50 hover:border-indigo-300 transition-colors flex items-center gap-2"
                                    data-sort="nom_asc">
                                <i class="fas fa-sort-alpha-down text-indigo-600"></i>
                                <span>A → Z</span>
                            </button>
                            <button onclick="changeTachesSort('nom_desc')" 
                                    class="tache-sort-btn px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-indigo-50 hover:border-indigo-300 transition-colors flex items-center gap-2"
                                    data-sort="nom_desc">
                                <i class="fas fa-sort-alpha-up text-indigo-600"></i>
                                <span>Z → A</span>
                            </button>
                            
                            <!-- Tri par mission -->
                            <button onclick="changeTachesSort('mission')" 
                                    class="tache-sort-btn px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-teal-50 hover:border-teal-300 transition-colors flex items-center gap-2"
                                    data-sort="mission">
                                <i class="fas fa-briefcase text-teal-600"></i>
                                <span>Par mission</span>
                            </button>
                            
                            <!-- Tri par temps -->
                            <button onclick="changeTachesSort('temps_estime_desc')" 
                                    class="tache-sort-btn px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-amber-50 hover:border-amber-300 transition-colors flex items-center gap-2"
                                    data-sort="temps_estime_desc">
                                <i class="fas fa-hourglass-end text-amber-600"></i>
                                <span>Plus long</span>
                            </button>
                            <button onclick="changeTachesSort('temps_estime_asc')" 
                                    class="tache-sort-btn px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-amber-50 hover:border-amber-300 transition-colors flex items-center gap-2"
                                    data-sort="temps_estime_asc">
                                <i class="fas fa-hourglass-start text-amber-600"></i>
                                <span>Plus court</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Liste des tâches -->
                    <div id="tachesList" class="space-y-4">
                        <!-- Les tâches seront chargées ici -->
                    </div>
                </div>
            </div>

            <!-- Onglet Clients -->
            <div id="clients" class="tab-content">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-building text-purple-600 mr-2"></i>Gestion des Clients
                        </h2>
                        <button onclick="openClientModal()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Nouveau Client
                        </button>
                    </div>
                    
                    <!-- Filtres et recherche -->
                    <div class="mb-6 flex flex-wrap gap-4">
                        <div class="flex-1 min-w-64">
                            <input type="text" id="clientSearch" placeholder="Rechercher un client..." 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                        <select id="clientStatutFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="">Tous les statuts</option>
                            <option value="actif">Actif</option>
                            <option value="inactif">Inactif</option>
                            <option value="suspendu">Suspendu</option>
                        </select>
                    </div>
                    
                    <!-- Liste des clients -->
                    <div id="clientsList" class="space-y-4">
                        <!-- Les clients seront chargés ici -->
                    </div>
                </div>
            </div>

            <!-- Onglet Contacts -->
            <div id="contacts" class="tab-content">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-address-book text-orange-600 mr-2"></i>Gestion des Contacts
                        </h2>
                        <button onclick="openContactModal()" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Nouveau Contact
                        </button>
                    </div>
                    
                    <!-- Filtres et recherche -->
                    <div class="mb-6 flex flex-wrap gap-4">
                        <div class="flex-1 min-w-64">
                            <input type="text" id="contactSearch" placeholder="Rechercher un contact..." 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        </div>
                        <select id="contactStatutFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                            <option value="">Tous les statuts</option>
                            <option value="actif">Actif</option>
                            <option value="inactif">Inactif</option>
                        </select>
                    </div>
                    
                    <!-- Liste des contacts -->
                    <div id="contactsList" class="space-y-4">
                        <!-- Les contacts seront chargés ici -->
                    </div>
                </div>
            </div>

            <!-- Onglet Statistiques -->
            <div id="statistiques" class="tab-content">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-chart-bar text-indigo-600 mr-2"></i>Statistiques Globales
                    </h2>
                    
                    <!-- Cartes de statistiques -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                            <div class="flex items-center">
                                <div class="p-2 bg-blue-600 text-white rounded-lg">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-blue-900" id="totalMissions">0</div>
                                    <div class="text-blue-700">Missions</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                            <div class="flex items-center">
                                <div class="p-2 bg-green-600 text-white rounded-lg">
                                    <i class="fas fa-list-check"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-green-900" id="totalTaches">0</div>
                                    <div class="text-green-700">Tâches</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-purple-50 p-6 rounded-lg border border-purple-200">
                            <div class="flex items-center">
                                <div class="p-2 bg-purple-600 text-white rounded-lg">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-purple-900" id="totalClients">0</div>
                                    <div class="text-purple-700">Clients</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-orange-50 p-6 rounded-lg border border-orange-200">
                            <div class="flex items-center">
                                <div class="p-2 bg-orange-600 text-white rounded-lg">
                                    <i class="fas fa-address-book"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-orange-900" id="totalContacts">0</div>
                                    <div class="text-orange-700">Contacts</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Graphiques et détails -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Missions par Statut</h3>
                            <div id="missionsStatutChart">
                                <!-- Graphique des missions par statut -->
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Tâches par Statut</h3>
                            <div id="tachesStatutChart">
                                <!-- Graphique des tâches par statut -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modals -->
    <div id="modalContainer">
        <!-- Modal Mission -->
        <div id="missionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 id="missionModalTitle" class="text-xl font-bold text-gray-800">Nouvelle Mission</h3>
                            <button onclick="closeMissionModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <form id="missionForm" onsubmit="saveMission(event)">
                            <input type="hidden" id="missionId" name="id">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Client *</label>
                                    <select id="missionClientId" name="client_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">Sélectionner un client</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom de la mission *</label>
                                    <input type="text" id="missionNom" name="nom" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea id="missionDescription" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                                    <select id="missionStatut" name="statut" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="en_attente">En attente</option>
                                        <option value="en_cours">En cours</option>
                                        <option value="en_pause">En pause</option>
                                        <option value="terminee">Terminée</option>
                                        <option value="annulee">Annulée</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Priorité</label>
                                    <select id="missionPriorite" name="priorite" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="basse">Basse</option>
                                        <option value="normale">Normale</option>
                                        <option value="haute">Haute</option>
                                        <option value="urgente">Urgente</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                                    <input type="date" id="missionDateDebut" name="date_debut" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin prévue</label>
                                    <input type="date" id="missionDateFin" name="date_fin_prevue" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Budget prévu (€)</label>
                                    <input type="number" id="missionBudget" name="budget_prevu" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Temps estimé (heures)</label>
                                    <input type="number" id="missionTemps" name="temps_estime" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <textarea id="missionNotes" name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                            </div>
                            
                            <div class="flex justify-end gap-3">
                                <button type="button" onclick="closeMissionModal()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Annuler
                                </button>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-save mr-2"></i>Sauvegarder
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tâche -->
        <div id="tacheModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 id="tacheModalTitle" class="text-xl font-bold text-gray-800">Nouvelle Tâche</h3>
                            <button onclick="closeTacheModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <form id="tacheForm" onsubmit="saveTache(event)">
                            <input type="hidden" id="tacheId" name="id">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Mission *</label>
                                    <select id="tacheMissionId" name="mission_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        <option value="">Sélectionner une mission</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom de la tâche *</label>
                                    <input type="text" id="tacheNom" name="nom" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea id="tacheDescription" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"></textarea>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                                    <select id="tacheStatut" name="statut" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        <option value="a_faire">À faire</option>
                                        <option value="en_cours">En cours</option>
                                        <option value="terminee">Terminée</option>
                                        <option value="annulee">Annulée</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Priorité</label>
                                    <select id="tachePriorite" name="priorite" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        <option value="basse">Basse</option>
                                        <option value="normale">Normale</option>
                                        <option value="haute">Haute</option>
                                        <option value="urgente">Urgente</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date d'échéance</label>
                                    <input type="date" id="tacheDateEcheance" name="date_echeance" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Assigné à</label>
                                    <input type="text" id="tacheAssigne" name="assigne_a" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Temps estimé (minutes)</label>
                                    <input type="number" id="tacheTempsEstime" name="temps_estime" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Ordre</label>
                                    <input type="number" id="tacheOrdre" name="ordre" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <textarea id="tacheNotes" name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"></textarea>
                            </div>
                            
                            <div class="flex justify-end gap-3">
                                <button type="button" onclick="closeTacheModal()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Annuler
                                </button>
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    <i class="fas fa-save mr-2"></i>Sauvegarder
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Client -->
        <div id="clientModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 id="clientModalTitle" class="text-xl font-bold text-gray-800">Nouveau Client</h3>
                            <button onclick="closeClientModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <form id="clientForm" onsubmit="saveClient(event)">
                            <input type="hidden" id="clientId" name="id">
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nom de l'entreprise *</label>
                                <input type="text" id="clientNom" name="nom" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" id="clientEmail" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                                    <input type="tel" id="clientTelephone" name="telephone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Adresse</label>
                                <textarea id="clientAdresse" name="adresse" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Ville</label>
                                    <input type="text" id="clientVille" name="ville" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Code postal</label>
                                    <input type="text" id="clientCodePostal" name="code_postal" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Pays</label>
                                    <input type="text" id="clientPays" name="pays" value="France" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">SIRET</label>
                                    <input type="text" id="clientSiret" name="siret" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                                    <select id="clientStatut" name="statut" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                        <option value="actif">Actif</option>
                                        <option value="inactif">Inactif</option>
                                        <option value="suspendu">Suspendu</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <textarea id="clientNotes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                            </div>
                            
                            <div class="flex justify-end gap-3">
                                <button type="button" onclick="closeClientModal()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Annuler
                                </button>
                                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                    <i class="fas fa-save mr-2"></i>Sauvegarder
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Contact -->
        <div id="contactModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 id="contactModalTitle" class="text-xl font-bold text-gray-800">Nouveau Contact</h3>
                            <button onclick="closeContactModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <form id="contactForm" onsubmit="saveContact(event)">
                            <input type="hidden" id="contactId" name="id">
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Client *</label>
                                <select id="contactClientId" name="client_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <option value="">Sélectionner un client</option>
                                </select>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Prénom *</label>
                                    <input type="text" id="contactPrenom" name="prenom" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                                    <input type="text" id="contactNom" name="nom" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" id="contactEmail" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                                    <input type="tel" id="contactTelephone" name="telephone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Poste</label>
                                    <input type="text" id="contactPoste" name="poste" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                                    <select id="contactStatut" name="statut" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                        <option value="actif">Actif</option>
                                        <option value="inactif">Inactif</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <textarea id="contactNotes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"></textarea>
                            </div>
                            
                            <div class="flex justify-end gap-3">
                                <button type="button" onclick="closeContactModal()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Annuler
                                </button>
                                <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                                    <i class="fas fa-save mr-2"></i>Sauvegarder
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let missions = [];
        let taches = [];
        let clients = [];
        let contacts = [];
        let currentTab = 'missions';
        let currentTacheSort = 'date_creation_desc'; // Tri par défaut

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            loadAllData();
            setupEventListeners();
            
            // Initialiser le bouton de tri par défaut
            changeTachesSort('date_creation_desc');
        });

        // Gestion des onglets
        function showTab(tabName) {
            // Masquer tous les onglets
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Désactiver tous les boutons d'onglets
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Afficher l'onglet sélectionné
            document.getElementById(tabName).classList.add('active');
            
            // Activer le bouton d'onglet sélectionné
            event.target.classList.add('active');
            
            currentTab = tabName;
            
            // Charger les données spécifiques à l'onglet
            switch(tabName) {
                case 'missions':
                    loadMissions();
                    break;
                case 'taches':
                    loadTaches();
                    break;
                case 'clients':
                    loadClients();
                    break;
                case 'contacts':
                    loadContacts();
                    break;
                case 'statistiques':
                    loadStatistiques();
                    break;
            }
        }

        // Chargement de toutes les données
        async function loadAllData() {
            await Promise.all([
                loadMissions(),
                loadTaches(),
                loadClients(),
                loadContacts()
            ]);
        }

        // Chargement des missions
        async function loadMissions() {
            try {
                const response = await fetch('/api/missions');
                const data = await response.json();
                
                if (data.success) {
                    missions = data.data;
                    displayMissions(missions);
                } else {
                    console.error('Erreur lors du chargement des missions:', data.error);
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        }

        // Affichage des missions
        function displayMissions(missionsToShow) {
            const container = document.getElementById('missionsList');
            
            if (missionsToShow.length === 0) {
                container.innerHTML = '<div class="text-center text-gray-500 py-8">Aucune mission trouvée</div>';
                return;
            }
            
            container.innerHTML = missionsToShow.map(mission => `
                <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">${mission.nom}</h3>
                            <p class="text-sm text-gray-600">Client: ${mission.client_nom || 'Non défini'}</p>
                        </div>
                        <div class="flex gap-2">
                            <span class="px-3 py-1 rounded-full text-sm ${mission.priorite_couleur}">
                                ${mission.priorite_libelle}
                            </span>
                            <span class="px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-800">
                                ${mission.statut_libelle}
                            </span>
                        </div>
                    </div>
                    
                    ${mission.description ? `<p class="text-gray-700 mb-4">${mission.description}</p>` : ''}
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600 mb-4">
                        ${mission.date_debut ? `<div><strong>Début:</strong> ${formatDate(mission.date_debut)}</div>` : ''}
                        ${mission.date_fin_prevue ? `<div><strong>Fin prévue:</strong> ${formatDate(mission.date_fin_prevue)}</div>` : ''}
                        ${mission.budget_prevu ? `<div><strong>Budget:</strong> ${formatCurrency(mission.budget_prevu)}</div>` : ''}
                        <div><strong>Temps:</strong> ${mission.temps_estime_formate}</div>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            Créée le ${formatDateTime(mission.created_at)}
                        </div>
                        <div class="flex gap-2">
                            <button onclick="openMissionModal(${mission.id})" class="text-blue-600 hover:text-blue-800" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteMission(${mission.id})" class="text-red-600 hover:text-red-800" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Chargement des tâches
        async function loadTaches() {
            try {
                const response = await fetch('/api/taches');
                const data = await response.json();
                
                if (data.success) {
                    taches = data.data;
                    displayTaches(taches);
                } else {
                    console.error('Erreur lors du chargement des tâches:', data.error);
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        }

        // Affichage des tâches
        function displayTaches(tachesToShow) {
            const container = document.getElementById('tachesList');
            
            if (tachesToShow.length === 0) {
                container.innerHTML = '<div class="text-center text-gray-500 py-8">Aucune tâche trouvée</div>';
                return;
            }
            
            // Appliquer le tri
            const sortedTaches = sortTaches(tachesToShow);
            
            container.innerHTML = sortedTaches.map(tache => `
                <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">${tache.nom}</h3>
                            <p class="text-sm text-gray-600">Mission: ${tache.mission_nom || 'Non définie'}</p>
                        </div>
                        <div class="flex gap-2">
                            <span class="px-3 py-1 rounded-full text-sm ${tache.priorite_couleur}">
                                ${tache.priorite_libelle}
                            </span>
                            <span class="px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-800">
                                ${tache.statut_libelle}
                            </span>
                        </div>
                    </div>
                    
                    ${tache.description ? `<p class="text-gray-700 mb-4">${tache.description}</p>` : ''}
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600 mb-4">
                        ${tache.date_echeance ? `<div><strong>Échéance:</strong> ${formatDate(tache.date_echeance)}</div>` : ''}
                        <div><strong>Temps estimé:</strong> ${tache.temps_estime_formate}</div>
                        <div><strong>Temps réel:</strong> ${tache.temps_reel_formate}</div>
                        ${tache.assigne_a ? `<div><strong>Assigné à:</strong> ${tache.assigne_a}</div>` : ''}
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            Créée le ${formatDateTime(tache.created_at)}
                        </div>
                        <div class="flex gap-2">
                            <button onclick="openTacheModal(${tache.id})" class="text-blue-600 hover:text-blue-800" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteTache(${tache.id})" class="text-red-600 hover:text-red-800" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Chargement des clients
        async function loadClients() {
            try {
                const response = await fetch('/api/clients');
                const data = await response.json();
                
                if (data.success) {
                    clients = data.data;
                    displayClients(clients);
                } else {
                    console.error('Erreur lors du chargement des clients:', data.error);
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        }

        // Affichage des clients
        function displayClients(clientsToShow) {
            const container = document.getElementById('clientsList');
            
            if (clientsToShow.length === 0) {
                container.innerHTML = '<div class="text-center text-gray-500 py-8">Aucun client trouvé</div>';
                return;
            }
            
            container.innerHTML = clientsToShow.map(client => `
                <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">${client.nom}</h3>
                            <p class="text-sm text-gray-600">${client.adresse_complete || 'Adresse non définie'}</p>
                        </div>
                        <div class="flex gap-2">
                            <span class="px-3 py-1 rounded-full text-sm ${client.statut === 'actif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                ${client.statut_libelle}
                            </span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm text-gray-600 mb-4">
                        ${client.email ? `<div><strong>Email:</strong> ${client.email}</div>` : ''}
                        ${client.telephone ? `<div><strong>Téléphone:</strong> ${client.telephone}</div>` : ''}
                        ${client.siret ? `<div><strong>SIRET:</strong> ${client.siret}</div>` : ''}
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            Créé le ${formatDateTime(client.created_at)}
                        </div>
                        <div class="flex gap-2">
                            <button onclick="openClientModal(${client.id})" class="text-blue-600 hover:text-blue-800" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteClient(${client.id})" class="text-red-600 hover:text-red-800" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Chargement des contacts
        async function loadContacts() {
            try {
                const response = await fetch('/api/contacts');
                const data = await response.json();
                
                if (data.success) {
                    contacts = data.data;
                    displayContacts(contacts);
                } else {
                    console.error('Erreur lors du chargement des contacts:', data.error);
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        }

        // Affichage des contacts
        function displayContacts(contactsToShow) {
            const container = document.getElementById('contactsList');
            
            if (contactsToShow.length === 0) {
                container.innerHTML = '<div class="text-center text-gray-500 py-8">Aucun contact trouvé</div>';
                return;
            }
            
            container.innerHTML = contactsToShow.map(contact => `
                <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">${contact.nom_complet}</h3>
                            <p class="text-sm text-gray-600">${contact.poste || 'Poste non défini'}</p>
                        </div>
                        <div class="flex gap-2">
                            <span class="px-3 py-1 rounded-full text-sm ${contact.statut === 'actif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                ${contact.statut_libelle}
                            </span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm text-gray-600 mb-4">
                        ${contact.email ? `<div><strong>Email:</strong> ${contact.email}</div>` : ''}
                        ${contact.telephone ? `<div><strong>Téléphone:</strong> ${contact.telephone}</div>` : ''}
                        <div><strong>Client:</strong> ${contact.client_id}</div>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            Créé le ${formatDateTime(contact.created_at)}
                        </div>
                        <div class="flex gap-2">
                            <button onclick="openContactModal(${contact.id})" class="text-blue-600 hover:text-blue-800" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteContact(${contact.id})" class="text-red-600 hover:text-red-800" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Chargement des statistiques
        async function loadStatistiques() {
            try {
                const [missionsResponse, tachesResponse, clientsResponse, contactsResponse] = await Promise.all([
                    fetch('/api/missions/statistiques'),
                    fetch('/api/taches/statistiques'),
                    fetch('/api/clients/statistiques'),
                    fetch('/api/contacts/statistiques')
                ]);
                
                const [missionsData, tachesData, clientsData, contactsData] = await Promise.all([
                    missionsResponse.json(),
                    tachesResponse.json(),
                    clientsResponse.json(),
                    contactsResponse.json()
                ]);
                
                if (missionsData.success) {
                    document.getElementById('totalMissions').textContent = missionsData.data.globales.total_missions || 0;
                }
                
                if (tachesData.success) {
                    document.getElementById('totalTaches').textContent = tachesData.data.globales.total_taches || 0;
                }
                
                if (clientsData.success) {
                    document.getElementById('totalClients').textContent = clientsData.data.total || 0;
                }
                
                if (contactsData.success) {
                    document.getElementById('totalContacts').textContent = contactsData.data.total || 0;
                }
            } catch (error) {
                console.error('Erreur lors du chargement des statistiques:', error);
            }
        }

        // Configuration des écouteurs d'événements
        function setupEventListeners() {
            // Recherche des missions
            document.getElementById('missionSearch').addEventListener('input', filterMissions);
            document.getElementById('missionStatutFilter').addEventListener('change', filterMissions);
            document.getElementById('missionPrioriteFilter').addEventListener('change', filterMissions);
            
            // Recherche des tâches
            document.getElementById('tacheSearch').addEventListener('input', filterTaches);
            
            // Filtres de statut des tâches (checkboxes)
            document.querySelectorAll('.tache-statut-filter').forEach(checkbox => {
                checkbox.addEventListener('change', filterTaches);
            });
            
            // Filtres de priorité des tâches (checkboxes)
            document.querySelectorAll('.tache-priorite-filter').forEach(checkbox => {
                checkbox.addEventListener('change', filterTaches);
            });
            
            // Recherche des clients
            document.getElementById('clientSearch').addEventListener('input', filterClients);
            document.getElementById('clientStatutFilter').addEventListener('change', filterClients);
            
            // Recherche des contacts
            document.getElementById('contactSearch').addEventListener('input', filterContacts);
            document.getElementById('contactStatutFilter').addEventListener('change', filterContacts);
        }

        // Filtrage des missions
        function filterMissions() {
            const search = document.getElementById('missionSearch').value.toLowerCase();
            const statutFilter = document.getElementById('missionStatutFilter').value;
            const prioriteFilter = document.getElementById('missionPrioriteFilter').value;
            
            let filtered = missions.filter(mission => {
                const matchesSearch = !search || 
                    mission.nom.toLowerCase().includes(search) ||
                    (mission.description && mission.description.toLowerCase().includes(search)) ||
                    (mission.client_nom && mission.client_nom.toLowerCase().includes(search));
                
                const matchesStatut = !statutFilter || mission.statut === statutFilter;
                const matchesPriorite = !prioriteFilter || mission.priorite === prioriteFilter;
                
                return matchesSearch && matchesStatut && matchesPriorite;
            });
            
            displayMissions(filtered);
        }

        // Filtrage des tâches
        function filterTaches() {
            const search = document.getElementById('tacheSearch').value.toLowerCase();
            
            // Récupérer les statuts cochés
            const selectedStatuts = Array.from(document.querySelectorAll('.tache-statut-filter:checked'))
                .map(cb => cb.value);
            
            // Récupérer les priorités cochées
            const selectedPriorites = Array.from(document.querySelectorAll('.tache-priorite-filter:checked'))
                .map(cb => cb.value);
            
            let filtered = taches.filter(tache => {
                const matchesSearch = !search || 
                    tache.nom.toLowerCase().includes(search) ||
                    (tache.description && tache.description.toLowerCase().includes(search)) ||
                    (tache.mission_nom && tache.mission_nom.toLowerCase().includes(search));
                
                // Si aucun statut n'est coché, on affiche tout
                const matchesStatut = selectedStatuts.length === 0 || selectedStatuts.includes(tache.statut);
                
                // Si aucune priorité n'est cochée, on affiche tout
                const matchesPriorite = selectedPriorites.length === 0 || selectedPriorites.includes(tache.priorite);
                
                return matchesSearch && matchesStatut && matchesPriorite;
            });
            
            displayTaches(filtered);
        }

        // Filtrage des clients
        function filterClients() {
            const search = document.getElementById('clientSearch').value.toLowerCase();
            const statutFilter = document.getElementById('clientStatutFilter').value;
            
            let filtered = clients.filter(client => {
                const matchesSearch = !search || 
                    client.nom.toLowerCase().includes(search) ||
                    (client.email && client.email.toLowerCase().includes(search)) ||
                    (client.ville && client.ville.toLowerCase().includes(search));
                
                const matchesStatut = !statutFilter || client.statut === statutFilter;
                
                return matchesSearch && matchesStatut;
            });
            
            displayClients(filtered);
        }

        // Filtrage des contacts
        function filterContacts() {
            const search = document.getElementById('contactSearch').value.toLowerCase();
            const statutFilter = document.getElementById('contactStatutFilter').value;
            
            let filtered = contacts.filter(contact => {
                const matchesSearch = !search || 
                    contact.nom_complet.toLowerCase().includes(search) ||
                    (contact.email && contact.email.toLowerCase().includes(search)) ||
                    (contact.poste && contact.poste.toLowerCase().includes(search));
                
                const matchesStatut = !statutFilter || contact.statut === statutFilter;
                
                return matchesSearch && matchesStatut;
            });
            
            displayContacts(filtered);
        }

        // Fonctions utilitaires
        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR');
        }

        function formatDateTime(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleString('fr-FR');
        }

        function formatCurrency(amount) {
            if (!amount) return '';
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            }).format(amount);
        }

        // Fonctions des modals
        async function openMissionModal(id = null) {
            const modal = document.getElementById('missionModal');
            const title = document.getElementById('missionModalTitle');
            const form = document.getElementById('missionForm');

            // Charger la liste des clients en premier
            await loadClientsForSelect('#missionClientId');

            if (id) {
                title.textContent = 'Modifier la Mission';
                await loadMissionData(id);
            } else {
                title.textContent = 'Nouvelle Mission';
                form.reset();
                document.getElementById('missionId').value = '';
            }
            
            modal.classList.remove('hidden');
        }

        async function openTacheModal(id = null) {
            const modal = document.getElementById('tacheModal');
            const title = document.getElementById('tacheModalTitle');
            const form = document.getElementById('tacheForm');

            // Charger la liste des missions en premier
            await loadMissionsForSelect('#tacheMissionId');

            if (id) {
                title.textContent = 'Modifier la Tâche';
                await loadTacheData(id);
            } else {
                title.textContent = 'Nouvelle Tâche';
                form.reset();
                document.getElementById('tacheId').value = '';
            }
            
            modal.classList.remove('hidden');
        }

        async function openClientModal(id = null) {
            const modal = document.getElementById('clientModal');
            const title = document.getElementById('clientModalTitle');
            const form = document.getElementById('clientForm');

            if (id) {
                title.textContent = 'Modifier le Client';
                await loadClientData(id);
            } else {
                title.textContent = 'Nouveau Client';
                form.reset();
                document.getElementById('clientId').value = '';
            }

            modal.classList.remove('hidden');
        }

        async function openContactModal(id = null) {
            const modal = document.getElementById('contactModal');
            const title = document.getElementById('contactModalTitle');
            const form = document.getElementById('contactForm');

            // Charger la liste des clients en premier
            await loadClientsForSelect('#contactClientId');

            if (id) {
                title.textContent = 'Modifier le Contact';
                await loadContactData(id);
            } else {
                title.textContent = 'Nouveau Contact';
                form.reset();
                document.getElementById('contactId').value = '';
            }
            
            modal.classList.remove('hidden');
        }

        // Fonctions de fermeture des modals
        function closeMissionModal() {
            document.getElementById('missionModal').classList.add('hidden');
        }

        function closeTacheModal() {
            document.getElementById('tacheModal').classList.add('hidden');
        }

        function closeClientModal() {
            document.getElementById('clientModal').classList.add('hidden');
        }

        function closeContactModal() {
            document.getElementById('contactModal').classList.add('hidden');
        }

        // Fonctions de chargement des données pour les modals
        async function loadMissionData(id) {
            try {
                const response = await fetch(`/api/missions/${id}`);
                const data = await response.json();
                
                if (data.success) {
                    const mission = data.data;
                    document.getElementById('missionId').value = mission.id;
                    document.getElementById('missionClientId').value = mission.client_id;
                    document.getElementById('missionNom').value = mission.nom;
                    document.getElementById('missionDescription').value = mission.description || '';
                    document.getElementById('missionStatut').value = mission.statut;
                    document.getElementById('missionPriorite').value = mission.priorite;
                    document.getElementById('missionDateDebut').value = mission.date_debut || '';
                    document.getElementById('missionDateFin').value = mission.date_fin_prevue || '';
                    document.getElementById('missionBudget').value = mission.budget_prevu || '';
                    document.getElementById('missionTemps').value = mission.temps_estime ? Math.round(mission.temps_estime / 60) : '';
                    document.getElementById('missionNotes').value = mission.notes || '';
                }
            } catch (error) {
                console.error('Erreur lors du chargement de la mission:', error);
            }
        }

        async function loadTacheData(id) {
            try {
                const response = await fetch(`/api/taches/${id}`);
                const data = await response.json();
                
                if (data.success) {
                    const tache = data.data;
                    document.getElementById('tacheId').value = tache.id;
                    document.getElementById('tacheMissionId').value = tache.mission_id;
                    document.getElementById('tacheNom').value = tache.nom;
                    document.getElementById('tacheDescription').value = tache.description || '';
                    document.getElementById('tacheStatut').value = tache.statut;
                    document.getElementById('tachePriorite').value = tache.priorite;
                    document.getElementById('tacheDateEcheance').value = tache.date_echeance || '';
                    document.getElementById('tacheAssigne').value = tache.assigne_a || '';
                    document.getElementById('tacheTempsEstime').value = tache.temps_estime || '';
                    document.getElementById('tacheOrdre').value = tache.ordre || '';
                    document.getElementById('tacheNotes').value = tache.notes || '';
                }
            } catch (error) {
                console.error('Erreur lors du chargement de la tâche:', error);
            }
        }

        async function loadClientData(id) {
            try {
                const response = await fetch(`/api/clients/${id}`);
                const data = await response.json();
                
                if (data.success) {
                    const client = data.data;
                    document.getElementById('clientId').value = client.id;
                    document.getElementById('clientNom').value = client.nom;
                    document.getElementById('clientEmail').value = client.email || '';
                    document.getElementById('clientTelephone').value = client.telephone || '';
                    document.getElementById('clientAdresse').value = client.adresse || '';
                    document.getElementById('clientVille').value = client.ville || '';
                    document.getElementById('clientCodePostal').value = client.code_postal || '';
                    document.getElementById('clientPays').value = client.pays || '';
                    document.getElementById('clientSiret').value = client.siret || '';
                    document.getElementById('clientStatut').value = client.statut;
                    document.getElementById('clientNotes').value = client.notes || '';
                }
            } catch (error) {
                console.error('Erreur lors du chargement du client:', error);
            }
        }

        async function loadContactData(id) {
            try {
                const response = await fetch(`/api/contacts/${id}`);
                const data = await response.json();
                
                if (data.success) {
                    const contact = data.data;
                    document.getElementById('contactId').value = contact.id;
                    document.getElementById('contactClientId').value = contact.client_id;
                    document.getElementById('contactPrenom').value = contact.prenom;
                    document.getElementById('contactNom').value = contact.nom;
                    document.getElementById('contactEmail').value = contact.email || '';
                    document.getElementById('contactTelephone').value = contact.telephone || '';
                    document.getElementById('contactPoste').value = contact.poste || '';
                    document.getElementById('contactStatut').value = contact.statut;
                    document.getElementById('contactNotes').value = contact.notes || '';
                }
            } catch (error) {
                console.error('Erreur lors du chargement du contact:', error);
            }
        }

        // Fonctions de chargement des listes pour les selects
        async function loadClientsForSelect(selectId) {
            try {
                const response = await fetch('/api/clients');
                const data = await response.json();
                
                if (data.success) {
                    const select = document.querySelector(selectId);
                    select.innerHTML = '<option value="">Sélectionner un client</option>';
                    data.data.forEach(client => {
                        const option = document.createElement('option');
                        option.value = client.id;
                        option.textContent = client.nom;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Erreur lors du chargement des clients:', error);
            }
        }

        async function loadMissionsForSelect(selectId) {
            try {
                const response = await fetch('/api/missions');
                const data = await response.json();
                
                if (data.success) {
                    const select = document.querySelector(selectId);
                    select.innerHTML = '<option value="">Sélectionner une mission</option>';
                    data.data.forEach(mission => {
                        const option = document.createElement('option');
                        option.value = mission.id;
                        option.textContent = mission.nom;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Erreur lors du chargement des missions:', error);
            }
        }

        // Fonctions de sauvegarde
        async function saveMission(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData.entries());
            
            // Convertir le temps estimé en minutes
            if (data.temps_estime) {
                data.temps_estime = parseInt(data.temps_estime) * 60;
            }

            const missionId = document.getElementById('missionId').value;
            const url = missionId ? `/api/missions/${missionId}` : '/api/missions';
            const method = missionId ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    closeMissionModal();
                    loadMissions();
                    loadStatistiques();
                    showNotification('Mission sauvegardée avec succès', 'success');
                } else {
                    showNotification('Erreur: ' + (result.error || 'Erreur inconnue'), 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur lors de la sauvegarde', 'error');
            }
        }

        async function saveTache(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData.entries());

            const tacheId = document.getElementById('tacheId').value;
            const url = tacheId ? `/api/taches/${tacheId}` : '/api/taches';
            const method = tacheId ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    closeTacheModal();
                    loadTaches();
                    loadStatistiques();
                    showNotification('Tâche sauvegardée avec succès', 'success');
                } else {
                    showNotification('Erreur: ' + (result.error || 'Erreur inconnue'), 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur lors de la sauvegarde', 'error');
            }
        }

        async function saveClient(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData.entries());

            const clientId = document.getElementById('clientId').value;
            const url = clientId ? `/api/clients/${clientId}` : '/api/clients';
            const method = clientId ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    closeClientModal();
                    loadClients();
                    loadStatistiques();
                    showNotification('Client sauvegardé avec succès', 'success');
                } else {
                    showNotification('Erreur: ' + (result.error || 'Erreur inconnue'), 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur lors de la sauvegarde', 'error');
            }
        }

        async function saveContact(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData.entries());

            const contactId = document.getElementById('contactId').value;
            const url = contactId ? `/api/contacts/${contactId}` : '/api/contacts';
            const method = contactId ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    closeContactModal();
                    loadContacts();
                    loadStatistiques();
                    showNotification('Contact sauvegardé avec succès', 'success');
                } else {
                    showNotification('Erreur: ' + (result.error || 'Erreur inconnue'), 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur lors de la sauvegarde', 'error');
            }
        }

        // Fonctions de suppression
        async function deleteMission(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette mission ?')) {
                return;
            }

            try {
                const response = await fetch(`/api/missions/${id}`, {
                    method: 'DELETE'
                });

                const data = await response.json();

                if (data.success) {
                    loadMissions();
                    loadStatistiques();
                    showNotification('Mission supprimée avec succès', 'success');
                } else {
                    showNotification('Erreur: ' + (data.error || 'Erreur lors de la suppression'), 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur lors de la suppression', 'error');
            }
        }

        async function deleteTache(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?')) {
                return;
            }

            try {
                const response = await fetch(`/api/taches/${id}`, {
                    method: 'DELETE'
                });

                const data = await response.json();

                if (data.success) {
                    loadTaches();
                    loadStatistiques();
                    showNotification('Tâche supprimée avec succès', 'success');
                } else {
                    showNotification('Erreur: ' + (data.error || 'Erreur lors de la suppression'), 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur lors de la suppression', 'error');
            }
        }

        async function deleteClient(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce client ?')) {
                return;
            }

            try {
                const response = await fetch(`/api/clients/${id}`, {
                    method: 'DELETE'
                });

                const data = await response.json();

                if (data.success) {
                    loadClients();
                    loadStatistiques();
                    showNotification('Client supprimé avec succès', 'success');
                } else {
                    showNotification('Erreur: ' + (data.error || 'Erreur lors de la suppression'), 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur lors de la suppression', 'error');
            }
        }

        async function deleteContact(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce contact ?')) {
                return;
            }

            try {
                const response = await fetch(`/api/contacts/${id}`, {
                    method: 'DELETE'
                });

                const data = await response.json();

                if (data.success) {
                    loadContacts();
                    loadStatistiques();
                    showNotification('Contact supprimé avec succès', 'success');
                } else {
                    showNotification('Erreur: ' + (data.error || 'Erreur lors de la suppression'), 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur lors de la suppression', 'error');
            }
        }

        // Changer le tri des tâches
        function changeTachesSort(sortType) {
            currentTacheSort = sortType;
            
            // Mettre à jour l'apparence des boutons
            document.querySelectorAll('.tache-sort-btn').forEach(btn => {
                if (btn.dataset.sort === sortType) {
                    btn.classList.add('bg-green-100', 'border-green-500', 'font-semibold');
                    btn.classList.remove('border-gray-300');
                } else {
                    btn.classList.remove('bg-green-100', 'border-green-500', 'font-semibold');
                    btn.classList.add('border-gray-300');
                }
            });
            
            // Réappliquer les filtres avec le nouveau tri
            filterTaches();
        }

        // Fonction de tri des tâches
        function sortTaches(tachesToSort) {
            const sortBy = currentTacheSort;
            const sorted = [...tachesToSort];
            
            const prioriteOrder = { 'urgente': 4, 'haute': 3, 'normale': 2, 'basse': 1 };
            const statutOrder = { 'a_faire': 1, 'en_cours': 2, 'terminee': 3, 'annulee': 4 };
            
            switch(sortBy) {
                case 'date_creation_desc':
                    sorted.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                    break;
                case 'date_creation_asc':
                    sorted.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                    break;
                case 'priorite_desc':
                    sorted.sort((a, b) => (prioriteOrder[b.priorite] || 0) - (prioriteOrder[a.priorite] || 0));
                    break;
                case 'priorite_asc':
                    sorted.sort((a, b) => (prioriteOrder[a.priorite] || 0) - (prioriteOrder[b.priorite] || 0));
                    break;
                case 'echeance_asc':
                    sorted.sort((a, b) => {
                        if (!a.date_echeance && !b.date_echeance) return 0;
                        if (!a.date_echeance) return 1;
                        if (!b.date_echeance) return -1;
                        return new Date(a.date_echeance) - new Date(b.date_echeance);
                    });
                    break;
                case 'echeance_desc':
                    sorted.sort((a, b) => {
                        if (!a.date_echeance && !b.date_echeance) return 0;
                        if (!a.date_echeance) return 1;
                        if (!b.date_echeance) return -1;
                        return new Date(b.date_echeance) - new Date(a.date_echeance);
                    });
                    break;
                case 'statut':
                    sorted.sort((a, b) => (statutOrder[a.statut] || 0) - (statutOrder[b.statut] || 0));
                    break;
                case 'nom_asc':
                    sorted.sort((a, b) => a.nom.localeCompare(b.nom));
                    break;
                case 'nom_desc':
                    sorted.sort((a, b) => b.nom.localeCompare(a.nom));
                    break;
                case 'mission':
                    sorted.sort((a, b) => (a.mission_nom || '').localeCompare(b.mission_nom || ''));
                    break;
                case 'temps_estime_desc':
                    sorted.sort((a, b) => (b.temps_estime || 0) - (a.temps_estime || 0));
                    break;
                case 'temps_estime_asc':
                    sorted.sort((a, b) => (a.temps_estime || 0) - (b.temps_estime || 0));
                    break;
                default:
                    // Par défaut: date de création descendante
                    sorted.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            }
            
            return sorted;
        }

        // Fonction de notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                'bg-blue-500 text-white'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
</body>
</html>