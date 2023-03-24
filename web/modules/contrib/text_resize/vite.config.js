import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    emptyOutDir: false,
    outDir: 'js',
    rollupOptions: {
      input: 'js/src/text_resize.js',
      output: {
        entryFileNames: '[name].js',
      },
    },
  },
});
