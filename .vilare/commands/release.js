import chalk from 'chalk';
import fs from 'fs';
import path from 'path';
import shell from 'shelljs';
import { Command } from 'commander';
import { Client as FTPClient } from 'basic-ftp';
import { NodeSSH as SSHClient } from 'node-ssh';
import { config } from '../utils.js';

class Controller {
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

  async deploy(environment = 'staging', full = false) {
    this.release();

    console.log('✈️ Deploying WordPress Project \r\n');

    const connection = {
      host: environment === 'staging' ? await config('STAGING_HOST') : await config('PRODUCTION_HOST'),
      username: environment === 'staging' ? await config('STAGING_USERNAME') : await config('PRODUCTION_USERNAME'),
      password: environment === 'staging' ? await config('STAGING_PASSWORD') : await config('PRODUCTION_PASSWORD'),
      root: environment === 'staging' ? await config('STAGING_ROOT') : await config('PRODUCTION_ROOT'),
    };

    const ftp = new FTPClient();
    const ssh = new SSHClient();

    try {
      shell.exec(`mkdir -p "${this.output.path}/${connection.root}/wp-content/themes/${this.theme.slug}"`, { silent: true });
      shell.exec(`mv "${this.output.path}/app" "${this.output.path}/${connection.root}/wp-content/themes/${this.theme.slug}"`);
      shell.exec(`mv "${this.output.path}/dist" "${this.output.path}/${connection.root}/wp-content/themes/${this.theme.slug}"`);
      shell.exec(`mv "${this.output.path}/inc" "${this.output.path}/${connection.root}/wp-content/themes/${this.theme.slug}"`);
      shell.exec(`mv "${this.output.path}/resources" "${this.output.path}/${connection.root}/wp-content/themes/${this.theme.slug}"`);
      shell.exec(`mv "${this.output.path}/vendor" "${this.output.path}/${connection.root}/wp-content/themes/${this.theme.slug}"`);

      if (full) {
        shell.exec(`cp -R "${this.wordpress.path}/wp-admin" "${this.output.path}/${connection.root}"`);
        shell.exec(`cp -R "${this.wordpress.path}/wp-includes" "${this.output.path}/${connection.root}"`);
        shell.exec(`cp -R "${this.wordpress.path}/wp-content/plugins" "${this.output.path}/${connection.root}/wp-content/plugins"`);
        shell.exec(`cp -R "${this.wordpress.path}/wp-content/themes/twentytwentyfive" "${this.output.path}/${connection.root}/wp-content/themes"`);
        shell.exec(`cp "${this.wordpress.path}/wp-content/themes/index.php" "${this.output.path}/${connection.root}/wp-content/themes"`);
        shell.exec(`cp "${this.wordpress.path}/wp-content/index.php" "${this.output.path}/${connection.root}/wp-content"`);
        shell.exec(`cp "${this.wordpress.path}/index.php" "${this.output.path}/${connection.root}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-activate.php" "${this.output.path}/${connection.root}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-blog-header.php" "${this.output.path}/${connection.root}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-comments-post.php" "${this.output.path}/${connection.root}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-config-sample.php" "${this.output.path}/${connection.root}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-cron.php" "${this.output.path}/${connection.root}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-links-opml.php" "${this.output.path}/${connection.root}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-load.php" "${this.output.path}/${connection.root}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-login.php" "${this.output.path}/${connection.root}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-mail.php" "${this.output.path}/${connection.root}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-settings.php" "${this.output.path}/${connection.root}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-signup.php" "${this.output.path}/${connection.root}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-trackback.php" "${this.output.path}/${connection.root}"`);
        shell.exec(`cp "${this.wordpress.path}/xmlrpc.php" "${this.output.path}/${connection.root}"`);
      }

      shell.exec(`find "${this.output.path}" -type f -name ".gitkeep" -delete`);
      shell.exec(`find "${this.output.path}" -type f -name ".DS_Store" -delete`);
      shell.exec(`cd "${this.output.path}" && zip -r "www.zip" .`, { silent: true });
      shell.exec(`rm -rf "${this.output.path}/www"`);

      await ftp.access({
        host: connection.host,
        user: connection.username,
        password: connection.password,
        secure: false,
      });

      await ssh.connect({
        host: 'ssh.iq.pl',
        username: connection.username,
        password: connection.password,
        tryKeyboard: true,
      });

      const sshshell = await ssh.requestShell();

      ssh.execCommand = async(command) => {
        return new Promise((resolve, reject) => {
          let output = '';

          const onData = (data) => {
            output += data.toString();
          };

          const onError = (err) => {
            sshshell.off('data', onData);
            sshshell.off('error', onError);
            reject(err);
          };

          sshshell.on('data', onData);
          sshshell.on('error', onError);

          sshshell.write(`${command}\n`);

          setTimeout(() => {
            sshshell.off('data', onData);
            sshshell.off('error', onError);
            resolve(output);
          }, 500);
        });
      };

      console.log(`🧹 Clearing: /wp-content/themes/${this.theme.slug}`);
      await ftp.ensureDir(`/${connection.root}/wp-content/themes/${this.theme.slug}`);
      await ftp.cd('/');
      await ssh.execCommand(`rm -rf ${connection.root}/wp-content/themes/${this.theme.slug}`);

      console.log('🧹 Clearing: /wp-content/cache');
      await ftp.ensureDir(`/${connection.root}/wp-content/cache`);
      await ftp.cd('/');
      await ssh.execCommand(`rm -rf ${connection.root}/wp-content/cache`);

      console.log(`🛫 Transfering: /wp-content/themes/${this.theme.slug}`);
      await ftp.uploadFrom(`${this.output.path}/www.zip`, 'www.zip');
      await ftp.cd('/');

      console.log(`🛬 Unpacking: /wp-content/themes/${this.theme.slug}`);
      await ssh.execCommand('unzip -u www.zip');

      console.log();
    } catch (err) {
      console.log(err);
    } finally {
      ftp.close();
      ssh.dispose();
    }
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

  program
    .command('deploy')
    .description('deploy release package')
    .option('-e, --env <env>', 'staging | production', 'staging')
    .option('-f, --full <full>', 'full deploy', false)
    .action((options) => {
      try {
        controller.deploy(options.env, options.full);
      } catch (error) {
        program.error(error);
      }
    });

  return program;
};
