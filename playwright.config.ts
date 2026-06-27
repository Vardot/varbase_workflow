import type { LaunchOptions, BrowserContextOptions } from 'playwright';

type BrowserName = 'chromium' | 'firefox' | 'webkit';

const browser = (process.env.BROWSER || 'chromium') as BrowserName;

const chromiumArgs: string[] = [
  '--no-sandbox',
  '--disable-dev-shm-usage',
  '--disable-setuid-sandbox',
  '--disable-web-security',
  '--ignore-certificate-errors',
  '--disable-extensions',
  '--incognito',
  '--disable-infobars',
  '--window-size=1920,1080',
  '--force-device-scale-factor=1',
  '--disable-gpu',
  '--allow-insecure-localhost',
  '--no-first-run',
];

interface PlaywrightConfig {
  browser: BrowserName;
  launchOptions: LaunchOptions;
  contextOptions: BrowserContextOptions;
}

const config: PlaywrightConfig = {
  browser,
  launchOptions: {
    headless: true,
    slowMo: 300,
    args: browser === 'chromium' ? chromiumArgs : [],
  },
  contextOptions: {
    viewport: { width: 1920, height: 1080 },
    ignoreHTTPSErrors: true,
  },
};

export = config;
