// Cucumber-js config for the Varbase Workflow varbase-e2e suite.
//
// Runs the Drupal Standard profile feature set (Olivero front end, Claro
// admin theme) under tests/features/drupal/.
//
//   npx cucumber-js --config cucumber.js
//
// Reusable step definitions ship with varbase-e2e; the module-specific steps
// live in tests/step-definitions/varbase_workflow.steps.js. Every worldParameter
// (users, selectors, screenshot, video, javascript, minWaitTime, launchUrl)
// is inlined below - this single file is the whole configuration. Artefacts
// land directly under tests/reports, tests/screenshots and tests/videos.

module.exports = {
  default: {
    timeout: 120000,
    requireModule: ['tsx/cjs'],
    require: [
      'node_modules/@vardot/varbase-e2e/tests/step-definitions/**/*.js',
      'tests/step-definitions/**/*.js',
    ],
    paths: ['tests/features/drupal/**/*.feature'],
    format: [
      '@cucumber/pretty-formatter',
      'json:tests/reports/cucumber_report.json',
    ],
    worldParameters: {
      launchUrl: process.env.LAUNCH_URL || 'http://localhost',
      // Test users. The Webmaster row is the `drush site:install` super-admin
      // created with `--account-name=webmaster --account-pass=...`.
      users: {
        'Webmaster': {
          username: 'webmaster',
          email: 'webmaster@example.test',
          password: 'dD.123123ddd',
          isAdmin: true,
        },
      },
      minWaitTime: {
        page: 10000,
        before_scenario: 0,
        after_scenario: 0,
        before_step: 0,
        after_step: 0,
      },
      selectors: {
        css: {},
        xpath: {},
        filesPath: './tests/selectors/',
        files: [
          'cms-drupal-core-claro.json',
          'varbase_workflow.json',
        ],
        offset: 60,
        breakpoints: {
          xs:  { width: 375,  height: 667  },
          sm:  { width: 576,  height: 800  },
          md:  { width: 768,  height: 1024 },
          lg:  { width: 992,  height: 768  },
          xl:  { width: 1200, height: 900 },
          xxl: { width: 1400, height: 900 },
          xxxl: { width: 1920, height: 1080, default: true },
        },
      },
      screenshot: {
        dir: './tests/screenshots',
        purge: false,
        onFailed: true,
        onEveryStep: false,
        alwaysFullscreen: false,
        failedPrefix: 'failed_',
        filenamePattern: '{datetime}.{feature_file}.feature_{step_line}.{ext}',
        filenamePatternFailed: '{failed_prefix}{datetime}.{feature_file}.feature_{step_line}.{ext}',
        infoTypes: '',
      },
      video: {
        mode: process.env.VARBASE_E2E_VIDEO || 'on-failure',
        dir: './tests/videos',
        size: { width: 1920, height: 1080 },
        filenamePattern: '{datetime}.{feature_file}.{scenario}.{status}.{ext}',
      },
      javascript: {
        mode: process.env.VARBASE_E2E_JS_ERROR_MODE || 'warn',
        levels: ['error'],
        ignore: '',
        beforeScenario: false,
        afterScenario: true,
      },
    },
  },
};
