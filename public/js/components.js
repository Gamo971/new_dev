/**
 * Composants réutilisables - UI Components
 * Génère du HTML de manière modulaire et DRY
 */

/**
 * Composant Badge - Affiche un badge coloré (statut, priorité)
 * @param {string} text - Texte du badge
 * @param {string} color - Classe CSS de couleur (ex: 'bg-blue-100 text-blue-800')
 * @returns {string} HTML du badge
 */
function Badge(text, color = 'bg-gray-100 text-gray-800') {
    return `<span class="px-3 py-1 rounded-full text-sm ${color}">${text}</span>`;
}

/**
 * Composant IconBadge - Badge avec icône
 * @param {string} icon - Classe Font Awesome (ex: 'fa-check')
 * @param {string} text - Texte du badge
 * @param {string} color - Classe CSS de couleur
 * @returns {string} HTML du badge avec icône
 */
function IconBadge(icon, text, color = 'bg-gray-100 text-gray-800') {
    return `<span class="px-3 py-1 rounded-full text-sm ${color}">
        <i class="fas ${icon} mr-1"></i>${text}
    </span>`;
}

/**
 * Composant InfoField - Affiche un champ d'information
 * @param {string} label - Label du champ
 * @param {string} value - Valeur du champ
 * @returns {string} HTML du champ
 */
function InfoField(label, value) {
    if (!value) return '';
    return `<div><strong>${label}:</strong> ${value}</div>`;
}

/**
 * Composant ActionButton - Bouton d'action avec icône
 * @param {string} icon - Classe Font Awesome
 * @param {string} action - Fonction onclick
 * @param {string} title - Titre (tooltip)
 * @param {string} color - Classe de couleur
 * @returns {string} HTML du bouton
 */
function ActionButton(icon, action, title, color = 'text-blue-600 hover:text-blue-800') {
    return `<button onclick="${action}" class="${color}" title="${title}">
        <i class="fas ${icon}"></i>
    </button>`;
}

/**
 * Composant ActionButtons - Groupe de boutons d'action (Modifier, Supprimer)
 * @param {string} entity - Type d'entité (mission, tache, client, contact)
 * @param {number} id - ID de l'entité
 * @returns {string} HTML des boutons d'action
 */
function ActionButtons(entity, id) {
    const capitalizedEntity = entity.charAt(0).toUpperCase() + entity.slice(1);
    return `
        <div class="flex gap-2">
            ${ActionButton('fa-edit', `open${capitalizedEntity}Modal(${id})`, 'Modifier', 'text-blue-600 hover:text-blue-800')}
            ${ActionButton('fa-trash', `delete${capitalizedEntity}(${id})`, 'Supprimer', 'text-red-600 hover:text-red-800')}
        </div>
    `;
}

/**
 * Composant Card - Carte pour afficher une entité
 * @param {Object} config - Configuration de la carte
 * @param {string} config.title - Titre de la carte
 * @param {string} config.subtitle - Sous-titre
 * @param {string} config.badges - HTML des badges (statut, priorité)
 * @param {string} config.description - Description (optionnel)
 * @param {string} config.fields - HTML des champs d'information
 * @param {string} config.footer - HTML du footer (date, boutons)
 * @returns {string} HTML de la carte
 */
function Card({ title, subtitle, badges, description = '', fields = '', footer }) {
    return `
        <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">${title}</h3>
                    <p class="text-sm text-gray-600">${subtitle}</p>
                </div>
                <div class="flex gap-2">
                    ${badges}
                </div>
            </div>
            
            ${description ? `<p class="text-gray-700 mb-4">${description}</p>` : ''}
            
            ${fields ? `<div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600 mb-4">
                ${fields}
            </div>` : ''}
            
            <div class="flex justify-between items-center">
                ${footer}
            </div>
        </div>
    `;
}

/**
 * Composant EmptyState - État vide quand il n'y a pas de données
 * @param {string} message - Message à afficher
 * @param {string} icon - Icône Font Awesome (optionnel)
 * @returns {string} HTML de l'état vide
 */
function EmptyState(message, icon = 'fa-inbox') {
    return `
        <div class="text-center text-gray-500 py-8">
            <i class="fas ${icon} text-4xl mb-3 text-gray-400"></i>
            <p>${message}</p>
        </div>
    `;
}

/**
 * Composant FormField - Champ de formulaire
 * @param {Object} config - Configuration du champ
 * @param {string} config.label - Label du champ
 * @param {string} config.id - ID du champ
 * @param {string} config.type - Type (text, email, tel, date, number, textarea, select)
 * @param {boolean} config.required - Champ requis
 * @param {string} config.value - Valeur par défaut
 * @param {Array} config.options - Options pour select [{value, label}]
 * @param {string} config.placeholder - Placeholder
 * @param {number} config.rows - Nombre de lignes (textarea)
 * @returns {string} HTML du champ de formulaire
 */
function FormField({ label, id, type = 'text', required = false, value = '', options = [], placeholder = '', rows = 3 }) {
    const requiredAttr = required ? 'required' : '';
    const labelRequired = required ? ' *' : '';
    const commonClasses = 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent';
    
    let input = '';
    
    switch(type) {
        case 'textarea':
            input = `<textarea id="${id}" name="${id.replace(/^[a-z]+/, '')}" ${requiredAttr} rows="${rows}" class="${commonClasses}">${value}</textarea>`;
            break;
        case 'select':
            input = `<select id="${id}" name="${id.replace(/^[a-z]+/, '')}" ${requiredAttr} class="${commonClasses}">
                ${options.map(opt => `<option value="${opt.value}" ${value === opt.value ? 'selected' : ''}>${opt.label}</option>`).join('')}
            </select>`;
            break;
        default:
            input = `<input type="${type}" id="${id}" name="${id.replace(/^[a-z]+/, '')}" ${requiredAttr} value="${value}" ${placeholder ? `placeholder="${placeholder}"` : ''} class="${commonClasses}">`;
    }
    
    return `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">${label}${labelRequired}</label>
            ${input}
        </div>
    `;
}

/**
 * Composant FormRow - Ligne de formulaire avec plusieurs champs
 * @param {Array<string>} fields - Array de HTML de champs
 * @param {number} columns - Nombre de colonnes (1, 2, 3, 4)
 * @returns {string} HTML de la ligne
 */
function FormRow(fields, columns = 2) {
    return `
        <div class="grid grid-cols-1 md:grid-cols-${columns} gap-4 mb-4">
            ${fields.join('')}
        </div>
    `;
}

/**
 * Composant SearchBar - Barre de recherche
 * @param {string} id - ID du champ
 * @param {string} placeholder - Texte du placeholder
 * @returns {string} HTML de la barre de recherche
 */
function SearchBar(id, placeholder = 'Rechercher...') {
    return `
        <div class="flex-1 min-w-64">
            <input type="text" id="${id}" placeholder="${placeholder}" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent">
        </div>
    `;
}

/**
 * Composant FilterSelect - Menu déroulant de filtrage
 * @param {string} id - ID du select
 * @param {Array} options - Options [{value, label}]
 * @param {string} defaultLabel - Label par défaut
 * @param {string} ringColor - Couleur du focus ring
 * @returns {string} HTML du select
 */
function FilterSelect(id, options, defaultLabel = 'Tous', ringColor = 'blue') {
    return `
        <select id="${id}" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-${ringColor}-500">
            <option value="">${defaultLabel}</option>
            ${options.map(opt => `<option value="${opt.value}">${opt.label}</option>`).join('')}
        </select>
    `;
}

/**
 * Composant StatCard - Carte de statistique
 * @param {Object} config - Configuration
 * @param {string} config.icon - Icône Font Awesome
 * @param {string} config.value - Valeur numérique
 * @param {string} config.label - Label
 * @param {string} config.color - Couleur (blue, green, purple, orange, indigo)
 * @returns {string} HTML de la carte de stat
 */
function StatCard({ icon, value, label, color }) {
    return `
        <div class="bg-${color}-50 p-6 rounded-lg border border-${color}-200">
            <div class="flex items-center">
                <div class="p-2 bg-${color}-600 text-white rounded-lg">
                    <i class="fas ${icon}"></i>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-${color}-900" id="${label.toLowerCase().replace(' ', '')}">${value}</div>
                    <div class="text-${color}-700">${label}</div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Composant Button - Bouton stylisé
 * @param {Object} config - Configuration
 * @param {string} config.text - Texte du bouton
 * @param {string} config.icon - Icône Font Awesome (optionnel)
 * @param {string} config.onclick - Action onclick
 * @param {string} config.color - Couleur (blue, green, purple, orange, red, gray)
 * @param {string} config.type - Type (button, submit)
 * @returns {string} HTML du bouton
 */
function Button({ text, icon = '', onclick = '', color = 'blue', type = 'button' }) {
    return `
        <button type="${type}" ${onclick ? `onclick="${onclick}"` : ''} 
                class="px-4 py-2 bg-${color}-600 text-white rounded-lg hover:bg-${color}-700 transition-colors">
            ${icon ? `<i class="fas ${icon} mr-2"></i>` : ''}${text}
        </button>
    `;
}

/**
 * Composant TabButton - Bouton d'onglet
 * @param {string} id - ID de l'onglet cible
 * @param {string} icon - Icône Font Awesome
 * @param {string} text - Texte du bouton
 * @param {boolean} active - Si l'onglet est actif
 * @returns {string} HTML du bouton d'onglet
 */
function TabButton(id, icon, text, active = false) {
    const activeClass = active ? 'active' : '';
    return `
        <button class="tab px-4 py-2 rounded-lg border border-gray-300 hover:bg-blue-50 transition-colors ${activeClass}" 
                onclick="showTab('${id}')">
            <i class="fas ${icon} mr-2"></i>${text}
        </button>
    `;
}

