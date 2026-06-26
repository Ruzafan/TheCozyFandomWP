/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './*.php',
    './template-parts/**/*.php',
    './woocommerce/**/*.php',
    './inc/**/*.php',
    './legal-pages/**/*.html',
    './assets/js/**/*.js',
  ],
  safelist: [
    'hidden', 'translate-x-full',
    'bg-white', 'bg-cozy-sand', 'bg-cozy-mintLight', 'bg-white',
    'text-cozy-mint', 'text-cozy-accent',
    'bg-cozy-mint/10', 'bg-cozy-accent/10',
  ],
  important: true,
  theme: {
    extend: {
      colors: {
        cozy: {
          cream:    '#FCF9F5',
          sand:     '#F2E6D5',
          coffee:   '#3A3128',
          mint:     '#88C4B5',
          mintDark: '#72b0a2',
          mintLight:'#EAF6F3',
          accent:   '#D4A373',
        }
      },
      fontFamily: {
        sans:  ['"Plus Jakarta Sans"', 'sans-serif'],
        serif: ['"Playfair Display"', 'serif'],
      }
    }
  },
  plugins: [],
}
