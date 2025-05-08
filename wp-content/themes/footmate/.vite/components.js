import fs from 'fs';

class Plugin {
  constructor(params) {
    this.path = params.path;
    this.components = fs.readdirSync(params.path);
  }

  assets() {
    const assets = [];

    for (const component of this.components) {
      if (fs.existsSync(`${this.path}/${component}/script.js`)) {
        assets.push(`${this.path}/${component}/script.js`);
      }

      if (fs.existsSync(`${this.path}/${component}/style.scss`)) {
        assets.push(`${this.path}/${component}/style.scss`);
      }
    }

    return assets;
  }
}

export default function(params) {
  const plugin = new Plugin(params);

  return {
    name: 'vite:components',

    config(config) {
      plugin.assets().forEach((input) => {
        config.build.rollupOptions.input.push(input);
      });
    },
  };
}
