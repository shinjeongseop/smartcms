/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./home.html', './admin.html', './app.js'],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#ecfdf3',
          100: '#d1fadf',
          500: '#03c75a',
          600: '#02a74b',
          700: '#027a39'
        }
      },
      boxShadow: {
        panel: '0 1px 3px rgba(15, 23, 42, 0.08)'
      }
    }
  },
  plugins: []
};
