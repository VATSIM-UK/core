import preset from './vendor/filament/support/tailwind.config.preset';

const defaultTheme = require('tailwindcss/defaultTheme');
const colors = require('tailwindcss/colors');

module.exports = {
    presets: [preset],
    darkMode: 'class',
    mode: 'jit',
    theme: {
        colors: {
            // Core
            brand: '#25ADE3',
            transparent: 'transparent',
            black: colors.black,
            white: colors.white,

            // Filament
            current: 'currentColor',
            custom: colors.sky,
            primary: colors.sky,
            secondary: colors.slate,
            positive: colors.green,
            success: colors.green,
            warning: colors.amber,
            negative: colors.red,
            danger: colors.red,
            info: colors.teal,

            // Named
            blue: colors.sky,
            gray: colors.slate,
            neutral: colors.neutral,
            green: colors.green,
            red: colors.red,
            amber: colors.amber,
            teal: colors.teal,
            orange: colors.orange,
            yellow: colors.yellow,
            lime: colors.lime,
            emerald: colors.emerald,
            cyan: colors.cyan,
            indigo: colors.indigo,
            violet: colors.violet,
            purple: colors.purple,
            fuchsia: colors.fuchsia,
            pink: colors.pink,
            rose: colors.rose,
        }
    },
    content: [
        './app/**/*.php',
        './resources/**/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/Filament/**/*.php',
        './app/Livewire/**/*.php',
        './vendor/filament/**/*.blade.php',
        './vendor/wire-elements/modal/resources/views/*.blade.php',
        './storage/framework/views/*.php'
    ],
    plugins: [require("@tailwindcss/forms")],
};
