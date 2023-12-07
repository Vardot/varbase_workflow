<?php

/**
 * @file
 * Contains List of all varbase_workflow helpers.
 *
 * Add custom needed helper functions.
 */

use Drupal\user\Entity\Role;
use Drupal\access_unpublished\AccessUnpublished;

/**
 * Grant Access Unpublished permissions for a user role.
 */
function grant_access_unpublished_permissions(string $user_role) {
  if ($site_admin_user_role = Role::load('site_admin')) {

    // Default Access Unpublished permissions.
    $permissions = [];
    $definitions = \Drupal::service('entity_type.manager')->getDefinitions();
    foreach ($definitions as $definition) {
      if (AccessUnpublished::applicableEntityType($definition)) {
        $permission = 'access_unpublished ' . $definition->id();
        if ($definition->get('bundle_entity_type')) {
          $bundles = \Drupal::service('entity_type.manager')->getStorage($definition->getBundleEntityType())->loadMultiple();
          foreach ($bundles as $bundle) {
            $permissions[] = $permission . ' ' . $bundle->id();
          }
        }
        else {
          $permissions[] = $permission;
        }

      }
    }

    foreach ($permissions as $permission) {
      $site_admin_user_role->grantPermission($permission);
    }

    $site_admin_user_role->trustData()->save();

  }
}
