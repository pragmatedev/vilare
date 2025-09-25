import fs from 'fs';
import path from 'path';
import inquirer from 'inquirer';
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
            name: 'create component',
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
        type: 'list',
        name: 'type',
        message: 'Type: ',
        choices: ['block', 'component', 'template'],
        when: () => !options.type,
      },
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
      type: options.type || inputs.type,
      id: options.id || inputs.id,
      title: options.title || inputs.title,
    };

    const files = this.getFiles(config);

    for (const file of files) {
      const dir = path.dirname(file.destination);

      if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
      }

      fs.copyFileSync(file.source, file.destination);

      const content = fs.readFileSync(file.destination, 'utf8')
        .replace(/Base/g, config.title)
        .replace(/base/g, config.id);

      fs.writeFileSync(file.destination, content, 'utf8');
    }

    console.log(`✅ ${config.title} ${config.type} created successfully`);
  }

  getFiles(config) {
    if (!['block', 'component', 'template'].includes(config.type)) {
      throw new Error('invalid component type');
    }

    if (!/^[a-z]+(-[a-z]+)*$/.test(config.id)) {
      throw new Error('id must be kebab-case');
    }

    if (!/^[A-Z][a-zA-Z]*$/.test(config.title)) {
      throw new Error('title must be PascalCase');
    }

    switch (config.type) {
      case 'block':
        if (fs.existsSync(`${this.theme.path}/resources/blocks/${config.id}`)) {
          throw new Error(`❌ ${config.id} block already exists`);
        }

        if (fs.existsSync(`${this.theme.path}/app/Blocks/${config.title}.php`)) {
          throw new Error(`❌ ${config.id} block already exists`);
        }

        return [
          {
            source: `${this.templates.path}/blocks/base/script.js`,
            destination: `${this.theme.path}/resources/blocks/${config.id}/script.js`,
          },
          {
            source: `${this.templates.path}/blocks/base/style.scss`,
            destination: `${this.theme.path}/resources/blocks/${config.id}/style.scss`,
          },
          {
            source: `${this.templates.path}/blocks/base/template.blade.php`,
            destination: `${this.theme.path}/resources/blocks/${config.id}/template.blade.php`,
          },
          {
            source: `${this.templates.path}/blocks/base/Base.php`,
            destination: `${this.theme.path}/app/Blocks/${config.title}.php`,
          },
        ];

      case 'component':
        if (fs.existsSync(`${this.theme.path}/resources/components/${config.id}`)) {
          throw new Error(`❌ ${config.id} component already exists`);
        }

        if (fs.existsSync(`${this.theme.path}/app/Components/${config.title}.php`)) {
          throw new Error(`❌ ${config.id} component already exists`);
        }

        return [
          {
            source: `${this.templates.path}/components/base/script.js`,
            destination: `${this.theme.path}/resources/components/${config.id}/script.js`,
          },
          {
            source: `${this.templates.path}/components/base/style.scss`,
            destination: `${this.theme.path}/resources/components/${config.id}/style.scss`,
          },
          {
            source: `${this.templates.path}/components/base/template.blade.php`,
            destination: `${this.theme.path}/resources/components/${config.id}/template.blade.php`,
          },
          {
            source: `${this.templates.path}/components/base/Base.php`,
            destination: `${this.theme.path}/app/Components/${config.title}.php`,
          },
        ];

      case 'template':
        if (fs.existsSync(`${this.theme.path}/resources/templates/${config.id}`)) {
          throw new Error(`❌ ${config.id} template already exists`);
        }

        if (fs.existsSync(`${this.theme.path}/app/Templates/${config.title}.php`)) {
          throw new Error(`❌ ${config.id} template already exists`);
        }

        return [
          {
            source: `${this.templates.path}/templates/base/script.js`,
            destination: `${this.theme.path}/resources/templates/${config.id}/script.js`,
          },
          {
            source: `${this.templates.path}/templates/base/style.scss`,
            destination: `${this.theme.path}/resources/templates/${config.id}/style.scss`,
          },
          {
            source: `${this.templates.path}/templates/base/template.blade.php`,
            destination: `${this.theme.path}/resources/templates/${config.id}/template.blade.php`,
          },
          {
            source: `${this.templates.path}/templates/base/Base.php`,
            destination: `${this.theme.path}/app/Templates/${config.title}.php`,
          },
        ];

      default:
        return [];
    }
  }
}

export const component = () => {
  const program = new Command('component');
  const controller = new Controller();

  program
    .description('manage project components')
    .action(async(options) => {
      try {
        await controller.process(options);
      } catch (error) {
        program.error(error.message);
      }
    });

  program
    .command('create')
    .description('create a new component')
    .option('-y, --type <type>', 'the type of the component')
    .option('-i, --id <id>', 'the id of the component')
    .option('-t, --title <title>', 'the title of the component')
    .action(async(options) => {
      try {
        await controller.create(options);
      } catch (error) {
        program.error(error.message);
      }
    });

  return program;
};
