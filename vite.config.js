// import {defineConfig} from 'vite';
// import laravel from 'laravel-vite-plugin';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: [
//                 'resources/css/filament/admin/theme.css',
//                 'resources/css/app.css',
//                 'resources/js/app.js',

//             ],
//             refresh: true,
//         }),
//     ],
// });

import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'
export default defineConfig({
  plugins: [
    tailwindcss(),
    // …
  ],
  build: {
    rollupOptions: {
      input: 'resources/js/app.js' // or wherever your main file is
    }
  }
})
