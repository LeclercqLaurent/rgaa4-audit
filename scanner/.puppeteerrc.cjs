// Cache Puppeteer (binaire Chromium) confiné dans le volume du scanner, pour
// survivre à la recréation du conteneur et rester dans le périmètre projet.
const { join } = require('path');

module.exports = {
  cacheDirectory: join(__dirname, '.cache', 'puppeteer'),
};
