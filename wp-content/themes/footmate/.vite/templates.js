import fs from 'fs';

class Plugin {
  constructor(params) {
    this.path = params.path;
    this.templates = fs.readdirSync(params.path);
  }

  assets() {
    const assets = [];

    for (const template of this.templates) {
      if (fs.existsSync(`${this.path}/${template}/script.js`)) {
        assets.push(`${this.path}/${template}/script.js`);
      }

      if (fs.existsSync(`${this.path}/${template}/style.scss`)) {
        assets.push(`${this.path}/${template}/style.scss`);
      }
    }

    return assets;
  }
}

export default function(params) {
  const plugin = new Plugin(params);

  return {
    name: 'vite:templates',

    config(config) {
      plugin.assets().forEach((input) => {
        config.build.rollupOptions.input.push(input);
      });
    },
  };
}
