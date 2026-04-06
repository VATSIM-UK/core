import colors from 'tailwindcss/colors';
import preset from './vendor/filament/support/tailwind.config.preset.js';

export default {
    presets: [preset],
    darkMode: 'class',
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
        },
    },
};
