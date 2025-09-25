import { defineConfig } from 'vite';
import vilare from './.vilare/vite';
import copy from './.vilare/vite/copy';

export default defineConfig({
  build: {
    manifest: 'manifest.json',
    assetsDir: '.',
    outDir: 'dist',
    emptyOutDir: true,
    rollupOptions: {
      input: [
        'resources/scripts/alpine.js',
        'resources/scripts/swiper.js',
        'resources/styles/swiper.scss',
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

  css: {
    preprocessorOptions: {
      scss: {
        additionalData: `
          @import "@styles/abstracts/_mixins.scss";
          @import "@styles/abstracts/_modifiers.scss";
        `,
      },
    },
  },

  plugins: [
    vilare(),

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
  ],
});
