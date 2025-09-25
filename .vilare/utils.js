import dotenv from 'dotenv';
import fs from 'fs';
import inquirer from 'inquirer';

export const config = async(name, ask = true) => {
  let configPath = '';
  let value = '';

  if (fs.existsSync(`${process.cwd()}/wp-config-db.php`)) {
    configPath = `${process.cwd()}/wp-config-db.php`;
  } else if (fs.existsSync(`${process.cwd()}/../../../wp-config-db.php`)) {
    configPath = `${process.cwd()}/../../../wp-config-db.php`;
  }

  if (fs.existsSync(configPath)) {
    const configContent = fs.readFileSync(configPath, 'utf8');

    const defineRegex = /define\s*\(\s*['"`]([^'"`]+)['"`]\s*,\s*['"`]([^'"`]*)['"`]\s*\)/g;
    const config = {};

    let match;

    while ((match = defineRegex.exec(configContent)) !== null) {
      const [, key, value] = match;
      config[key] = value;
    }

    if (name in config) {
      value = config[name].trim();
    } else if (name in process.env) {
      value = process.env[name].trim();
    }
  }

  if (!value) {
    if (process.env[name]) {
      value = process.env[name].trim();
    } else {
      if (fs.existsSync(`${process.cwd()}/.env`)) {
        dotenv.config({ path: `${process.cwd()}/.env`, quiet: true });
      } else if (fs.existsSync(`${process.cwd()}/../../../.env`)) {
        dotenv.config({ path: `${process.cwd()}/../../../.env`, quiet: true });
      }

      if (name in process.env) {
        value = process.env[name].trim();
      }
    }
  }

  if (!value && ask) {
    const data = await inquirer.prompt([
      {
        type: name.includes('PASSWORD') ? 'password' : 'input',
        name: 'value',
        message: name,
        validate: (value) => {
          return value.trim() === '' ? 'Value is required' : true;
        },
      },
    ]);

    return data.value.trim();
  }

  return value;
};

export const isFileExists = (value) => {
  return fs.existsSync(value);
};

export const isKebabCase = (value) => {
  return /^[a-z]+(-[a-z]+)*$/.test(value);
};

export const isPascalCase = (value) => {
  return /^[A-Z][a-zA-Z]*$/.test(value);
};

export const isUrl = (value) => {
  try {
    const url = new URL(value);

    return !!url;
  } catch (e) {
    return false;
  }
};
