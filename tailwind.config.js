/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./public/**/*.{html,js,php}",
    "./src/**/*.php"
  ],
  theme: {
    extend: {
      colors: {
        // Couleurs personnalisées pour le projet
        'cabinet-blue': '#3b82f6',
        'cabinet-green': '#10b981',
        'cabinet-purple': '#8b5cf6',
        'cabinet-orange': '#f97316',
      },
      fontFamily: {
        'sans': ['ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'Noto Sans', 'sans-serif'],
      },
      spacing: {
        '18': '4.5rem',
        '88': '22rem',
      },
      animation: {
        'fade-in': 'fadeIn 0.5s ease-in-out',
        'slide-down': 'slideDown 0.3s ease-out',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideDown: {
          '0%': { transform: 'translateY(-10px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
      },
    },
  },
  plugins: [
    // Plugins TailwindCSS optionnels
    // require('@tailwindcss/forms'),
    // require('@tailwindcss/typography'),
  ],
  // Configuration pour la production
  future: {
    hoverOnlyWhenSupported: true,
  },
  // Optimisations
  experimental: {
    optimizeUniversalDefaults: true,
  },
}
