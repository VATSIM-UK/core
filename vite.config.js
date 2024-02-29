import {defineConfig} from 'vite';
import laravel, {refreshPaths} from 'laravel-vite-plugin'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/assets/less/admin.less',
                'resources/assets/sass/app.scss',
                'resources/assets/css/tailwind.css',
                'resources/assets/js/app.js',
                'resources/assets/sass/home.scss',
                'resources/assets/js/home.js',
                'resources/assets/js/snow.js',
                'resources/assets/js/top-notification.js'
            ],
            refresh: [
                ...refreshPaths,
            ],
        }),
        {
            name: 'blade',
            handleHotUpdate({file, server}) {
                if (file.endsWith('.blade.php')) {
                    server.ws.send({
                        type: 'full-reload',
                        path: '*',
                    });
                }
            },
        },
    ]
});
