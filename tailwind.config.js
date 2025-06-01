/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/Http/Livewire/**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#476b6b', // Muted teal
          light: '#668585',
          dark: '#2a4040',
        },
        secondary: {
          DEFAULT: '#8B4513', // Saddle brown (earthy)
          light: '#A0522D',
          dark: '#6B3606',
        },
        accent: {
          DEFAULT: '#A52A2A', // Deep red
          light: '#C03E3E',
          dark: '#7A1F1F',
        },
        neutral: {
          DEFAULT: '#F5F5DC', // Beige/cream
          light: '#FFFFF0',
          dark: '#E6E6C8',
        },
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        serif: ['Merriweather', 'serif'],
      },
    },
  },
  plugins: [],
} 