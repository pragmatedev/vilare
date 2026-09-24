import fs from 'fs';
import path from 'path';
import inquirer from 'inquirer';
import shell from 'shelljs';
import { camelCase, kebabCase, snakeCase, startCase, upperFirst } from 'lodash-es';
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
        type: 'select',
        name: 'task',
        message: 'What do you want to do?',
        choices: [
          {
            name: 'create component',
            value: 'create',
          },
          {
            name: 'pull component',
            value: 'pull',
          },
        ],
      },
    ]);

    switch (data.task) {
      case 'create':
        await this.create(options);
        break;
      case 'pull':
        await this.pull(options);
        break;
    }
  }

  async create(options) {
    const inputs = await inquirer.prompt([
      {
        type: 'select',
        name: 'type',
        message: 'Type: ',
        choices: () => ['block', 'component', 'template'],
        when: () => !options.type,
      },
      {
        type: 'input',
        name: 'id',
        message: 'ID: ',
        when: () => !options.id,
      },
    ]);

    const config = {
      type: options.type || inputs.type,
      id: kebabCase(options.id || inputs.id),
      name: upperFirst(camelCase(options.id || inputs.id)),
      title: startCase(camelCase(options.id || inputs.id)),
    };

    const files = this.getFiles(config);

    for (const file of files) {
      const dir = path.dirname(file.destination);

      if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
      }

      fs.copyFileSync(file.source, file.destination);

      shell.exec(`sed -i '' "s|base|${config.id}|g" ${file.destination}`);
      shell.exec(`sed -i '' "s|Base|${config.name}|g" ${file.destination}`);

      shell.exec(`sed -i '' "s|setTitle('${config.name}')|setTitle('${config.title}')|g" ${file.destination}`);
      shell.exec(`sed -i '' "s|'title' => '${config.name}'|'title' => '${config.title}'|g" ${file.destination}`);
      shell.exec(`sed -i '' 's|"title": "${config.name}"|"title": "${config.title}"|g' ${file.destination}`);

      shell.exec(`sed -i '' "s|_${config.id}|_${snakeCase(config.id)}|g" ${file.destination}`);
    }

    console.log(`✅ ${config.title} ${config.type} created successfully`);
  }

  async pull(options) {
    const repository = [
      {
        id: 'form',
        name: 'Form',
        type: 'block',
        repository: 'git@github.com:pragmatedev/vilare-block-form.git',
      },
      {
        id: 'navbar',
        name: 'Navbar',
        type: 'block',
        repository: 'git@github.com:pragmatedev/vilare-block-navbar.git',
      },
      {
        id: 'query',
        name: 'Query',
        type: 'block',
        repository: 'git@github.com:pragmatedev/vilare-block-query.git',
      },
      {
        id: 'vimeo',
        name: 'Vimeo',
        type: 'component',
        repository: 'git@github.com:pragmatedev/vilare-component-vimeo.git',
      },
    ];

    const inputs = await inquirer.prompt([
      {
        type: 'select',
        name: 'type',
        message: 'Type: ',
        choices: () => ['block', 'component', 'template'],
        when: () => !options.type,
      },
      {
        type: 'select',
        name: 'item',
        message: 'Item: ',
        choices: (answers) => repository.filter(item => item.type === (options.type || answers.type)).map(item => item.repository),
        when: () => !options.item,
      },
    ]);

    const item = repository.find(item => item.repository === inputs.item);

    if (fs.existsSync(this.output.path)) {
      fs.rmSync(this.output.path, { recursive: true });
    }

    shell.exec(`git clone ${item.repository} ${this.output.path}`);

    for (const file of this.getFilesRepo(item)) {
      const dir = path.dirname(file.destination);

      if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
      }

      fs.copyFileSync(file.source, file.destination);
    }

    if (fs.existsSync(this.output.path)) {
      fs.rmSync(this.output.path, { recursive: true });
    }

    console.log(`✅ ${item.name} ${item.type} created successfully`);
  }

  getFiles(config) {
    if (!['block', 'component', 'template'].includes(config.type)) {
      throw new Error('invalid component type');
    }

    if (!/^[a-z]+(-[a-z]+)*$/.test(config.id)) {
      throw new Error('id must be kebab-case');
    }

    if (!/^[A-Z][a-zA-Z]*$/.test(config.name)) {
      throw new Error('title must be PascalCase');
    }

    switch (config.type) {
      case 'block':
        if (fs.existsSync(`${this.theme.path}/resources/blocks/${config.id}`)) {
          throw new Error(`❌ ${config.id} block already exists`);
        }

        if (fs.existsSync(`${this.theme.path}/app/Blocks/${config.name}.php`)) {
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
            source: `${this.templates.path}/blocks/base/block.json`,
            destination: `${this.theme.path}/resources/blocks/${config.id}/block.json`,
          },
          {
            source: `${this.templates.path}/blocks/base/Base.php`,
            destination: `${this.theme.path}/app/Blocks/${config.name}.php`,
          },
        ];

      case 'component':
        if (fs.existsSync(`${this.theme.path}/resources/components/${config.id}`)) {
          throw new Error(`❌ ${config.id} component already exists`);
        }

        if (fs.existsSync(`${this.theme.path}/app/Components/${config.name}.php`)) {
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
            destination: `${this.theme.path}/app/Components/${config.name}.php`,
          },
        ];

      case 'template':
        if (fs.existsSync(`${this.theme.path}/resources/templates/${config.id}`)) {
          throw new Error(`❌ ${config.id} template already exists`);
        }

        if (fs.existsSync(`${this.theme.path}/app/Templates/${config.name}.php`)) {
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
            destination: `${this.theme.path}/app/Templates/${config.name}.php`,
          },
        ];

      default:
        return [];
    }
  }

  getFilesRepo(config) {
    if (!['block', 'component', 'template'].includes(config.type)) {
      throw new Error('invalid component type');
    }

    if (!/^[a-z]+(-[a-z]+)*$/.test(config.id)) {
      throw new Error('id must be kebab-case');
    }

    if (!/^[A-Z][a-zA-Z]*$/.test(config.name)) {
      throw new Error('title must be PascalCase');
    }

    switch (config.type) {
      case 'block':
        if (fs.existsSync(`${this.theme.path}/resources/blocks/${config.id}`)) {
          throw new Error(`❌ ${config.id} block already exists`);
        }

        if (fs.existsSync(`${this.theme.path}/app/Blocks/${config.name}.php`)) {
          throw new Error(`❌ ${config.id} block already exists`);
        }

        return [
          {
            source: `${this.output.path}/block.json`,
            destination: `${this.theme.path}/resources/blocks/${config.id}/block.json`,
          },
          {
            source: `${this.output.path}/script.js`,
            destination: `${this.theme.path}/resources/blocks/${config.id}/script.js`,
          },
          {
            source: `${this.output.path}/style.scss`,
            destination: `${this.theme.path}/resources/blocks/${config.id}/style.scss`,
          },
          {
            source: `${this.output.path}/template.blade.php`,
            destination: `${this.theme.path}/resources/blocks/${config.id}/template.blade.php`,
          },
          {
            source: `${this.output.path}/${config.name}.php`,
            destination: `${this.theme.path}/app/Blocks/${config.name}.php`,
          },
        ];

      case 'component':
        if (fs.existsSync(`${this.theme.path}/resources/components/${config.id}`)) {
          throw new Error(`❌ ${config.id} component already exists`);
        }

        if (fs.existsSync(`${this.theme.path}/app/Components/${config.name}.php`)) {
          throw new Error(`❌ ${config.id} component already exists`);
        }

        return [
          {
            source: `${this.output.path}/script.js`,
            destination: `${this.theme.path}/resources/components/${config.id}/script.js`,
          },
          {
            source: `${this.output.path}/style.scss`,
            destination: `${this.theme.path}/resources/components/${config.id}/style.scss`,
          },
          {
            source: `${this.output.path}/template.blade.php`,
            destination: `${this.theme.path}/resources/components/${config.id}/template.blade.php`,
          },
          {
            source: `${this.output.path}/${config.name}.php`,
            destination: `${this.theme.path}/app/Components/${config.name}.php`,
          },
        ];

      case 'template':
        if (fs.existsSync(`${this.theme.path}/resources/templates/${config.id}`)) {
          throw new Error(`❌ ${config.id} template already exists`);
        }

        if (fs.existsSync(`${this.theme.path}/app/Templates/${config.name}.php`)) {
          throw new Error(`❌ ${config.id} template already exists`);
        }

        return [
          {
            source: `${this.output.path}/script.js`,
            destination: `${this.theme.path}/resources/templates/${config.id}/script.js`,
          },
          {
            source: `${this.output.path}/style.scss`,
            destination: `${this.theme.path}/resources/templates/${config.id}/style.scss`,
          },
          {
            source: `${this.output.path}/template.blade.php`,
            destination: `${this.theme.path}/resources/templates/${config.id}/template.blade.php`,
          },
          {
            source: `${this.output.path}/${config.name}.php`,
            destination: `${this.theme.path}/app/Templates/${config.name}.php`,
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
    .option('-t, --type <type>', 'the type of the component')
    .option('-i, --id <id>', 'the id of the component')
    .action(async(options) => {
      try {
        await controller.create(options);
      } catch (error) {
        program.error(error.message);
      }
    });

  program
    .command('pull')
    .description('pull components from repository')
    .option('-t, --type <type>', 'the type of the component')
    .option('-i, --item <item>', 'the item to pull')
    .action(async(options) => {
      try {
        await controller.pull(options);
      } catch (error) {
        program.error(error.message);
      }
    });

  return program;
};
