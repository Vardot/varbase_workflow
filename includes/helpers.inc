<?php

/**
 * @file
 * Contains List of all varbase_workflow helpers.
 *
 * Add custom needed helper functions.
 */

use Drupal\access_unpublished\AccessUnpublished;
use Drupal\user\Entity\Role;

/**
 * Grant access unpublished permissions for entity types we care about.
 *
 * Reads the module config to see which entity types should get access
 * unpublished permissions for anonymous and authenticated users.
 */
function varbase_workflow__auto_grant_access_unpublished() {
  $vw_settings = \Drupal::service('config.factory')->get('varbase_workflow.settings')->get('limited_applicable_entity_types');
  $definitions = \Drupal::service('entity_type.manager')->getDefinitions();

  foreach ($definitions as $definition) {
    if (!empty($vw_settings[$definition->id()])
      && isset($vw_settings[$definition->id()])
      && $vw_settings[$definition->id()]) {

      // Grant new access unpublished permissions for anonymous and all
      // authenticated user roles.
      varbase_workflow__grant_access_unpublished_permissions('anonymous', $definition->id());
      varbase_workflow__grant_access_unpublished_permissions('authenticated', $definition->id());
    }
  }
}

/**
 * Grant Access Unpublished permissions for a user role.
 */
function varbase_workflow__grant_access_unpublished_permissions(string $user_role, string $definition_id = '', string $bundle = '') {
  if ($role = Role::load($user_role)) {

    $vw_settings = \Drupal::service('config.factory')->get('varbase_workflow.settings')->get('limited_applicable_entity_types');

    // Default Access Unpublished permissions.
    $permissions = [];
    $definitions = \Drupal::service('entity_type.manager')->getDefinitions();
    foreach ($definitions as $definition) {
      if (($definition_id == '' || $definition->id() == $definition_id)
        && \Drupal::moduleHandler()->moduleExists('access_unpublished')
        && AccessUnpublished::applicableEntityType($definition)
        && !empty($vw_settings[$definition_id])
        && isset($vw_settings[$definition_id])
        && $vw_settings[$definition_id]) {

        $permission = 'access_unpublished ' . $definition->id();
        if ($definition->get('bundle_entity_type')) {
          if ($bundle == '') {
            $bundles = \Drupal::service('entity_type.manager')->getStorage($definition->getBundleEntityType())->loadMultiple();
            foreach ($bundles as $bundle) {
              $permissions[] = $permission . ' ' . $bundle->id();
            }
          }
          else {
            $permissions[] = $permission . ' ' . $bundle;
          }

        }
        else {
          $permissions[] = $permission;
        }

      }
    }

    foreach ($permissions as $permission) {
      $role->grantPermission($permission);
    }

    $role->trustData()->save();

  }
}
