import crypto from 'crypto';
import fs from 'fs';
import path from 'path';
import { globSync } from 'glob';

class Plugin {
  constructor() {
    this.targets = [];
    this.entries = [];

    this.dest = '';
    this.rename = '';
    this.manifest = '';
  }

  init(config) {
    this.dest = config.dest;
    this.rename = config.rename;

    if (config.manifest) {
      this.manifest = `${this.dest}/${config.manifest}`;
    }

    if (config.targets) {
      this.targets = config.targets
        .filter(item => item.src)
        .map((item) => {
          return {
            src: item.src,
            rename: item.rename || this.rename,
            manifest: item.manifest !== false,
            files: [],
          };
        });
    }
  }

  resolve() {
    for (const target of this.targets) {
      for (const file of globSync([target.src])) {
        const info = path.parse(file);
        const name = target.rename
          .replace('[name]', info.name)
          .replace('[hash]', crypto.randomBytes(4).toString('hex'))
          .replace('[ext]', info.ext.substring(1));

        target.files.push({
          src: file,
          dest: `${this.dest}/${name}`,
          name,
        });
      }
    }
  }

  copy() {
    for (const target of this.targets) {
      for (const file of target.files) {
        try {
          fs.copyFileSync(file.src, file.dest);
          if (target.manifest) {
            this.entries.push({
              source: file.src,
              file: file.name,
            });
          }
        } catch (error) {
          console.error(error);
        }
      }
    }
  }

  write() {
    if (!this.manifest || !this.entries.length) {
      return;
    }

    const manifest = JSON.parse(fs.readFileSync(this.manifest, 'utf-8'));

    for (const entry of this.entries) {
      if (!manifest[entry.source]) {
        manifest[entry.source] = entry;
      }
    }

    fs.writeFileSync(this.manifest, JSON.stringify(manifest, null, 2));
  }
}

export default function(params) {
  const plugin = new Plugin();

  return {
    name: 'vite:copy',

    config(config) {
      const { build } = config;
      plugin.init({
        dest: build.outDir || 'dist',
        rename:
          build.rollupOptions.output.assetFileNames || '[name]-[hash].[ext]',
        targets: params.targets || [],
        manifest:
          typeof build.manifest === 'string'
            ? build.manifest
            : build.manifest === true
              ? '.vite/manifest.json'
              : '',
      });
    },

    buildStart() {
      plugin.resolve();
    },

    writeBundle() {
      plugin.copy();
    },

    closeBundle() {
      plugin.write();
    },
  };
}
