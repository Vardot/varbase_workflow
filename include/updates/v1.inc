<?php

/**
 * @file
 * Contains varbase_workflow_update_8###(s) hook updates for 1.0.x.
 */

/**
 * Issue #3157689: Fix default revision value for [ In review ] state to false.
 *
 * For the Editorial workflow.
 */
function varbase_workflow_update_82001() {
  if (\Drupal::moduleHandler()->moduleExists('content_moderation')) {
    $config_factory = \Drupal::service('config.factory')->getEditable('workflows.workflow.varbase_editorial_workflow');
    $type_settings = $config_factory->get('type_settings');
    if (isset($type_settings)
      && !empty($type_settings)
      && isset($type_settings['states'])
      && isset($type_settings['states']['review'])
      && isset($type_settings['states']['review']['default_revision'])) {

      $type_settings['states']['review']['default_revision'] = FALSE;
      $config_factory->set('type_settings', $type_settings)->save(TRUE);
    }
  }
}
