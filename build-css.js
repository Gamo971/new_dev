#!/usr/bin/env node

/**
 * Script de build CSS personnalisé pour le projet
 * Alternative à TailwindCSS CLI quand Node.js n'est pas disponible
 */

const fs = require('fs');
const path = require('path');

console.log('🎨 Build CSS personnalisé pour le projet...');

// Vérifier si TailwindCSS CLI est disponible
const { execSync } = require('child_process');

try {
  // Essayer d'utiliser TailwindCSS CLI
  console.log('📦 Tentative d\'utilisation de TailwindCSS CLI...');
  
  const inputFile = path.join(__dirname, 'public/css/input.css');
  const outputFile = path.join(__dirname, 'public/css/tailwind-compiled.css');
  
  // Créer le fichier d'entrée s'il n'existe pas
  if (!fs.existsSync(inputFile)) {
    fs.writeFileSync(inputFile, `
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Styles personnalisés */
.custom-shadow {
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}

.kanban-ghost {
  opacity: 0.4;
}

.kanban-drag {
  transform: rotate(5deg);
}
    `);
  }
  
  // Compiler avec TailwindCSS
  execSync(`npx tailwindcss -i ${inputFile} -o ${outputFile} --minify`, { 
    stdio: 'inherit',
    cwd: __dirname 
  });
  
  console.log('✅ TailwindCSS compilé avec succès !');
  console.log(`📁 Fichier généré : ${outputFile}`);
  
} catch (error) {
  console.log('⚠️  TailwindCSS CLI non disponible, utilisation du CSS personnalisé...');
  console.log('💡 Pour compiler TailwindCSS, installez Node.js et exécutez :');
  console.log('   npm install -D tailwindcss');
  console.log('   npx tailwindcss -i public/css/input.css -o public/css/tailwind-compiled.css --minify');
  
  // Vérifier que notre CSS personnalisé existe
  const customCssPath = path.join(__dirname, 'public/css/tailwind-custom.css');
  if (fs.existsSync(customCssPath)) {
    console.log('✅ CSS personnalisé disponible : tailwind-custom.css');
  } else {
    console.log('❌ CSS personnalisé manquant !');
    process.exit(1);
  }
}

console.log('🎉 Build terminé !');
