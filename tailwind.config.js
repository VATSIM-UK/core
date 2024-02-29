/** @type {import('tailwindcss').Config} */
import preset from './vendor/filament/support/tailwind.config.preset';
const colors = require('tailwindcss/colors');

module.exports = {
    content: [
        './resources/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                brand: '#25ADE3'
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}
