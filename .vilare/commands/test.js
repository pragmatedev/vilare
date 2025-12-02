import chalk from 'chalk';
import fs from 'fs';
import inquirer from 'inquirer';
import path from 'path';
import lighthouse from 'lighthouse';
import puppeteer from 'puppeteer';
import { Command } from 'commander';
import { config, isUrl } from '../utils.js';

import desktop from 'lighthouse/core/config/lr-desktop-config.js';
import mobile from 'lighthouse/core/config/lr-mobile-config.js';

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
    console.log('🚧 Setting Up Test Operations\r\n');

    const data = await inquirer.prompt([
      {
        type: 'list',
        name: 'task',
        message: 'What do you want to do?',
        choices: [
          {
            name: 'test PSI results for a page',
            value: 'psi',
          }],
      },
      {
        type: 'input',
        name: 'path',
        message: 'Path: ',
        default: '/playground/',
        when: (answers) => answers.task === 'psi',
        validate: (value) => value ? true : 'value is required',
      },
      {
        type: 'list',
        name: 'devices',
        message: 'Path: ',
        choices: [
          { name: 'Mobile', value: 'mobile' },
          { name: 'Desktop', value: 'desktop' },
          { name: 'Both', value: 'both' },
        ],
        when: (answers) => answers.task === 'psi',
      },
    ]);

    console.log();

    switch (data.task) {
      case 'psi':
        if (data.devices === 'mobile') {
          await this.psi(data.path, true);
        } else if (data.devices === 'desktop') {
          await this.psi(data.path, false);
        } else {
          await this.psi(data.path, true);
          await this.psi(data.path, false);
        }
        break;
    }
  }

  async psi(slug, isMobile = true) {
    const url = isUrl(slug) ? slug : `${await config('DOMAIN_LOCAL')}/${slug}/`.replace(/\/{2,}/g, '/').replace(/\/+$/, '/');

    if (!isUrl(url)) {
      throw new Error(`Invalid URL: ${test}`);
    }

    if (isMobile) {
      console.log(`📱 Testing ${chalk.green('mobile')} PSI scores for: ${chalk.green(url)}\r\n`);
    } else {
      console.log(`💻 Testing ${chalk.green('desktop')} PSI scores for: ${chalk.green(url)}\r\n`);
    }

    const BASE = this.output.path.replace(this.wordpress.path, await config('DOMAIN_LOCAL'));

    const browser = await puppeteer.launch();
    const endpoint = new URL(browser.wsEndpoint());

    if (!fs.existsSync(this.output.path)) {
      fs.mkdirSync(this.output.path);
    }

    const result = await lighthouse(
      url,
      {
        output: 'html',
        onlyCategories: [
          'performance',
          'accessibility',
        ],
        port: endpoint.port,
      },
      isMobile ? mobile : desktop,
    );

    if (isMobile) {
      fs.writeFileSync(`${this.output.path}/lighthouse-mobile.html`, await this.report(result.report), 'utf-8');
      await this.screenshot(`${BASE}/lighthouse-mobile.html`, `${this.output.path}/lighthouse-mobile.png`);
      console.log(`- Performance: ${result.lhr.categories.performance.score * 100}\r\n- Accessibility: ${result.lhr.categories.accessibility.score * 100}\r\n- Report: ${BASE}/lighthouse-mobile.html\r\n- Image: ${BASE}/lighthouse-mobile.png\r\n`);
    } else {
      fs.writeFileSync(`${this.output.path}/lighthouse-desktop.html`, await this.report(result.report), 'utf-8');
      await this.screenshot(`${BASE}/lighthouse-desktop.html`, `${this.output.path}/lighthouse-desktop.png`);
      console.log(`- Performance: ${result.lhr.categories.performance.score * 100}\r\n- Accessibility: ${result.lhr.categories.accessibility.score * 100}\r\n- Report: ${BASE}/lighthouse-desktop.html\r\n- Image: ${BASE}/lighthouse-desktop.png\r\n`);
    }

    await browser.close();
  }

  async screenshot(url, path) {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });
    await page.goto(url);
    await page.screenshot({ path, fullPage: true });
    await browser.close();
  }

  async report(html) {
    return html.replace('</head>', `
      <style>
      .lh-topbar {display: none!important}
      .lh-metrics__disclaimer {display: none!important}
      .lh-filmstrip-container {display: none!important}
      .lh-sticky-header {display: none!important}
      .lh-category-header__description {display: none!important}
      .lh-audit-group--metrics .lh-audit-group__header {display: none!important}
      .lh-audit-group--metrics .lh-buttons {display: none!important}
      .lh-metricfilter {display: none!important}
      ._lh-clump--manual {display: none!important}
      .lh-clump--passed {display: none!important}
      .lh-clump--notapplicable {display: none!important}
      .lh-audit--informative {display: none!important}
      .lh-audit-group__footer {display: none!important}
      .lh-footer {display: none!important}
      .lh-categories { display: flex; }
      .lh-category-wrapper { width: 50%; }
      </style>
      </head>`);
  }
}

export const test = () => {
  const program = new Command('test');
  const controller = new Controller();

  program
    .description('manage test operations')
    .action(async(options) => {
      try {
        await controller.process();
      } catch (error) {
        program.error(error);
      }
    });

  program
    .command('psi')
    .description('test PSI results for a page')
    .option('-u, --url <url>', 'the url of the page to test', '/playground/')
    .option('-m, --mobile', 'test mobile PSI scores')
    .option('-d, --desktop', 'test desktop PSI scores')
    .action(async(options) => {
      try {
        if (options.mobile) {
          await controller.psi(options.url, true);
        } else if (options.desktop) {
          await controller.psi(options.url, false);
        } else {
          await controller.psi(options.url, true);
          await controller.psi(options.url, false);
        }
      } catch (error) {
        program.error(error);
      }
    });

  return program;
};
