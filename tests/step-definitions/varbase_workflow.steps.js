'use strict';

/**
 * @file
 * Custom step definitions for the Varbase Search test suite.
 *
 * The suite reuses the step definitions that ship with webship-js (navigation,
 * forms, web-first assertions, accessibility). The only module-specific step is
 * logging in as a named user from cucumber.js worldParameters.users, because
 * webship-js does not ship a Drupal form-login step.
 */

const { Given, When } = require('@cucumber/cucumber');
const {
  friendly,
  gotoUrl,
  waitForPageLoad,
} = require('webship-js/tests/step-definitions/webship');

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
  try {
    await this.context.clearCookies();
    await gotoUrl(this.page, `${this.parameters.launchUrl}/user/login`);
    await this.page.locator('#user-login-form #edit-name').fill(username);
    await this.page.locator('#user-login-form #edit-pass').fill(password);
    await Promise.all([
      this.page.waitForURL((url) => !/\/user\/login/.test(String(url)), { timeout: 30000 }).catch(() => {}),
      this.page.locator('#user-login-form #edit-submit').click(),
    ]);
    await waitForPageLoad(this.page, this.minWaitTime && this.minWaitTime.page);
  }
  catch (err) {
    throw friendly(`Could not log in as "${key}"`, err);
  }
});

/**
 * Create and save a Carousel content block (Block description only; the
 * Carousel slides field is optional) and land on the confirmation.
 */
When(/^(?:I |we )?create a carousel block titled "([^"]*)"$/, async function (title) {
  await gotoUrl(this.page, `${this.parameters.launchUrl}/block/add/varbase_carousel_block`);
  await this.page.locator('#edit-info-0-value').fill(title);
  await this.page.locator('#edit-submit').click();
  await waitForPageLoad(this.page, this.minWaitTime && this.minWaitTime.page);
});
