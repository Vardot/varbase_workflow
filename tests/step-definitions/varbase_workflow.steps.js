'use strict';

/**
 * @file
 * Custom step definitions for the Varbase Search test suite.
 *
 * The suite reuses the step definitions that ship with varbase-e2e (navigation,
 * forms, web-first assertions, accessibility). The only module-specific step is
 * logging in as a named user from cucumber.js worldParameters.users, because
 * varbase-e2e does not ship a Drupal form-login step.
 */

const { When } = require('@cucumber/cucumber');
const { gotoUrl, waitForPageLoad } = require('@vardot/varbase-e2e/tests/step-definitions/varbase-e2e');

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
