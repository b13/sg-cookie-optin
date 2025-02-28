import {resolve} from 'path'
import {defineConfig} from 'vite';

export default defineConfig({
    build: {
        outDir: 'Resources/Public/JavaScript/Backend/dist',
        minify: false,
        target: 'modules', // or 'modules' for modern browsers
        lib: {
            // Could also be a dictionary or array of multiple entry points
            entry: resolve(__dirname, 'statistics.js'),
            name: 'Statistics',
            // the proper extensions will be added
            fileName: (format) => `statistics.${format}.js`,
            formats: ['es']
        },
        rollupOptions: {
            input: 'Resources/Public/JavaScript/Backend/Statistics.js',
        },

    },
});
