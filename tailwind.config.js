/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './**/*.php',
    '!./node_modules/**',
    '!./uploads/**',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        scotsaBlue: '#0A1F44',
        scotsaLight: '#0d2a5c',
        scotsaGold: '#D4AF37',
        ink: '#0F172A',
      },
      fontFamily: {
        heading: ['Sora', 'sans-serif'],
        body: ['"Plus Jakarta Sans"', 'sans-serif'],
      },
    },
  },
  plugins: [],
};
