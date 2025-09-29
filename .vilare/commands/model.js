import fs from 'fs';
import path from 'path';
import inquirer from 'inquirer';
import shell from 'shelljs';
import { Command } from 'commander';

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

  async process(options) {
    const data = await inquirer.prompt([
      {
        type: 'list',
        name: 'task',
        message: 'What do you want to do?',
        choices: [
          {
            name: 'create model',
            value: 'create',
          }],
      },
    ]);

    switch (data.task) {
      case 'create':
        await this.create(options);
        break;
    }
  }

  async create(options) {
    const inputs = await inquirer.prompt([
      {
        type: 'input',
        name: 'id',
        message: 'ID: ',
        when: () => !options.id,
      },
      {
        type: 'input',
        name: 'title',
        message: 'Title: ',
        when: () => !options.title,
      },
    ]);

    const config = {
      id: options.id || inputs.id,
      title: options.title || inputs.title,
    };

    if (!/^[a-z]+(-[a-z]+)*$/.test(config.id)) {
      throw new Error('id must be kebab-case');
    }

    if (!/^[A-Z][a-zA-Z]*$/.test(config.title)) {
      throw new Error('title must be PascalCase');
    }

    if (fs.existsSync(`${this.theme.path}/app/${config.title}.php`)) {
      throw new Error(`❌ ${config.title} model already exists`);
    }

    fs.copyFileSync(`${this.templates.path}/models/Article.php`, `${this.theme.path}/app/${config.title}.php`);
    shell.exec(`sed -i '' "s|Article|${config.title}|g" ${this.theme.path}/app/${config.title}.php`);
    shell.exec(`sed -i '' "s|article|${config.id}|g" ${this.theme.path}/app/${config.title}.php`);

    console.log(`✅ ${config.title} model created successfully`);
  }
}

export const model = () => {
  const program = new Command('model');
  const controller = new Controller();

  program
    .description('manage project models')
    .action(async(options) => {
      try {
        await controller.process(options);
      } catch (error) {
        program.error(error.message);
      }
    });

  program
    .command('create')
    .description('create a new model')
    .option('-i, --id <id>', 'the id of the model')
    .option('-t, --title <title>', 'the title of the model')
    .action(async(options) => {
      try {
        await controller.create(options);
      } catch (error) {
        program.error(error.message);
      }
    });

  return program;
};
