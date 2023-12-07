<?php

/**
 * @file
 * Contains varbase_workflow_update_10###(s) hook updates for 3.0.x.
 */

/**
 * Issue #3406041: Add Access Unpublished module.
 */
function varbase_workflow_update_103001() {
  if (!\Drupal::moduleHandler()->moduleExists('access_unpublished')) {

    // Install the Access Unpublished module.
    \Drupal::service('module_installer')->install(['access_unpublished'], FALSE);
  }

  // Grant new access unpublished permissions for the site admin user role.
  grant_access_unpublished_permissions('site_admin');
}
