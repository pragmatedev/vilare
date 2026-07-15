import chalk from 'chalk';
import inquirer from 'inquirer';
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

  async deploy(env, mode) {
    const ssh = new SSHClient();
    const release = new Release();

    const params = await this.params(env, mode);

    if (!await this.confirm(params)) {
      return console.log('❌ Deployment Cancelled\r\n');
    }

    console.clear();

    const connection = {
      host: params.env === 'production' ? await config('PRODUCTION_HOST') : await config('STAGING_HOST'),
      port: params.env === 'production' ? await config('PRODUCTION_PORT') : await config('STAGING_PORT'),
      username: params.env === 'production' ? await config('PRODUCTION_USERNAME') : await config('STAGING_USERNAME'),
      password: params.env === 'production' ? await config('PRODUCTION_PASSWORD') : await config('STAGING_PASSWORD'),
      root: params.env === 'production' ? await config('PRODUCTION_ROOT') : await config('STAGING_ROOT'),
    };

    await release.release();

    console.log('🚀 Deploying Release Package \r\n');

    try {
      console.log(`${chalk.yellow('[1/5]')} Preparing release package.`);

      shell.exec(`mkdir -p "${this.output.path}/wp-content/themes/${this.theme.slug}"`, { silent: true });
      shell.exec(`mv "${this.output.path}/app" "${this.output.path}/wp-content/themes/${this.theme.slug}"`);
      shell.exec(`mv "${this.output.path}/dist" "${this.output.path}/wp-content/themes/${this.theme.slug}"`);
      shell.exec(`mv "${this.output.path}/inc" "${this.output.path}/wp-content/themes/${this.theme.slug}"`);
      shell.exec(`mv "${this.output.path}/resources" "${this.output.path}/wp-content/themes/${this.theme.slug}"`);
      shell.exec(`mv "${this.output.path}/vendor" "${this.output.path}/wp-content/themes/${this.theme.slug}"`);

      if (params.mode === 'full') {
        shell.exec(`cp -R "${this.wordpress.path}/wp-admin" "${this.output.path}"`);
        shell.exec(`cp -R "${this.wordpress.path}/wp-includes" "${this.output.path}"`);
        shell.exec(`cp -R "${this.wordpress.path}/wp-content/plugins" "${this.output.path}/wp-content/plugins"`);
        shell.exec(`cp -R "${this.wordpress.path}/wp-content/themes/twentytwentyfive" "${this.output.path}/wp-content/themes"`);
        shell.exec(`cp "${this.wordpress.path}/wp-content/themes/index.php" "${this.output.path}/wp-content/themes"`);
        shell.exec(`cp "${this.wordpress.path}/wp-content/index.php" "${this.output.path}/wp-content"`);
        shell.exec(`cp "${this.wordpress.path}/index.php" "${this.output.path}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-activate.php" "${this.output.path}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-blog-header.php" "${this.output.path}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-comments-post.php" "${this.output.path}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-config-sample.php" "${this.output.path}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-cron.php" "${this.output.path}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-links-opml.php" "${this.output.path}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-load.php" "${this.output.path}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-login.php" "${this.output.path}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-mail.php" "${this.output.path}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-settings.php" "${this.output.path}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-signup.php" "${this.output.path}"`);
        shell.exec(`cp "${this.wordpress.path}/wp-trackback.php" "${this.output.path}"`);
        shell.exec(`cp "${this.wordpress.path}/xmlrpc.php" "${this.output.path}"`);
      }

      shell.exec(`find "${this.output.path}" -type f -name ".gitkeep" -delete`);
      shell.exec(`find "${this.output.path}" -type f -name ".DS_Store" -delete`);
      shell.exec(`cd "${this.output.path}" && zip -r "release.zip" .`, { silent: true });
      shell.exec(`find "${this.output.path}" -mindepth 1 -maxdepth 1 ! -name 'release.zip' -exec rm -rf {} +`);

      console.log(`${chalk.yellow('[2/5]')} Connecting to the server.`);

      await ssh.connect({
        host: connection.host,
        port: connection.port,
        username: connection.username,
        password: connection.password,
        tryKeyboard: true,
      });

      console.log(`${chalk.yellow('[3/5]')} Transfering release package.`);
      await ssh.putFile(`${this.output.path}/release.zip`, `${connection.root}/release.zip`);

      console.log(`${chalk.yellow('[4/5]')} Unpacking release package.`);
      await ssh.execCommand(`unzip -o -u ${connection.root}/release.zip -d ${connection.root}`);
      await ssh.execCommand(`rm ${connection.root}/release.zip`);

      console.log(`${chalk.yellow('[5/5]')} Clearing cache directory.`);
      await ssh.execCommand(`rm -rf ${connection.root}/wp-content/cache`);

      console.log();
    } catch (err) {
      console.log(err);
    } finally {
      ssh.dispose();
    }
  }

  async params(env, mode) {
    const inputs = await inquirer.prompt([
      {
        type: 'select',
        name: 'env',
        message: 'Environment:',
        choices: ['staging', 'production'],
        when: typeof env === 'undefined' || !['staging', 'production'].includes(env),
      },
      {
        type: 'select',
        name: 'mode',
        message: 'Mode:',
        choices: ['theme', 'full'],
        when: typeof mode === 'undefined' || !['theme', 'full'].includes(mode),
      },
    ]);

    return {
      env: env || inputs.env || 'staging',
      mode: mode || inputs.mode || 'theme',
    };
  }

  async confirm(params) {
    if (params.env === 'production') {
      const answers = await inquirer.prompt([
        {
          type: 'confirm',
          name: 'confirm',
          message: (answers) => `Are you sure you want to deploy to ${chalk.yellow(params.env)}?`,
          default: (answers) => false,
        },
      ]);

      if (answers.confirm === false) {
        return false;
      }
    }

    return true;
  }

  /**
   * [W.I.P] Deploy project to iq.pl servers
   */
  async deployIQ(env, mode) {
    const release = new Release();

    await release.release();

    console.log('✈️ Deploying WordPress Project \r\n');

    const connection = {
      host: env === 'production' ? await config('PRODUCTION_HOST') : await config('STAGING_HOST'),
      port: env === 'production' ? await config('PRODUCTION_PORT') : await config('STAGING_PORT'),
      username: env === 'production' ? await config('PRODUCTION_USERNAME') : await config('STAGING_USERNAME'),
      password: env === 'production' ? await config('PRODUCTION_PASSWORD') : await config('STAGING_PASSWORD'),
      root: env === 'production' ? await config('PRODUCTION_ROOT') : await config('STAGING_ROOT'),
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

      if (mode === 'full') {
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
        port: connection.port,
        secure: false,
      });

      await ssh.connect({
        host: 'ssh.iq.pl',
        port: connection.port,
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
    .option('-e, --env <env>', 'staging | production')
    .option('-m, --mode <mode>', 'theme | full')
    .action(async(options) => {
      try {
        await controller.deploy(options.env, options.mode);
      } catch (error) {
        program.error(error);
      }
    });

  return program;
};
