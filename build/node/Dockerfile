FROM node:24

# Dépendances système Chromium pour Puppeteer (image Debian node:24).
# Liste alignée sur les recommandations Puppeteer ; à ajuster si la base Debian
# de node:24 renomme un paquet (ex. libasound2 -> libasound2t64 selon la release).
RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates fonts-liberation fonts-noto-color-emoji \
        libasound2 libatk-bridge2.0-0 libatk1.0-0 libcairo2 libcups2 \
        libdbus-1-3 libexpat1 libfontconfig1 libgbm1 libglib2.0-0 libgtk-3-0 \
        libnspr4 libnss3 libpango-1.0-0 libx11-6 libxcomposite1 \
        libxdamage1 libxext6 libxfixes3 libxkbcommon0 libxrandr2 \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /scanner
