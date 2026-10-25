import { defineConfig } from 'vite';
import aurelia from '@aurelia/vite-plugin';
import tailwindcss from '@tailwindcss/postcss';
import { resolve } from 'path';
import { readFileSync } from 'fs';
import { blackcubeManifestPlugin } from './vite-manifest-plugin.mts';

// Load configuration
const config = JSON.parse(readFileSync('./assets-blackcube.json', 'utf-8'));

// Paths
const sourceDir = resolve(process.cwd(), config.sourceDir);
const outputDir = resolve(process.cwd(), `${config.outputBaseDir}/dist-vite`);

// Build entries
const entries: Record<string, string> = {};
for (const [name, entry] of Object.entries(config.entry)) {
    entries[name] = resolve(sourceDir, entry as string);
}

export default defineConfig({
    root: sourceDir,
    base: './',
    cacheDir: './.vite-cache',
    define: {
        APP_MODE: JSON.stringify(process.env.NODE_ENV || 'development'),
    },
    plugins: [
        // Add your plugins here (aurelia, tailwindcss, etc.)
        // The decorator filter is resolved from `root` (the sources), not from the project directory.
        aurelia({
            standardDecoratorInclude: '**/*.ts',
        }),
        blackcubeManifestPlugin({
            catalogFile: config.catalog,
        }),
    ],
    css: {
        // Vite inlines @import before Tailwind; Tailwind's optimize pass re-parses the whole CSS
        // as main.css and the url() of imported stylesheets no longer resolve. Vite minifies the CSS itself.
        postcss: {
            plugins: [
                tailwindcss({
                    optimize: false,
                }),
            ],
        },
    },
    build: {
        outDir: outputDir,
        emptyOutDir: true,
        target: 'esnext',
        manifest: false, // We use our own manifest plugin
        rollupOptions: {
            input: entries,
            output: {
                entryFileNames: `${config.subDirectories.js}/[name].[hash].js`,
                chunkFileNames: `${config.subDirectories.js}/[name].[hash].js`,
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name?.endsWith('.css')) {
                        return `${config.subDirectories.css}/[name].[hash][extname]`;
                    }
                    return 'assets/[name].[hash][extname]';
                },
            },
        },
    },
    server: {
        port: 9000,
    },
});
