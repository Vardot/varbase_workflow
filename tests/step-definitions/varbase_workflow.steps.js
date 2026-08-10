'use strict';

/**
 * @file
 * Custom step definitions for the Varbase Workflow test suite.
 *
 * Most of the suite reuses the step definitions that ship with varbase-e2e
 * (navigation, web-first assertions, accessibility). Only a few module-specific
 * helpers live here: logging in as a named user from cucumber.js
 * worldParameters.users and dropping back to an anonymous session, because
 * varbase-e2e does not ship a Drupal form-login step.
 */

const { friendly } = require('@vardot/varbase-e2e/tests/step-definitions/varbase-e2e');

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


