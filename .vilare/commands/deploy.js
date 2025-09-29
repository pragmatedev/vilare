import path from 'path';
import shell from 'shelljs';
import { Command } from 'commander';
import { Client as FTPClient } from 'basic-ftp';
import { NodeSSH as SSHClient } from 'node-ssh';
import { config } from '../utils.js';
import { Controller as Release } from './release.js';

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
    const release = new Release();

    await release.release();

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
}

export const deploy = () => {
  const program = new Command('deploy');
  const controller = new Controller();

  program
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
