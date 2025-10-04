<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Database\Database;
use App\Repositories\ClientRepository;
use App\Repositories\ContactRepository;
use App\Repositories\MissionRepository;
use App\Repositories\TacheRepository;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Mission;
use App\Models\Tache;

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__);
if (file_exists(__DIR__ . '/.env')) {
    $dotenv->load();
}

echo "🚀 POPULATION DE DONNÉES DE TEST\n";
echo "================================\n\n";

// Initialiser la base de données
$databasePath = $_ENV['DATABASE_PATH'] ?? 'storage/database.sqlite';
$database = new Database($databasePath);
$clientRepo = new ClientRepository($database);
$contactRepo = new ContactRepository($database);
$missionRepo = new MissionRepository($database);
$tacheRepo = new TacheRepository($database);

echo "✅ Base de données initialisée : {$databasePath}\n\n";

// 1. Créer des clients
echo "1️⃣  Création des clients...\n";

$clients = [
    new Client(
        nom: 'Entreprise ABC SARL',
        email: 'contact@entreprise-abc.fr',
        telephone: '01 23 45 67 89',
        adresse: '123 Rue de la République',
        ville: 'Paris',
        codePostal: '75001',
        pays: 'France',
        siret: '12345678901234',
        statut: 'actif',
        notes: 'Client principal - Secteur industrie'
    ),
    new Client(
        nom: 'TechStart SAS',
        email: 'info@techstart.com',
        telephone: '02 34 56 78 90',
        adresse: '456 Avenue des Champs-Élysées',
        ville: 'Lyon',
        codePostal: '69001',
        pays: 'France',
        siret: '23456789012345',
        statut: 'actif',
        notes: 'Startup technologique - Croissance rapide'
    ),
    new Client(
        nom: 'Groupe Industriel XYZ',
        email: 'direction@groupe-xyz.fr',
        telephone: '03 45 67 89 01',
        adresse: '789 Boulevard de la Liberté',
        ville: 'Marseille',
        codePostal: '13001',
        pays: 'France',
        siret: '34567890123456',
        statut: 'actif',
        notes: 'Groupe industriel - Secteur automobile'
    ),
    new Client(
        nom: 'Consulting Pro',
        email: 'contact@consulting-pro.fr',
        telephone: '04 56 78 90 12',
        adresse: '321 Rue du Commerce',
        ville: 'Toulouse',
        codePostal: '31000',
        pays: 'France',
        siret: '45678901234567',
        statut: 'inactif',
        notes: 'Cabinet de conseil - Client suspendu'
    )
];

$clientIds = [];
foreach ($clients as $client) {
    $savedClient = $clientRepo->save($client);
    $clientIds[] = $savedClient->getId();
    echo "✅ Client créé : {$savedClient->getNom()} (ID: {$savedClient->getId()})\n";
}
echo "\n";

// 2. Créer des contacts
echo "2️⃣  Création des contacts...\n";

$contacts = [
    new Contact(
        clientId: $clientIds[0],
        prenom: 'Jean',
        nom: 'Dupont',
        email: 'jean.dupont@entreprise-abc.fr',
        telephone: '01 23 45 67 88',
        poste: 'Directeur Général',
        statut: 'actif',
        notes: 'Contact principal - Décisionnaire'
    ),
    new Contact(
        clientId: $clientIds[0],
        prenom: 'Marie',
        nom: 'Martin',
        email: 'marie.martin@entreprise-abc.fr',
        telephone: '01 23 45 67 87',
        poste: 'Responsable RH',
        statut: 'actif',
        notes: 'Contact RH - Gestion du personnel'
    ),
    new Contact(
        clientId: $clientIds[1],
        prenom: 'Pierre',
        nom: 'Durand',
        email: 'pierre.durand@techstart.com',
        telephone: '02 34 56 78 89',
        poste: 'CTO',
        statut: 'actif',
        notes: 'Directeur technique - Innovation'
    ),
    new Contact(
        clientId: $clientIds[2],
        prenom: 'Sophie',
        nom: 'Leroy',
        email: 'sophie.leroy@groupe-xyz.fr',
        telephone: '03 45 67 89 00',
        poste: 'Directrice Financière',
        statut: 'actif',
        notes: 'Gestion financière - Budgets'
    )
];

foreach ($contacts as $contact) {
    $savedContact = $contactRepo->save($contact);
    echo "✅ Contact créé : {$savedContact->getNomComplet()} pour {$clients[$contact->getClientId()-1]->getNom()}\n";
}
echo "\n";

// 3. Créer des missions
echo "3️⃣  Création des missions...\n";

$missions = [
    new Mission(
        clientId: $clientIds[0],
        nom: 'Audit Financier Complet',
        description: 'Audit financier complet pour l\'entreprise ABC - Analyse des processus comptables et financiers',
        statut: 'en_cours',
        priorite: 'haute',
        dateDebut: new \DateTime('2024-01-15'),
        dateFinPrevue: new \DateTime('2024-03-15'),
        budgetPrevu: 25000.00,
        tempsEstime: 4800, // 80 heures
        notes: 'Mission critique - Délais serrés'
    ),
    new Mission(
        clientId: $clientIds[1],
        nom: 'Business Plan TechStart',
        description: 'Élaboration du business plan pour la levée de fonds de TechStart',
        statut: 'en_attente',
        priorite: 'urgente',
        dateDebut: new \DateTime('2024-02-01'),
        dateFinPrevue: new \DateTime('2024-04-30'),
        budgetPrevu: 18000.00,
        tempsEstime: 3600, // 60 heures
        notes: 'Business plan pour série A - 2M€'
    ),
    new Mission(
        clientId: $clientIds[2],
        nom: 'Restructuration Organisationnelle',
        description: 'Accompagnement à la restructuration organisationnelle du Groupe XYZ',
        statut: 'terminee',
        priorite: 'normale',
        dateDebut: new \DateTime('2023-10-01'),
        dateFinPrevue: new \DateTime('2023-12-31'),
        dateFinReelle: new \DateTime('2023-12-15'),
        budgetPrevu: 35000.00,
        budgetReel: 32800.00,
        tempsEstime: 6000, // 100 heures
        tempsReel: 5800, // 96.7 heures
        notes: 'Mission terminée avec succès - Objectifs atteints'
    ),
    new Mission(
        clientId: $clientIds[0],
        nom: 'Formation Équipe RH',
        description: 'Formation de l\'équipe RH aux nouvelles procédures de recrutement',
        statut: 'en_pause',
        priorite: 'basse',
        dateDebut: new \DateTime('2024-01-20'),
        dateFinPrevue: new \DateTime('2024-02-28'),
        budgetPrevu: 8000.00,
        tempsEstime: 1200, // 20 heures
        notes: 'Formation reportée - Priorité faible'
    )
];

$missionIds = [];
foreach ($missions as $mission) {
    $savedMission = $missionRepo->save($mission);
    $missionIds[] = $savedMission->getId();
    echo "✅ Mission créée : {$savedMission->getNom()} (ID: {$savedMission->getId()})\n";
}
echo "\n";

// 4. Créer des tâches
echo "4️⃣  Création des tâches...\n";

$taches = [
    // Tâches pour Mission 1 (Audit Financier)
    new Tache(
        missionId: $missionIds[0],
        nom: 'Analyse des états financiers',
        description: 'Examen détaillé du bilan, compte de résultat et flux de trésorerie',
        statut: 'terminee',
        priorite: 'haute',
        dateEcheance: new \DateTime('2024-01-30'),
        tempsEstime: 960, // 16 heures
        tempsReel: 920, // 15.3 heures
        ordre: 1,
        assigneA: 'Auditeur Senior',
        notes: 'Analyse terminée - Points d\'attention identifiés'
    ),
    new Tache(
        missionId: $missionIds[0],
        nom: 'Vérification des procédures comptables',
        description: 'Audit des processus comptables et contrôle interne',
        statut: 'en_cours',
        priorite: 'haute',
        dateEcheance: new \DateTime('2024-02-15'),
        tempsEstime: 1440, // 24 heures
        tempsReel: 600, // 10 heures
        ordre: 2,
        assigneA: 'Auditeur Junior',
        notes: 'En cours - 40% terminé'
    ),
    new Tache(
        missionId: $missionIds[0],
        nom: 'Rédaction du rapport final',
        description: 'Synthèse des conclusions et recommandations',
        statut: 'a_faire',
        priorite: 'normale',
        dateEcheance: new \DateTime('2024-03-05'),
        tempsEstime: 1200, // 20 heures
        ordre: 3,
        assigneA: 'Auditeur Senior',
        notes: 'À faire après validation des analyses'
    ),
    
    // Tâches pour Mission 2 (Business Plan)
    new Tache(
        missionId: $missionIds[1],
        nom: 'Étude de marché',
        description: 'Analyse concurrentielle et positionnement marché',
        statut: 'a_faire',
        priorite: 'urgente',
        dateEcheance: new \DateTime('2024-02-20'),
        tempsEstime: 1800, // 30 heures
        ordre: 1,
        assigneA: 'Consultant Marketing',
        notes: 'Priorité haute - Base du business plan'
    ),
    new Tache(
        missionId: $missionIds[1],
        nom: 'Modélisation financière',
        description: 'Projections financières sur 5 ans',
        statut: 'a_faire',
        priorite: 'haute',
        dateEcheance: new \DateTime('2024-03-15'),
        tempsEstime: 1440, // 24 heures
        ordre: 2,
        assigneA: 'Analyste Financier',
        notes: 'Scénarios optimiste, réaliste, pessimiste'
    ),
    new Tache(
        missionId: $missionIds[1],
        nom: 'Présentation investisseurs',
        description: 'Préparation de la présentation pour les investisseurs',
        statut: 'a_faire',
        priorite: 'normale',
        dateEcheance: new \DateTime('2024-04-20'),
        tempsEstime: 600, // 10 heures
        ordre: 3,
        assigneA: 'Consultant Senior',
        notes: 'Pitch deck et présentation orale'
    ),
    
    // Tâches pour Mission 3 (Restructuration - Terminée)
    new Tache(
        missionId: $missionIds[2],
        nom: 'Diagnostic organisationnel',
        description: 'Analyse de l\'organisation actuelle et identification des dysfonctionnements',
        statut: 'terminee',
        priorite: 'haute',
        dateEcheance: new \DateTime('2023-10-31'),
        dateFinReelle: new \DateTime('2023-10-28 16:00:00'),
        tempsEstime: 2400, // 40 heures
        tempsReel: 2200, // 36.7 heures
        ordre: 1,
        assigneA: 'Consultant RH',
        notes: 'Diagnostic complet - Rapport remis'
    ),
    new Tache(
        missionId: $missionIds[2],
        nom: 'Conception nouvelle organisation',
        description: 'Définition de la nouvelle structure organisationnelle',
        statut: 'terminee',
        priorite: 'haute',
        dateEcheance: new \DateTime('2023-11-30'),
        dateFinReelle: new \DateTime('2023-11-25 14:30:00'),
        tempsEstime: 2400, // 40 heures
        tempsReel: 2600, // 43.3 heures
        ordre: 2,
        assigneA: 'Consultant Senior',
        notes: 'Organigramme validé par la direction'
    ),
    new Tache(
        missionId: $missionIds[2],
        nom: 'Plan de mise en œuvre',
        description: 'Élaboration du plan de déploiement de la nouvelle organisation',
        statut: 'terminee',
        priorite: 'normale',
        dateEcheance: new \DateTime('2023-12-15'),
        dateFinReelle: new \DateTime('2023-12-10 17:00:00'),
        tempsEstime: 1200, // 20 heures
        tempsReel: 1000, // 16.7 heures
        ordre: 3,
        assigneA: 'Chef de Projet',
        notes: 'Plan détaillé avec jalons et responsabilités'
    ),
    
    // Tâches pour Mission 4 (Formation RH - En pause)
    new Tache(
        missionId: $missionIds[3],
        nom: 'Conception modules de formation',
        description: 'Développement des contenus de formation',
        statut: 'a_faire',
        priorite: 'basse',
        dateEcheance: new \DateTime('2024-02-10'),
        tempsEstime: 600, // 10 heures
        ordre: 1,
        assigneA: 'Formateur RH',
        notes: 'Modules interactifs et cas pratiques'
    ),
    new Tache(
        missionId: $missionIds[3],
        nom: 'Session de formation',
        description: 'Animation de la session de formation pour l\'équipe RH',
        statut: 'a_faire',
        priorite: 'basse',
        dateEcheance: new \DateTime('2024-02-25'),
        tempsEstime: 600, // 10 heures
        ordre: 2,
        assigneA: 'Formateur RH',
        notes: 'Formation en présentiel - 8 participants'
    )
];

foreach ($taches as $tache) {
    $savedTache = $tacheRepo->save($tache);
    echo "✅ Tâche créée : {$savedTache->getNom()} pour mission {$savedTache->getMissionId()}\n";
}
echo "\n";

// 5. Statistiques finales
echo "5️⃣  Statistiques finales...\n";

$statsClients = $clientRepo->getStatistiques();
echo "📊 Clients par statut :\n";
foreach ($statsClients as $statut => $data) {
    echo "   - {$statut}: {$data['count']} clients\n";
}

$statsMissions = $missionRepo->getStatistiques();
echo "\n📊 Missions par statut :\n";
foreach ($statsMissions as $statut => $data) {
    echo "   - {$statut}: {$data['count']} missions (Budget: {$data['budget_prevu']}€)\n";
}

$statsTaches = $tacheRepo->getStatistiques();
echo "\n📊 Tâches par statut :\n";
foreach ($statsTaches as $statut => $data) {
    $heuresEstimees = round($data['temps_estime'] / 60, 1);
    $heuresReelles = round($data['temps_reel'] / 60, 1);
    echo "   - {$statut}: {$data['count']} tâches ({$heuresEstimees}h estimé, {$heuresReelles}h réel)\n";
}

echo "\n🎉 DONNÉES DE TEST CRÉÉES AVEC SUCCÈS !\n";
echo "=========================================\n";
echo "✅ " . count($clients) . " clients créés\n";
echo "✅ " . count($contacts) . " contacts créés\n";
echo "✅ " . count($missions) . " missions créées\n";
echo "✅ " . count($taches) . " tâches créées\n";
echo "✅ Données réalistes et variées\n";
echo "✅ Tous les statuts et priorités représentés\n\n";

echo "🌐 Accédez maintenant à l'application : http://localhost:8000\n";
echo "📱 Vous pouvez tester toutes les fonctionnalités avec des données réelles !\n";
