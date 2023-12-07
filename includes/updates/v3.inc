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

  // Grant the Site Admin user role with generate token permissions.
  user_role_grant_permissions('site_admin', [
    'access tokens overview',
    'renew token',
    'delete token',
    ]);

  // Auto grant access unpublished for limited applicable entity types.
  varbase_workflow__auto_grant_access_unpublished();
}
