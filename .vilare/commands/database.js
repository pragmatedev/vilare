import fs from 'fs';
import path from 'path';
import inquirer from 'inquirer';
import shell from 'shelljs';
import { Command } from 'commander';
import { config } from '../utils.js';

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

  async process() {
    const data = await inquirer.prompt([
      {
        type: 'list',
        name: 'action',
        message: 'Action:',
        choices: ['export', 'import', 'anonimize', 'optimize'],
      },
      {
        type: 'list',
        name: 'environment',
        message: 'Environment:',
        choices: ['local', 'staging', 'production'],
        when: (answers) => ['export', 'import'].includes(answers.action),
      },
    ]);

    switch (data.action) {
      case 'export':
        this.export(data.environment);
        break;

      case 'import':
        this.import(data.environment);
        break;

      case 'anonimize':
        this.anonimize();
        break;

      case 'optimize':
        this.optimize();
        break;

      default:
        break;
    }
  }

  async export(environment) {
    if (fs.existsSync(`${this.wordpress.path}/db.sql.gz`)) {
      shell.exec(`rm ${this.wordpress.path}/db.sql.gz`);
    }

    if (fs.existsSync(`${this.wordpress.path}/db.gz`)) {
      shell.exec(`rm ${this.wordpress.path}/db.gz`);
    }

    switch (environment) {
      case 'local':
        shell.exec(`wp db export ${this.wordpress.path}/db.sql`);
        shell.exec(`gzip ${this.wordpress.path}/db.sql`);
        break;

      case 'staging':
        shell.exec(`wp search-replace ${await config('DOMAIN_LOCAL')} ${await config('DOMAIN_STAGING')} --all-tables --report=0`);
        shell.exec(`wp db export ${this.wordpress.path}/db.sql`);
        shell.exec(`gzip ${this.wordpress.path}/db.sql`);
        shell.exec(`wp search-replace ${await config('DOMAIN_STAGING')} ${await config('DOMAIN_LOCAL')} --all-tables --report=0`);
        break;

      case 'production':
        shell.exec(`wp search-replace ${await config('DOMAIN_LOCAL')} ${await config('DOMAIN_PROD')} --all-tables --report=0`);
        shell.exec(`wp db export ${this.wordpress.path}/db.sql`);
        shell.exec(`gzip ${this.wordpress.path}/db.sql`);
        shell.exec(`wp search-replace ${await config('DOMAIN_PROD')} ${await config('DOMAIN_LOCAL')} --all-tables --report=0`);
        break;

      default:
        break;
    }
  }

  async import(environment) {
    if (fs.existsSync(`${this.wordpress.path}/db.sql.gz`)) {
      shell.exec(`gzip -d ${this.wordpress.path}/db.sql.gz`);
    }

    if (!fs.existsSync(`${this.wordpress.path}/db.sql`)) {
      throw new Error(`Database file not found: ${this.wordpress.path}/db.sql`);
    }

    shell.exec('wp db reset --yes');
    shell.exec(`wp db import ${this.wordpress.path}/db.sql`);
    shell.exec(`rm ${this.wordpress.path}/db.sql`);

    switch (environment) {
      case 'staging':
        shell.exec(`wp search-replace ${await config('DOMAIN_STAGING')} ${await config('DOMAIN_LOCAL')} --all-tables --report=0`);
        break;

      case 'production':
        shell.exec(`wp search-replace ${await config('DOMAIN_PROD')} ${await config('DOMAIN_LOCAL')} --all-tables --report=0`);
        break;

      default:
        break;
    }
  }

  async optimize() {
    shell.exec('wp post delete $(wp post list --post_type=\'revision\' --format=ids) --force');
    shell.exec('wp db query "DELETE pm FROM wp_postmeta pm LEFT JOIN wp_posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL;"');
    shell.exec('wp db query "DELETE FROM wp_commentmeta WHERE comment_id NOT IN (SELECT comment_id FROM wp_comments);"');
    shell.exec('wp db query "DELETE FROM wp_options WHERE option_name LIKE \'_wp_session_%\'"');
    shell.exec('wp db query "DELETE FROM wp_options WHERE option_name LIKE (\'%_transient_%\')"');
    shell.exec('wp db query "DELETE FROM wp_comments WHERE comment_type = \'pingback\';"');
    shell.exec('wp db query "DELETE FROM wp_comments WHERE comment_type = \'trackback\';"');
    shell.exec('wp db optimize');
  }

  async anonimize() {
    shell.exec('wp option set blog_public 0');
    shell.exec('wp user update $(wp user list --field=ID) --user_pass=test1234 --skip-email');
  }
}

export const database = () => {
  const program = new Command('database');
  const controller = new Controller();

  program
    .description('manage project database')
    .action(async() => {
      await controller.process();
    });

  program
    .command('export')
    .description('export database')
    .option('-e, --env <env>', 'local | staging | production', 'local')
    .action(async(options) => {
      try {
        await controller.export(options.env);
      } catch (error) {
        program.error(error);
      }
    });

  program
    .command('import')
    .description('import database')
    .option('-e, --env <env>', 'local | staging | production', 'local')
    .action(async(options) => {
      try {
        await controller.import(options.env);
      } catch (error) {
        program.error(error);
      }
    });

  program
    .command('anonimize')
    .description('anonimize database')
    .action(async() => {
      try {
        await controller.anonimize();
      } catch (error) {
        program.error(error);
      }
    });

  program
    .command('optimize')
    .description('optimize database')
    .action(async() => {
      try {
        await controller.optimize();
      } catch (error) {
        program.error(error);
      }
    });

  return program;
};
