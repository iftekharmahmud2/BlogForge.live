/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    "./*.php",
    "./views/**/*.php",
    "./partials/**/*.php",
    "./includes/**/*.php",
    "./admin/**/*.php",
    "./assets/js/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#fff7ed',
          100: '#ffedd5',
          200: '#fed7aa',
          300: '#fdba74',
          400: '#fb923c',
          500: '#ff6b13', // BlogForge main orange
          600: '#ea580c',
          700: '#c2410c',
          800: '#9a3412',
          900: '#7c2d12',
        },
        dark: {
          DEFAULT: '#0e1218',
          card: '#161a21',
          border: '#232936',
        },
        paper: '#fffdfa',
        soft: '#f7f4ef',
        cream: '#fbfaf8',
        ink: '#11151b',
        muted: '#6f7278',
        line: '#e9e5df',
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
      },
      boxShadow: {
        card: '0 16px 50px rgba(17,21,27,.08)',
        soft: '0 4px 20px rgba(17,21,27,.05)',
        glow: '0 8px 24px rgba(255,107,19,.24)',
      }
    },
  },
  plugins: [],
}

