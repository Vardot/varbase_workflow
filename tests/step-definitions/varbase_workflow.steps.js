'use strict';

/**
 * @file
 * Custom step definitions for the Varbase Workflow test suite.
 *
 * Most of the suite reuses the step definitions that ship with webship-js
 * (navigation, web-first assertions, accessibility). Only a few module-specific
 * helpers live here: logging in as a named user from cucumber.js
 * worldParameters.users and dropping back to an anonymous session, because
 * webship-js does not ship a Drupal form-login step.
 */

const { Given } = require('@cucumber/cucumber');
const {
  friendly,
  gotoUrl,
  waitForPageLoad,
} = require('webship-js/tests/step-definitions/webship');

/**
 * Run a step body and rethrow any failure as a tester-friendly error.
 *
 * @param {Function} body
 *   Async function performing the step.
 * @param {string} message
 *   Human-readable description for failures.
 */
async function attempt(body, message) {
  try {
    await body();
  }
  catch (err) {
    throw friendly(message, err);
  }
}

/**
 * Log in as a named test user defined in cucumber.js worldParameters.users.
 *
 * Example: Given I am a logged in user with the "Webmaster" user
 */
Given(/^I am a logged in user with( the)*( username)* "([^"]*)?"( user)?$/, async function (theCase, usernameCase, key, userCase) {
  const users = this.parameters.users || {};
  if (!(key in users)) {
    throw new Error(`No user named "${key}" in cucumber.js worldParameters.users`);
  }
  const { username, password } = users[key];
  if (!username || !password) {
    throw new Error(`User "${key}" is missing username or password in worldParameters.users`);
  }
  await attempt(async () => {
    let loggedIn = false;
    for (let i = 0; i < 3 && !loggedIn; i++) {
      await this.context.clearCookies();
      await gotoUrl(this.page, `${this.parameters.launchUrl}/user/login`);
      await this.page.locator('#user-login-form #edit-name').fill(username);
      await this.page.locator('#user-login-form #edit-pass').fill(password);
      await Promise.all([
        this.page.waitForURL((url) => !/\/user\/login/.test(String(url)), { timeout: 30000 }).catch(() => {}),
        this.page.locator('#user-login-form #edit-submit').click(),
      ]);
      await waitForPageLoad(this.page, this.minWaitTime && this.minWaitTime.page);
      // Confirm the session by loading the account page.
      await gotoUrl(this.page, `${this.parameters.launchUrl}/user`);
      await waitForPageLoad(this.page, this.minWaitTime && this.minWaitTime.page);
      const denied = await this.page.locator('h1:has-text("Access denied")').count();
      loggedIn = denied === 0;
    }
    if (!loggedIn) {
      throw new Error(`Login did not establish a session for "${key}"`);
    }
  }, `Could not log in as "${key}"`);
});
