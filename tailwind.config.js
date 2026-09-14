import colors from 'tailwindcss/colors';

// Shared brand colours referenced by multiple tokens below.
const uknavy = '#17375e';

export default {
    darkMode: 'class',
    theme: {
        colors: {
            // Nav (VATSIM UK branding)
            'nav-bg': uknavy,
            'nav-accent': '#00b0f0',
            'nav-secondary': '#0f131a',
            'nav-hover-bg': '#f5f5f5',
            'nav-hover-text': '#262626',
            // Core
            brand: '#25ADE3',
            uknavy: uknavy,
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
