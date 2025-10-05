# 🎨 Composants Réutilisables

## Vue d'ensemble

Le fichier `components.js` contient une bibliothèque de **composants UI réutilisables** qui génèrent du HTML de manière cohérente et maintenable.

### Avantages

✅ **DRY** - Don't Repeat Yourself  
✅ **Consistance** - Même style partout  
✅ **Maintenabilité** - Un seul endroit à modifier  
✅ **Testabilité** - Composants isolés  
✅ **Lisibilité** - Code plus clair  

---

## 📦 Composants disponibles

### 1. Badge

Affiche un badge coloré (statut, priorité).

**Signature :**
```javascript
Badge(text, color = 'bg-gray-100 text-gray-800')
```

**Exemple :**
```javascript
Badge('En cours', 'bg-blue-100 text-blue-800')
// → <span class="px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">En cours</span>
```

**Utilisation courante :**
```javascript
// Statut
Badge(mission.statut_libelle, 'bg-gray-100 text-gray-800')

// Priorité
Badge(mission.priorite_libelle, mission.priorite_couleur)
```

---

### 2. IconBadge

Badge avec une icône Font Awesome.

**Signature :**
```javascript
IconBadge(icon, text, color = 'bg-gray-100 text-gray-800')
```

**Exemple :**
```javascript
IconBadge('fa-check', 'Terminée', 'bg-green-100 text-green-800')
```

---

### 3. InfoField

Affiche un champ d'information formaté (label + valeur).

**Signature :**
```javascript
InfoField(label, value)
```

**Exemple :**
```javascript
InfoField('Email', 'contact@exemple.fr')
// → <div><strong>Email:</strong> contact@exemple.fr</div>

InfoField('Email', null)
// → '' (retourne vide si pas de valeur)
```

**Utilisation :**
```javascript
const fields = [
    InfoField('Début', formatDate(mission.date_debut)),
    InfoField('Budget', formatCurrency(mission.budget_prevu)),
    InfoField('Temps', mission.temps_estime_formate)
].join('');
```

---

### 4. ActionButton

Bouton d'action individuel avec icône.

**Signature :**
```javascript
ActionButton(icon, action, title, color = 'text-blue-600 hover:text-blue-800')
```

**Exemple :**
```javascript
ActionButton('fa-edit', 'editMission(1)', 'Modifier', 'text-blue-600 hover:text-blue-800')
```

---

### 5. ActionButtons

Groupe de boutons Modifier + Supprimer.

**Signature :**
```javascript
ActionButtons(entity, id)
```

**Exemple :**
```javascript
ActionButtons('mission', 5)
// Génère automatiquement :
// - Bouton Modifier → openMissionModal(5)
// - Bouton Supprimer → deleteMission(5)
```

**Entities supportées :** `mission`, `tache`, `client`, `contact`

---

### 6. Card

Carte complète pour afficher une entité (mission, tâche, client, contact).

**Signature :**
```javascript
Card({
    title,        // Titre principal
    subtitle,     // Sous-titre
    badges,       // HTML des badges
    description,  // Description (optionnel)
    fields,       // HTML des champs d'info
    footer        // HTML du footer
})
```

**Exemple complet :**
```javascript
Card({
    title: 'Refonte site web',
    subtitle: 'Client: Acme Corp',
    badges: Badge('Haute', 'bg-red-100 text-red-800') + Badge('En cours', 'bg-blue-100 text-blue-800'),
    description: 'Refonte complète du site web avec nouveau design',
    fields: InfoField('Budget', '15000€') + InfoField('Échéance', '2025-12-31'),
    footer: `
        <div class="text-sm text-gray-500">Créée le 01/10/2025</div>
        ${ActionButtons('mission', 1)}
    `
})
```

**Structure générée :**
```
┌─────────────────────────────────────┐
│ Titre                      [Badges] │
│ Sous-titre                          │
├─────────────────────────────────────┤
│ Description (si fournie)            │
├─────────────────────────────────────┤
│ [Champs d'information en grille]    │
├─────────────────────────────────────┤
│ Footer (date)         [Boutons]     │
└─────────────────────────────────────┘
```

---

### 7. EmptyState

État vide quand il n'y a pas de données.

**Signature :**
```javascript
EmptyState(message, icon = 'fa-inbox')
```

**Exemple :**
```javascript
if (missions.length === 0) {
    container.innerHTML = EmptyState('Aucune mission trouvée', 'fa-tasks');
}
```

---

### 8. FormField

Champ de formulaire avec label.

**Signature :**
```javascript
FormField({
    label,          // Label du champ
    id,             // ID HTML
    type,           // Type: text, email, tel, date, number, textarea, select
    required,       // true/false
    value,          // Valeur par défaut
    options,        // Pour select: [{value, label}]
    placeholder,    // Placeholder
    rows            // Pour textarea
})
```

**Exemples :**

**Input text :**
```javascript
FormField({
    label: 'Nom de la mission',
    id: 'missionNom',
    type: 'text',
    required: true,
    placeholder: 'Entrez le nom...'
})
```

**Textarea :**
```javascript
FormField({
    label: 'Description',
    id: 'missionDescription',
    type: 'textarea',
    rows: 3
})
```

**Select :**
```javascript
FormField({
    label: 'Statut',
    id: 'missionStatut',
    type: 'select',
    options: [
        { value: 'en_attente', label: 'En attente' },
        { value: 'en_cours', label: 'En cours' },
        { value: 'terminee', label: 'Terminée' }
    ],
    value: 'en_cours'
})
```

---

### 9. FormRow

Ligne de formulaire avec plusieurs champs côte à côte.

**Signature :**
```javascript
FormRow(fields, columns = 2)
```

**Exemple :**
```javascript
FormRow([
    FormField({ label: 'Prénom', id: 'prenom', required: true }),
    FormField({ label: 'Nom', id: 'nom', required: true })
], 2)
```

---

### 10. SearchBar

Barre de recherche stylisée.

**Signature :**
```javascript
SearchBar(id, placeholder = 'Rechercher...')
```

**Exemple :**
```javascript
SearchBar('missionSearch', 'Rechercher une mission...')
```

---

### 11. FilterSelect

Menu déroulant de filtrage.

**Signature :**
```javascript
FilterSelect(id, options, defaultLabel = 'Tous', ringColor = 'blue')
```

**Exemple :**
```javascript
FilterSelect('missionStatutFilter', [
    { value: 'en_attente', label: 'En attente' },
    { value: 'en_cours', label: 'En cours' },
    { value: 'terminee', label: 'Terminée' }
], 'Tous les statuts', 'blue')
```

---

### 12. StatCard

Carte de statistique (tableau de bord).

**Signature :**
```javascript
StatCard({
    icon,     // Icône Font Awesome
    value,    // Valeur numérique
    label,    // Label
    color     // Couleur: blue, green, purple, orange, indigo
})
```

**Exemple :**
```javascript
StatCard({
    icon: 'fa-tasks',
    value: '12',
    label: 'Missions',
    color: 'blue'
})
```

---

### 13. Button

Bouton stylisé générique.

**Signature :**
```javascript
Button({
    text,       // Texte du bouton
    icon,       // Icône (optionnel)
    onclick,    // Action onclick
    color,      // Couleur: blue, green, purple, orange, red, gray
    type        // Type: button, submit
})
```

**Exemple :**
```javascript
Button({
    text: 'Sauvegarder',
    icon: 'fa-save',
    type: 'submit',
    color: 'blue'
})
```

---

### 14. TabButton

Bouton d'onglet pour la navigation.

**Signature :**
```javascript
TabButton(id, icon, text, active = false)
```

**Exemple :**
```javascript
TabButton('missions', 'fa-tasks', 'Missions', true)
```

---

## 🎯 Exemples d'utilisation

### Afficher une liste de missions

```javascript
function displayMissions(missions) {
    const container = document.getElementById('missionsList');
    
    if (missions.length === 0) {
        container.innerHTML = EmptyState('Aucune mission trouvée', 'fa-tasks');
        return;
    }
    
    container.innerHTML = missions.map(mission => {
        const badges = Badge(mission.priorite_libelle, mission.priorite_couleur) + 
                      Badge(mission.statut_libelle, 'bg-gray-100 text-gray-800');
        
        const fields = [
            InfoField('Budget', formatCurrency(mission.budget_prevu)),
            InfoField('Échéance', formatDate(mission.date_fin_prevue))
        ].join('');
        
        const footer = `
            <div class="text-sm text-gray-500">Créée le ${formatDateTime(mission.created_at)}</div>
            ${ActionButtons('mission', mission.id)}
        `;
        
        return Card({
            title: mission.nom,
            subtitle: `Client: ${mission.client_nom}`,
            badges: badges,
            description: mission.description,
            fields: fields,
            footer: footer
        });
    }).join('');
}
```

### Créer un formulaire

```javascript
const formHTML = `
    ${FormRow([
        FormField({ label: 'Nom', id: 'nom', required: true }),
        FormField({ label: 'Email', id: 'email', type: 'email' })
    ], 2)}
    
    ${FormField({
        label: 'Description',
        id: 'description',
        type: 'textarea',
        rows: 4
    })}
    
    ${FormRow([
        FormField({
            label: 'Statut',
            id: 'statut',
            type: 'select',
            options: [
                { value: 'actif', label: 'Actif' },
                { value: 'inactif', label: 'Inactif' }
            ]
        }),
        FormField({
            label: 'Date',
            id: 'date',
            type: 'date'
        })
    ], 2)}
`;
```

---

## 🎨 Palette de couleurs

### Badges et boutons

- **blue** : Actions principales, liens
- **green** : Succès, actif, validé
- **red** : Erreur, suppression, urgent
- **orange** : Attention, avertissement
- **purple** : Clients, entreprises
- **gray** : Neutre, inactif

### Classes Tailwind courantes

```javascript
// Priorités
'bg-green-100 text-green-800'  // Basse
'bg-yellow-100 text-yellow-800' // Normale
'bg-orange-100 text-orange-800' // Haute
'bg-red-100 text-red-800'      // Urgente

// Statuts
'bg-blue-100 text-blue-800'    // En cours
'bg-green-100 text-green-800'  // Terminé
'bg-gray-100 text-gray-800'    // Autre
```

---

## 📝 Bonnes pratiques

### 1. Toujours utiliser les composants

❌ **Mauvais :**
```javascript
const html = `<span class="px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">En cours</span>`;
```

✅ **Bon :**
```javascript
const html = Badge('En cours', 'bg-blue-100 text-blue-800');
```

### 2. Composer les composants

```javascript
// Petit composants → Gros composants
const badges = Badge('Haute', 'bg-red-100 text-red-800') + 
              Badge('En cours', 'bg-blue-100 text-blue-800');

const fields = [
    InfoField('Budget', '10000€'),
    InfoField('Temps', '40h')
].join('');

const card = Card({ title, subtitle, badges, fields, footer });
```

### 3. Validation des données

```javascript
// Toujours vérifier les valeurs nulles
InfoField('Email', mission.email || '')  // ✅
InfoField('Email', mission.email)        // ❌ Peut afficher "undefined"
```

### 4. Filtrer les champs vides

```javascript
const fields = [
    InfoField('Email', client.email || ''),
    InfoField('Téléphone', client.telephone || ''),
    InfoField('SIRET', client.siret || '')
].filter(f => f).join('');  // ✅ Supprime les champs vides
```

---

## 🚀 Avantages de cette approche

| Avant | Après |
|-------|-------|
| 50 lignes de HTML dupliqué | 10 lignes avec composants |
| Modification dans 4 fichiers | Modification dans 1 fichier |
| Inconsistances de style | Style uniforme |
| Difficile à maintenir | Facile à maintenir |
| Code répétitif | Code DRY |

---

## 🔧 Extension

Pour ajouter un nouveau composant :

1. **Créer la fonction** dans `components.js`
2. **Documenter** les paramètres et exemples
3. **Tester** avec différentes valeurs
4. **Utiliser** dans `ui.js`, `modals.js`, etc.

**Exemple :**
```javascript
/**
 * Composant Tooltip - Infobulle
 * @param {string} text - Texte de l'infobulle
 * @param {string} content - Contenu HTML
 * @returns {string} HTML avec tooltip
 */
function Tooltip(text, content) {
    return `
        <div class="relative group">
            ${content}
            <div class="absolute hidden group-hover:block bg-gray-800 text-white px-2 py-1 rounded text-sm">
                ${text}
            </div>
        </div>
    `;
}
```

---

**Dernière mise à jour :** Octobre 2025  
**Version :** 1.0.0

