import fs from 'fs';
import path from 'path';
import { merge } from 'lodash-es';
import { config } from '../utils.js';

class Plugin {
  async components() {
    const assets = [];

    for (const type of ['components', 'blocks', 'templates']) {
      for (const item of fs.readdirSync(`resources/${type}`)) {
        if (fs.existsSync(`resources/${type}/${item}/script.js`)) {
          assets.push(`resources/${type}/${item}/script.js`);
        }

        if (fs.existsSync(`resources/${type}/${item}/style.scss`)) {
          assets.push(`resources/${type}/${item}/style.scss`);
        }
      }
    }

    return assets;
  }

  async domain() {
    return await config('DOMAIN_LOCAL');
  }
}

export default function() {
  const ROOT = path.resolve(process.cwd(), '../../../');
  const BASE = process.cwd().replace(ROOT, '');

  const plugin = new Plugin();

  return {
    name: 'vilare',

    async config(config) {
      const setup = {
        base: process.env.NODE_ENV === 'production' ? `${BASE}/dist/` : BASE,

        build: {
          rollupOptions: {
            input: [
              ...config.build.rollupOptions.input,
              ...await plugin.components(),
            ],
          },
        },

        css: {
          preprocessorOptions: {
            scss: {
              silenceDeprecations: ['global-builtin', 'import', 'legacy-js-api'],
            },
          },
        },

        server: {
          cors: {
            origin: process.env.NODE_ENV !== 'production' ? await plugin.domain() : '',
          },
        },

        resolve: {
          alias: {
            '@': `${process.cwd()}`,
            '@styles': `${process.cwd()}/resources/styles`,
            '@scripts': `${process.cwd()}/resources/scripts`,
          },
        },
      };

      Object.assign(config, merge(config, setup));
    },

    handleHotUpdate({ file, server }) {
      if (file.endsWith('.php')) {
        server.ws.send({ type: 'full-reload' });
      }
    },
  };
}
