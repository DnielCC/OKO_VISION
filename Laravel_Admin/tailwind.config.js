/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#050A18',
        secondary: '#0D1B35',
        accent: {
          cyan: '#00F2FF',
        },
        success: '#00FF87',
        danger: '#FF3131',
        warning: '#FFA500',
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
