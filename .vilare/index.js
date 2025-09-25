import dotenv from 'dotenv';
import fs from 'fs';
import path from 'path';
import { Command } from 'commander';
import { database } from './commands/database.js';
import { component } from './commands/component.js';
import { install } from './commands/install.js';
import { release } from './commands/release.js';
import { test } from './commands/test.js';

const env = path.resolve('../../../.env');

if (fs.existsSync(env)) {
  dotenv.config({ path: env, quiet: true });
}

const program = new Command();

console.clear();

program
  .name('vilare')
  .version('1.0.0');

program.addCommand(database());
program.addCommand(component());
program.addCommand(release());
program.addCommand(test());
program.addCommand(install());

program.parseAsync(process.argv);
