<?php

/**
 * @file
 * Contains varbase_workflow_update_9###(s) hook updates for 2.0.x.
 */

use Vardot\Installer\ModuleInstallerFactory;

/**
 * Issue #3272302: Switch to use the Module Installer Factory.
 *
 * In the Varbase Workflow module.
 */
function varbase_workflow_update_90001() {
  // Set the weight of the module after installation of list of modules.
  // To make sure that any hook or event subscriber worker
  // after all used modules.
  ModuleInstallerFactory::setModuleWeightAfterInstallation('varbase_workflow');
}

/**
 * Issue #3364872: Fix No moderated content available.
 *
 * In the Moderated Content Admin View.
 */
function varbase_workflow_update_90002() {
  $moderated_content_config_factory = \Drupal::configFactory()->getEditable('views.view.moderated_content');
  $moderated_content_view_display = $moderated_content_config_factory->get('display');

  if (isset($moderated_content_view_display)
    && isset($moderated_content_view_display['default'])
    && isset($moderated_content_view_display['default']['display_options'])
    && isset($moderated_content_view_display['default']['display_options']['filters'])
    && isset($moderated_content_view_display['default']['display_options']['filters']['moderation_state_1'])
    && isset($moderated_content_view_display['default']['display_options']['filters']['moderation_state_1']['value'])
    && isset($moderated_content_view_display['default']['display_options']['filters']['moderation_state_1']['value']['editorial-published'])
    && count($moderated_content_view_display['default']['display_options']['filters']['moderation_state_1']['value']) == 1) {

    unset($moderated_content_view_display['default']['display_options']['filters']['moderation_state_1']['value']['editorial-published']);
    $moderated_content_view_display['default']['display_options']['filters']['moderation_state_1']['value']['varbase_editorial_workflow-published'] = 'varbase_editorial_workflow-published';
    $moderated_content_view_display['default']['display_options']['filters']['moderation_state_1']['value']['varbase_simple_workflow-published'] = 'varbase_simple_workflow-published';
    $moderated_content_config_factory->set('display', $moderated_content_view_display)->save(TRUE);
  }

}
