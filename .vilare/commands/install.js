import chalk from 'chalk';
import fs from 'fs';
import path from 'path';
import inquirer from 'inquirer';
import shell from 'shelljs';
import { Command } from 'commander';
import { config } from '../utils.js';
import { Controller as Database } from './database.js';

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

  async prepare() {
    console.log('\r\n🖌️ Setting Up WordPress Theme \r\n');

    const data = await inquirer.prompt([
      {
        type: 'input',
        name: 'slug',
        message: 'Slug:',
        default: 'vilare',
        validate: (value) => {
          return value ? true : 'value is required';
        },
      },
      {
        type: 'input',
        name: 'name',
        message: 'Name:',
        default: 'Vilare',
        validate: (value) => {
          return value ? true : 'value is required';
        },
      },
      {
        type: 'input',
        name: 'author',
        message: 'Author:',
        default: 'pragmatedev',
        validate: (value) => {
          return value ? true : 'value is required';
        },
      },
      {
        type: 'input',
        name: 'author_uri',
        message: 'Author URI:',
        default: 'https://pragmate.dev',
        validate: (value) => {
          return value ? true : 'value is required';
        },
      },
    ]);

    console.log();

    const files = fs.readdirSync(process.cwd())
      .filter(entry => !['.DS_Store', '.git', 'README.md', 'init.sh'].includes(entry))
      .map(entry => ({
        source: `${process.cwd()}/${entry}`,
        destination: `${process.cwd()}/wp-content/themes/${data.slug}/${entry}`,
      }));

    shell.exec(`sed -i '' '/^composer.lock$/d' "${process.cwd()}/.gitignore"`);
    shell.exec(`sed -i '' '/^yarn.lock$/d' "${process.cwd()}/.gitignore"`);

    shell.exec(`sed -i '' "s|husky .husky|cd ../../.. \\&\\& husky wp-content/themes/${data.slug}/.husky|g" ${process.cwd()}/package.json`);
    shell.exec(`sed -i '' "s|yarn lint-staged|cd wp-content/themes/${data.slug}\\\\nyarn lint-staged|" ${process.cwd()}/.husky/pre-commit`);

    shell.exec(`sed -i '' "s|vilare/theme|${data.slug}/theme|g" ${process.cwd()}/composer.json`);
    shell.exec(`sed -i '' "s|Vilare Theme|${data.name} Theme|g" ${process.cwd()}/composer.json`);

    shell.exec(`sed -i '' "s|Theme Name: Vilare|Theme Name: ${data.name}|g" ${process.cwd()}/resources/style.css`);
    shell.exec(`sed -i '' "s|Description: Vilare Theme|Description: ${data.name} Theme|g" ${process.cwd()}/resources/style.css`);
    shell.exec(`sed -i '' "s|Author: pragmatedev|Author: ${data.author}|g" ${process.cwd()}/resources/style.css`);
    shell.exec(`sed -i '' "s|Author URI: https://pragmate.dev|Author URI: ${data.author_uri}|g" ${process.cwd()}/resources/style.css`);

    fs.mkdirSync(`${process.cwd()}/wp-content/themes/${data.slug}`, { recursive: true });

    for (const entry of files) {
      fs.renameSync(entry.source, entry.destination);
    }
  }

  async install() {
    await this.config();
    await this.htaccess();
    await this.core();
    await this.plugins();
    await this.init();
    await this.repo();
    await this.open();
  }

  async setup() {
    await this.build();
    await this.config();
    await this.database();
    await this.open();
  }

  async build() {
    console.log('🔧 Building Project\r\n');

    shell.exec('yarn build');

    console.log();
  }

  async config() {
    console.log('🔑 Setting Up System Configuration\r\n');

    fs.copyFileSync(`${this.templates.path}/configs/wp-config-db.php`, `${this.wordpress.path}/wp-config-db.php`);

    const inputs = await inquirer.prompt([
      {
        type: 'input',
        name: 'DB_NAME',
        message: 'DB_NAME: ',
        default: `${this.theme.slug}.com`,
        validate: (value) => value ? true : 'value is required',
      },
      {
        type: 'input',
        name: 'DB_USER',
        message: 'DB_USER: ',
        default: 'root',
        validate: (value) => value ? true : 'value is required',
      },
      {
        type: 'input',
        name: 'DB_PASSWORD',
        message: 'DB_PASSWORD: ',
        default: 'root',
        validate: (value) => value ? true : 'value is required',
      },
      {
        type: 'input',
        name: 'DB_HOST',
        message: 'DB_HOST: ',
        default: '127.0.0.1:3306',
        validate: (value) => value ? true : 'value is required',
      },
      {
        type: 'input',
        name: 'DB_CHARSET',
        message: 'DB_CHARSET: ',
        default: 'utf8mb4',
        validate: (value) => value ? true : 'value is required',
      },
      {
        type: 'input',
        name: 'DB_COLLATE',
        message: 'DB_COLLATE: ',
        default: 'utf8mb4_general_ci',
        validate: (value) => value ? true : 'value is required',
      },
      {
        type: 'input',
        name: 'DOMAIN_LOCAL',
        message: 'DOMAIN_LOCAL: ',
        default: `https://${this.theme.slug}.test`,
        validate: (value) => value ? true : 'value is required',
      },
      {
        type: 'input',
        name: 'DOMAIN_PROD',
        message: 'DOMAIN_PROD: ',
        default: `https://${this.theme.slug}.com`,
        validate: (value) => value ? true : 'value is required',
      },
      {
        type: 'input',
        name: 'DOMAIN_STAGING',
        message: 'DOMAIN_STAGING: ',
        default: `https://dev.${this.theme.slug}.com`,
        validate: (value) => value ? true : 'value is required',
      },
      {
        type: 'password',
        name: 'ACF_PRO_LICENSE',
        message: 'ACF_PRO_LICENSE: ',
      },
    ]);

    console.log();

    for (const key in inputs) {
      shell.exec(`sed -i '' "s|define( '${key}', '' );|define( '${key}', '${inputs[key]}' );|g" ${this.wordpress.path}/wp-config-db.php`);
    }
  }

  async htaccess() {
    const domains = {
      local: (await config('DOMAIN_LOCAL')).replace(/^https?:\/\//, '').replace(/\//g, ''),
      prod: (await config('DOMAIN_PROD')).replace(/^https?:\/\//, '').replace(/\//g, ''),
    };

    fs.copyFileSync(`${this.templates.path}/configs/htaccess`, `${this.wordpress.path}/.htaccess`);
    fs.copyFileSync(`${this.templates.path}/configs/htpasswd`, `${this.wordpress.path}/.htpasswd`);

    shell.exec(`sed -i '' "s|example.test|${domains.local}|g; s|example.com|${domains.prod}|g" ${this.wordpress.path}/.htaccess`);
  }

  async core() {
    console.log('🔧 Setting Up WordPress Core\r\n');

    const site = await inquirer.prompt([
      {
        type: 'input',
        name: 'title',
        message: 'Title:',
        validate: (value) => {
          return value ? true : 'value is required';
        },
      },
      {
        type: 'input',
        name: 'username',
        message: 'Username:',
        default: 'webmaster',
        validate: (value) => {
          return value ? true : 'value is required';
        },
      },
      {
        type: 'input',
        name: 'password',
        message: 'Password:',
        default: 'test1234',
        validate: (value) => {
          return value ? true : 'value is required';
        },
      },
      {
        type: 'input',
        name: 'email',
        message: 'Email:',
        default: 'team@pragmate.dev',
        validate: (value) => {
          return value ? true : 'value is required';
        },
      },
    ]);

    console.log();

    fs.copyFileSync(`${this.templates.path}/configs/wp-config.php`, `${this.wordpress.path}/wp-config.php`);
    fs.copyFileSync(`${this.templates.path}/configs/wp-cli.yml`, `${this.wordpress.path}/wp-cli.yml`);

    shell.exec(`wp core download --path=${this.wordpress.path}`);
    shell.exec('wp config shuffle-salts');
    shell.exec('wp db reset --yes');
    shell.exec(`wp core install --url=${await config('DOMAIN_LOCAL')} --title=${site.title} --admin_user=${site.username} --admin_password=${site.password} --admin_email="${site.email}" --skip-email`);
    shell.exec('wp user update 1 --first_name=Dev --last_name=Team');
    shell.exec('wp user meta update 1 show_welcome_panel 0');
    shell.exec('wp post delete 3 --force');
    shell.exec('wp post update 2 --post_title=Homepage --post_name=homepage');
    shell.exec('wp term update category 1 --name=News --slug=news');
    shell.exec('wp plugin delete hello akismet');
    shell.exec('wp theme delete twentytwentythree');
    shell.exec('wp theme delete twentytwentyfour');
    shell.exec('wp comment delete 1 --force');
    shell.exec('wp option set blog_public 0');
    shell.exec('wp option update show_on_front page');
    shell.exec('wp option update page_on_front 2');
    shell.exec('wp option update page_for_posts $(wp post create --post_title=Blog --post_name=blog --post_type=page --post_status=publish --post_author=1 --porcelain)');
    shell.exec('wp option update default_comment_status closed');
    shell.exec('wp option update default_ping_status closed');
    shell.exec('wp rewrite structure \'/%postname%/\'');
    shell.exec('wp rewrite flush --hard');

    await this.build();
    shell.exec('yarn translate:build');
    shell.exec(`wp theme activate ${this.theme.slug}/resources`);
    shell.exec('wp post create --post_type=page --post_title="Playground" --page_template="playground" --post_status=publish --post_author=1');
    shell.exec('wp post create --post_type=page --post_title="Demo" --page_template="playground" --post_status=publish --post_author=1 --post_content=\'<!-- wp:acf/guide {"name":"acf/guide","data":[],"mode":"preview"} /-->\'');

    fs.copyFileSync(`${this.templates.path}/configs/phpcs.xml.dist`, `${this.wordpress.path}/phpcs.xml.dist`);
    fs.copyFileSync(`${this.templates.path}/configs/gitignore`, `${this.wordpress.path}/.gitignore`);
    fs.unlinkSync(`${this.wordpress.path}/wp-cli.yml`);

    console.log();
  }

  async plugins() {
    console.log('🧩 Setting Up WordPress Plugins \r\n');

    const inputs = await inquirer.prompt([
      {
        type: 'checkbox',
        name: 'plugins',
        message: 'Plugins:',
        choices: [
          {
            name: 'Advanced Custom Fields',
            value: 'advanced-custom-fields',
            checked: true,
          },
          {
            name: 'Cache Enabler',
            value: 'cache-enabler',
            checked: false,
          },
          {
            name: 'Contact Form 7',
            value: 'contact-form-7',
            checked: false,
          },
          {
            name: 'Yoast SEO',
            value: 'wordpress-seo',
            checked: false,
          },
        ],
      },
    ]);

    console.log();

    if (inputs.plugins.includes('advanced-custom-fields')) {
      if (await config('ACF_PRO_LICENSE', false)) {
        shell.exec(`git clone git@bitbucket.org:coditive/advanced-custom-fields-pro.git ${this.wordpress.path}/wp-content/plugins/advanced-custom-fields-pro`);
        shell.exec(`rm -rf ${this.wordpress.path}/wp-content/plugins/advanced-custom-fields-pro/.git`);
        shell.exec('wp plugin activate advanced-custom-fields-pro');
      } else {
        shell.exec('wp plugin install https://advancedcustomfields.com/latest/ --activate');
      }
    }

    if (inputs.plugins.includes('cache-enabler')) {
      shell.exec('wp plugin install cache-enabler --activate');
      shell.exec('wp option patch update cache_enabler cache_expires 1', { silent: true });
      shell.exec('wp option patch update cache_enabler cache_expiry_time 4', { silent: true });
      shell.exec('wp option patch update cache_enabler clear_site_cache_on_saved_post 1', { silent: true });
      shell.exec('wp option patch update cache_enabler clear_site_cache_on_saved_term 1', { silent: true });
      shell.exec('wp option patch update cache_enabler clear_site_cache_on_saved_user 1', { silent: true });
      shell.exec('wp option patch update cache_enabler clear_site_cache_on_changed_plugin 1', { silent: true });
      shell.exec('wp plugin deactivate cache-enabler', { silent: true });
    }

    if (inputs.plugins.includes('contact-form-7')) {
      shell.exec('wp plugin install contact-form-7 --activate');
    }

    if (inputs.plugins.includes('wordpress-seo')) {
      shell.exec('wp plugin install wordpress-seo --activate');
      shell.exec('wp option patch update wpseo dismiss_configuration_workout_notice 1', { silent: true });
      shell.exec('wp option patch update wpseo ignore_search_engines_discouraged_notice 1', { silent: true });
      shell.exec('wp option patch update wpseo enable_ai_generator 0', { silent: true });
      shell.exec('wp option patch update wpseo enable_metabox_insights 0', { silent: true });
      shell.exec('wp option patch update wpseo enable_admin_bar_menu 0', { silent: true });
      shell.exec('wp option patch update wpseo enable_cornerstone_content 0', { silent: true });
      shell.exec('wp option patch update wpseo semrush_integration_active 0', { silent: true });
      shell.exec('wp option patch update wpseo wincher_integration_active 0', { silent: true });
    }

    console.log();
  }

  async init() {
    fs.unlinkSync(`${this.wordpress.path}/init.sh`);
    fs.copyFileSync(`${this.templates.path}/configs/init-repository.sh`, `${this.wordpress.path}/init.sh`);
    shell.exec(`sed -i '' "s|themes/vilare|themes/${this.theme.slug}|g" ${this.wordpress.path}/init.sh`);
  }

  async repo() {
    console.log('🔄 Setting Up Repository \r\n');

    const { remote } = await inquirer.prompt([
      {
        type: 'input',
        name: 'remote',
        message: 'Remote:',
        default: '',
      },
    ]);

    console.log();

    shell.cd(this.wordpress.path);

    if (remote.includes('bitbucket')) {
      fs.copyFileSync(`${this.templates.path}/pipelines/bitbucket-pipelines.yml`, `${this.wordpress.path}/bitbucket-pipelines.yml`);
      shell.exec(`sed -i '' 's/vilare/${this.theme.slug}/g' "${this.wordpress.path}/bitbucket-pipelines.yml"`);
    }

    if (remote.includes('github')) {
      fs.mkdirSync(`${this.wordpress.path}/.github/workflows`, { recursive: true });
      fs.copyFileSync(`${this.templates.path}/pipelines/github-actions.yaml`, `${this.wordpress.path}/.github/workflows/lint.yaml`);
      shell.exec(`sed -i '' 's/vilare/${this.theme.slug}/g' ${this.wordpress.path}/.github/workflows/lint.yaml`);
    }

    shell.exec('git init');
    shell.exec('git checkout -b master');

    shell.exec('git add .');
    shell.exec('git commit -m "install wordpress"', { silent: true });

    shell.exec('git checkout -b develop');

    if (remote) {
      shell.exec(`git remote add origin ${remote}`);
      shell.exec('git push -u origin master');
      shell.exec('git push -u origin develop');
    }

    shell.cd(this.theme.path);

    console.log();
  }

  async database() {
    console.log('📂 Setting Up WordPress Database\r\n');

    const database = new Database();

    if (!fs.existsSync(`${this.wordpress.path}/db.sql.gz`)) {
      await inquirer.prompt([
        {
          type: 'input',
          name: 'confirm',
          message: `Put the ${chalk.green('db.sql.gz')} file in the ${chalk.green(this.wordpress.path)} and press enter...`,
          validate: (value) => fs.existsSync(`${this.wordpress.path}/db.sql.gz`) ? true : 'db.sql.gz file not found',
        },
      ]);
    }

    const { environment } = await inquirer.prompt({
      type: 'list',
      name: 'environment',
      message: 'Choose database source:',
      choices: ['local', 'staging', 'production'],
    });

    console.log();

    await database.import(environment);

    console.log();
  }

  async open() {
    shell.exec(`open ${await config('DOMAIN_LOCAL')}/demo/`);
    console.log();
    console.log(chalk.green('Success:'), `Opened ${await config('DOMAIN_LOCAL')}`);
  }
}

export const install = () => {
  const program = new Command('install');
  const controller = new Controller();

  program
    .description('install project')
    .option('-p, --prepare', 'prepare project installation')
    .option('-s, --setup', 'setup project installation')
    .action(async(options) => {
      try {
        if (options.prepare) {
          controller.prepare();
        } else if (options.setup) {
          controller.setup();
        } else {
          controller.install();
        }
      } catch (error) {
        program.error(error);
      }
    });

  return program;
};
