'use strict';

// Scan d'accessibilité d'une URL : Puppeteer charge la page dans un Chromium
// headless, y injecte axe-core, exécute l'analyse et émet le JSON axe brut sur
// stdout. Aucune logique métier ici : Symfony valide/normalise le contrat JSON.
//
//   node scan.js <url>
//
// Sortie (stdout) : { url, timestamp, violations, passes, incomplete, inapplicable }
// Codes de sortie : 0 = OK, 1 = erreur d'exécution, 2 = usage incorrect.

const puppeteer = require('puppeteer');
const { source: axeSource } = require('axe-core');

const TIMEOUT_MS = 60000;

async function scan(url) {
  const browser = await puppeteer.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'],
  });

  try {
    const page = await browser.newPage();
    await page.goto(url, { waitUntil: 'networkidle2', timeout: TIMEOUT_MS });
    await page.evaluate(axeSource);

    const results = await page.evaluate(async () =>
      window.axe.run(document, {
        resultTypes: ['violations', 'passes', 'incomplete', 'inapplicable'],
      }),
    );

    return {
      url: results.url,
      timestamp: results.timestamp,
      violations: results.violations,
      passes: results.passes,
      incomplete: results.incomplete,
      inapplicable: results.inapplicable,
    };
  } finally {
    await browser.close();
  }
}

async function main() {
  const url = process.argv[2];

  if (!url) {
    process.stderr.write('Usage: node scan.js <url>\n');
    process.exit(2);
  }

  const report = await scan(url);
  process.stdout.write(JSON.stringify(report));
}

main().catch((error) => {
  process.stderr.write(`${(error && error.stack) || error}\n`);
  process.exit(1);
});
