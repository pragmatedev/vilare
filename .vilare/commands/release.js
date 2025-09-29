import chalk from 'chalk';
import fs from 'fs';
import path from 'path';
import shell from 'shelljs';
import { Command } from 'commander';

export class Controller {
  constructor() {
    this.wordpress = {
      path: path.resolve(process.cwd(), '../../..'),
    };

    this.theme = {
      path: path.join(process.cwd()),
      slug: path.basename(path.join(process.cwd())),
    };

    this.templates = {
      path: path.join(process.cwd(), '.vilare/templates'),
    };

    this.output = {
      path: path.join(process.cwd(), '.output'),
    };
  }

  release(zip = false) {
    console.log('✈️ Creating Release Package \r\n');

    /**
     * clean output
     */
    if (fs.existsSync(`${this.output.path}`)) {
      fs.rmSync(`${this.output.path}`, { recursive: true });
    }

    fs.mkdirSync(`${this.output.path}`);

    /**
     * copy vendors
     */
    shell.exec('composer install --no-dev --quiet', { silent: true });
    shell.exec(`cp -R "${this.theme.path}/vendor" "${this.output.path}/vendor"`);
    shell.exec('composer install --quiet', { silent: true });

    /**
     * build theme
     */
    shell.exec('yarn build', { silent: true });

    /**
     * copy theme files
     */
    shell.exec(`cp -R "${this.theme.path}/dist" "${this.output.path}/dist"`);
    shell.exec(`cp -R "${this.theme.path}/app" "${this.output.path}/app"`);
    shell.exec(`cp -R "${this.theme.path}/inc" "${this.output.path}/inc"`);
    shell.exec(`cp -R "${this.theme.path}/resources" "${this.output.path}/resources"`);

    shell.exec(`rm -rf "${this.output.path}/resources/scripts"`);
    shell.exec(`rm -rf "${this.output.path}/resources/styles"`);
    shell.exec(`rm -rf "${this.output.path}/resources/fonts"`);
    shell.exec(`rm -rf "${this.output.path}/resources/images"`);

    shell.exec(`find "${this.output.path}/resources" -type f -name "*.js" -delete`);
    shell.exec(`find "${this.output.path}/resources" -type f -name "*.scss" -delete`);
    shell.exec(`find "${this.output.path}/resources" -type f -name ".gitkeep" -delete`);
    shell.exec(`find "${this.output.path}/resources" -type f -name ".DS_Store" -delete`);

    if (zip) {
      shell.exec(`cd "${this.output.path}" && zip -r "${this.theme.slug}.zip" .`, { silent: true });
      shell.exec(`rm -rf "${this.output.path}/app"`);
      shell.exec(`rm -rf "${this.output.path}/dist"`);
      shell.exec(`rm -rf "${this.output.path}/inc"`);
      shell.exec(`rm -rf "${this.output.path}/resources"`);
      shell.exec(`rm -rf "${this.output.path}/vendor"`);
    }

    console.log(`Release created in ${chalk.green(this.output.path)} directory`);
    console.log();
  }
}

export const release = () => {
  const program = new Command('release');
  const controller = new Controller();

  program
    .description('create release package')
    .option('-z, --zip', 'create zip archive')
    .action((options) => {
      try {
        controller.release(options.zip);
      } catch (error) {
        program.error(error);
      }
    });

  return program;
};
