import path from 'path';
import { defineConfig } from 'vite';
import copy from './.vite/copy';
import blocks from './.vite/blocks';
import components from './.vite/components';
import templates from './.vite/templates';

const ROOT = path.resolve('../../../');
const BASE = __dirname.replace(ROOT, '');

export default defineConfig({
  base: process.env.NODE_ENV === 'production' ? `${BASE}/dist/` : BASE,

  build: {
    manifest: 'manifest.json',
    assetsDir: '.',
    outDir: 'dist',
    emptyOutDir: true,
    rollupOptions: {
      input: [
        'resources/scripts/scripts.js',
        'resources/styles/styles.scss',
        'resources/styles/admin.scss',
      ],
      output: {
        entryFileNames: '[hash].js',
        assetFileNames: '[hash].[ext]',
        chunkFileNames: '[hash].js',
      },
    },
  },

  /**
   * @todo https://sass-lang.com/documentation/breaking-changes/import/
   */
  css: {
    preprocessorOptions: {
      scss: {
        additionalData: `
          @import "@styles/abstracts/_breakpoints.scss";
          @import "@styles/abstracts/_mixins.scss";
        `,
        silenceDeprecations: ['global-builtin', 'import', 'legacy-js-api'],
      },
    },
  },

  server: {
    cors: {
      origin: 'https://fm.tentyp.test',
    },
  },

  plugins: [
    copy({
      targets: [
        {
          src: 'resources/images/**/*.{mp4,png,jpg,jpeg,svg,webp,avif}',
        },
        {
          src: 'resources/videos/**/*.{mp4,png,jpg,jpeg,svg,webp,avif}',
        },
      ],
    }),

    blocks({
      path: 'resources/blocks',
    }),

    components({
      path: 'resources/components',
    }),

    templates({
      path: 'resources/templates',
    }),

    {
      name: 'php',
      handleHotUpdate({ file, server }) {
        if (file.endsWith('.php')) {
          server.ws.send({ type: 'full-reload' });
        }
      },
    },
  ],

  resolve: {
    alias: {
      '@': path.resolve(__dirname),
      '@scripts': path.resolve(__dirname, './resources/scripts'),
      '@styles': path.resolve(__dirname, './resources/styles'),
    },
  },

  logLevel: 'warn',
});
