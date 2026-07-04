<?php

declare(strict_types=1);

namespace Drupal\varbase_workflow\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Vardot\Entity\EntityDefinitionUpdateManager;

/**
 * Hook implementations for the Varbase Workflow module.
 */
class VarbaseWorkflowHooks {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * Constructs a VarbaseWorkflowHooks object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $classResolver
   *   The class resolver.
   */
  public function __construct(
    protected ConfigFactoryInterface $configFactory,
    protected ClassResolverInterface $classResolver,
  ) {}

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(array &$form, FormStateInterface $form_state, $form_id): void {

    if ($form_id == 'node_type_add_form' || $form_id == 'node_type_edit_form') {

      $default_workflow_option = '_none';
      // List of workflows.workflow.* config in the site.
      $content_moderation_workflow_options = ['_none' => $this->t('- none -')];
      $config_factory = $this->configFactory;
      $workflows = $config_factory->listAll('workflows.workflow.');
      foreach ($workflows as $workflow) {
        $content_moderation_workflow_options[$workflow] = $config_factory->getEditable($workflow)->get('label');
        if ($workflow == 'workflows.workflow.varbase_simple_workflow'
            && $default_workflow_option == '_none') {
          $default_workflow_option = 'workflows.workflow.varbase_simple_workflow';
        }
      }

      $workflows_configuration_page = Link::fromTextAndUrl($this->t('Workflows configuration page'), new Url('entity.workflow.collection'));
      $form['workflow']['content_moderation_workflow'] = [
        '#type' => 'select',
        '#title' => $this->t('Content moderation workflow'),
        '#default_value' => $default_workflow_option,
        '#options' => $content_moderation_workflow_options,
        '#description' => $this->t('Select the workflow you would like to use for this content type. Once selected, you can only change it for this content type from the @link.', ['@link' => $workflows_configuration_page->toString()]),
      ];

      if ($form_id == 'node_type_add_form') {
        foreach (array_keys($form['actions']) as $action) {
          if ($action != 'preview' && isset($form['actions'][$action]['#type']) && $form['actions'][$action]['#type'] === 'submit') {
            $form['actions'][$action]['#submit'][] = [$this, 'nodeTypeAddFormSubmit'];
          }
        }
      }
      else {
        $form['workflow']['content_moderation_workflow']['#attributes']['readonly'] = 'readonly';
        $form['workflow']['content_moderation_workflow']['#attributes']['disabled'] = 'disabled';

        $form['workflow']['content_moderation_workflow']['#default_value'] = '_none';
        foreach ($workflows as $workflow) {
          $workflow_type_settings = $config_factory->getEditable($workflow)->get('type_settings');
          $node_type = $form_state->getFormObject()->getEntity()->get('type');

          if (isset($workflow_type_settings['entity_types'])
            && isset($workflow_type_settings['entity_types']['node'])) {

            if (in_array($node_type, $workflow_type_settings['entity_types']['node'])) {
              $form['workflow']['content_moderation_workflow']['#default_value'] = $workflow;
            }
          }
        }
      }

    }
    elseif (preg_match('/^node_.*._form$/', $form_id) && isset($form['moderation_state'])) {

      if (isset($form['publish_state'])) {
        $form['publish_state']['#access'] = FALSE;
        $form['publish_state']['widget'][0]['#default_value'] = 'published';
      }

      if (isset($form['unpublish_state'])) {
        $form['unpublish_state']['#access'] = FALSE;
        $form['unpublish_state']['widget'][0]['#default_value'] = 'archived';
      }

    }
  }

  /**
   * Form submit callback for the node type add form.
   */
  public function nodeTypeAddFormSubmit(array &$form, FormStateInterface &$form_state): void {

    $node_type = $form_state->getFormObject()->getEntity()->get('type');
    $content_moderation_workflow = $form_state->getFormObject()->getEntity()->get('content_moderation_workflow');

    if (isset($content_moderation_workflow)
        && $content_moderation_workflow != ''
        && $content_moderation_workflow != '_none') {

      $config_factory = $this->configFactory;
      $workflow_type_settings = $config_factory->getEditable($content_moderation_workflow)->get('type_settings');

      if (isset($workflow_type_settings['entity_types'])) {
        if (isset($workflow_type_settings['entity_types']['node'])) {
          if (!in_array($node_type, $workflow_type_settings['entity_types']['node'])) {
            $workflow_type_settings['entity_types']['node'][] = $node_type;
            $config_factory->getEditable($content_moderation_workflow)->set('type_settings', $workflow_type_settings)->save(TRUE);
          }
        }
        else {
          $workflow_type_settings['entity_types']['node'] = [];
          $workflow_type_settings['entity_types']['node'][] = $node_type;
          $config_factory->getEditable($content_moderation_workflow)->set('type_settings', $workflow_type_settings)->save(TRUE);
        }

        // Entity updates to clear up any mismatched entity and/or field
        // definitions and fix changes were detected in the entity type and
        // field definitions.
        $this->classResolver
          ->getInstanceFromDefinition(EntityDefinitionUpdateManager::class)
          ->applyUpdates();

      }
    }
  }

}
