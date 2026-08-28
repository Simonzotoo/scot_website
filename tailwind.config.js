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
      // Matches the parent WIUC site (wiuc-ghana.edu.gh) exactly: DM Sans
      // for headings, Lato for body — confirmed from its own stylesheet's
      // font-face/font-family rules, not guessed.
      fontFamily: {
        heading: ['"DM Sans"', 'sans-serif'],
        body: ['Lato', 'sans-serif'],
      },
      // Matches WIUC's own type scale (from its theme's --theme-font-size
      // rules): body text 16px, small labels 15px, and headings stepping
      // 20/25/30/35/40px (their h5/h4/h3/h2/h1).
      fontSize: {
        xs:   '0.9375rem', // 15px — small labels/footer, matches WIUC's small-label text
        sm:   '1rem',      // 16px — body copy, matches WIUC's paragraph default
        base: '1rem',      // 16px — same as WIUC, where body text and h6 coincide
        lg:   '1.25rem',   // 20px — matches WIUC h5
        xl:   '1.5625rem', // 25px — matches WIUC h4
        '2xl': '1.875rem', // 30px — matches WIUC h3
        '3xl': '2.1875rem',// 35px — matches WIUC h2
        '4xl': '2.5rem',   // 40px — matches WIUC h1
      },
    },
  },
  plugins: [],
};
