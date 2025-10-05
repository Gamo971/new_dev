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
    <link rel="stylesheet" href="/css/style.css">
    
    <!-- Bibliothèques pour le Planning -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/locales/fr.global.min.js"></script>
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
                <button class="tab px-4 py-2 rounded-lg border border-gray-300 hover:bg-blue-50 transition-colors" onclick="showTab('planning'); showPlanningTab();">
                    <i class="fas fa-calendar-alt mr-2"></i>Planning
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
                        <!-- Barre de recherche et tri -->
                        <div class="flex flex-wrap gap-4 mb-4">
                            <div class="flex-1 min-w-64">
                                <input type="text" id="tacheSearch" placeholder="Rechercher une tâche..." 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            </div>
                            <select id="tacheSortBy" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                <option value="date_creation_desc">📅 Plus récent</option>
                                <option value="date_creation_asc">📅 Plus ancien</option>
                                <option value="priorite_desc">🔴 Priorité (haute → basse)</option>
                                <option value="priorite_asc">🟢 Priorité (basse → haute)</option>
                                <option value="echeance_asc">⏰ Échéance (proche → lointaine)</option>
                                <option value="echeance_desc">⏰ Échéance (lointaine → proche)</option>
                                <option value="statut">📊 Par statut</option>
                                <option value="nom_asc">🔤 Nom (A → Z)</option>
                                <option value="nom_desc">🔤 Nom (Z → A)</option>
                                <option value="mission">💼 Par mission</option>
                                <option value="temps_estime_desc">⏳ Plus long</option>
                                <option value="temps_estime_asc">⏳ Plus court</option>
                            </select>
                        </div>
                        
                        <!-- Section Filtres (collapsible) -->
                        <div class="border border-gray-300 rounded-lg overflow-hidden">
                            <!-- En-tête des filtres -->
                            <button onclick="toggleTacheFilters()" 
                                    class="w-full px-4 py-3 bg-gray-50 hover:bg-gray-100 transition-colors flex items-center justify-between">
                                <span class="font-semibold text-gray-700 flex items-center gap-2">
                                    <i class="fas fa-filter text-green-600"></i>
                                    Filtres avancés
                                </span>
                                <i id="tacheFiltersIcon" class="fas fa-chevron-down text-gray-500 transition-transform"></i>
                            </button>
                            
                            <!-- Contenu des filtres -->
                            <div id="tacheFiltersContent" class="hidden">
                                <!-- Filtres par statut -->
                                <div class="p-4 border-t border-gray-200">
                                    <label class="text-sm font-semibold text-gray-700 mb-2 block">
                                        <i class="fas fa-tasks mr-2"></i>Statut:
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
                                <div class="p-4 border-t border-gray-200">
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

            <!-- Onglet Planning -->
            <div id="planning" class="tab-content">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <h2 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-calendar-alt text-purple-600 mr-2"></i>Planning des Tâches
                        </h2>
                        <button onclick="showSchedulingModal()" 
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors flex items-center gap-2">
                            <i class="fas fa-wand-magic-sparkles"></i>
                            Ordonnancement auto
                        </button>
                    </div>
                    
                    <!-- Barre d'outils des vues -->
                    <div class="mb-6 flex flex-wrap gap-2 border-b border-gray-200 pb-4">
                        <button class="view-btn active px-4 py-2 rounded-lg border border-gray-300 hover:bg-blue-50 transition-colors" 
                                data-view="kanban" 
                                onclick="showPlanningView('kanban')">
                            <i class="fas fa-columns mr-2"></i>Kanban
                        </button>
                        <button class="view-btn px-4 py-2 rounded-lg border border-gray-300 hover:bg-blue-50 transition-colors" 
                                data-view="agenda" 
                                onclick="showPlanningView('agenda')">
                            <i class="fas fa-calendar mr-2"></i>Agenda
                        </button>
                        <button class="view-btn px-4 py-2 rounded-lg border border-gray-300 hover:bg-blue-50 transition-colors" 
                                data-view="liste" 
                                onclick="showPlanningView('liste')">
                            <i class="fas fa-list mr-2"></i>Liste
                        </button>
                    </div>
                    
                    <!-- Conteneur pour les vues -->
                    <div id="planning-container">
                        <!-- Le contenu sera injecté ici -->
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

    <!-- Scripts JavaScript modulaires -->
    <script src="/js/utils.js"></script>
    <script src="/js/components.js"></script>
    <script src="/js/api.js"></script>
    <script src="/js/ui.js"></script>
    <script src="/js/filters.js"></script>
    <script src="/js/modals.js"></script>
    <!-- Planning -->
    <script src="/js/planning/scheduling.js"></script>
    <script src="/js/planning/kanban.js"></script>
    <script src="/js/planning/agenda.js"></script>
    <script src="/js/planning.js"></script>
    <script src="/js/app.js"></script>
</body>
</html>