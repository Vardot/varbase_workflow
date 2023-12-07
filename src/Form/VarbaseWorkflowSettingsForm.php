<?php

namespace Drupal\varbase_workflow\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\user\Entity\Role;
use Drupal\access_unpublished\AccessUnpublished;

/**
 * Varbase Workflow Settings Form Class.
 */
class VarbaseWorkflowSettingsForm extends ConfigFormBase {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type bundle information service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $bundleInfo;

  /**
   * The class resolver.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  protected $classResolver;

  /**
   * Constructs a new Varbase Workflow Block.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $class_resolver
   *   (optional) The class resolver.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $bundle_info, ClassResolverInterface $class_resolver) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_type_manager;
    $this->bundleInfo = $bundle_info;
    $this->classResolver = $class_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('class_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'varbase_workflow_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('varbase_workflow.settings');

    $definitions = $this->entityTypeManager->getDefinitions();
    $vw_settings = $config->get('limited_applicable_entity_types');

    $form['limited_applicable_entity_types'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Limit Applicable Entity Types for Access Unpublished'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#tree' => TRUE,
      '#description' => $this->t('Select applicable Entity type to auto grant new access unpublished permissions for anonymous and all authenticated user roles for these entity types.'),
    ];

    foreach ($definitions as $definition) {
      if (AccessUnpublished::applicableEntityType($definition)) {
        $form['limited_applicable_entity_types'][$definition->id()] = [
          '#type' => 'checkbox',
          '#title' => $definition->getLabel(),
          '#default_value' => !empty($vw_settings[$definition->id()]) ?? [],
        ];
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Entity types which are going to use the varbase Workflow.
    $this->config('varbase_workflow.settings')
      ->set('limited_applicable_entity_types', $form_state->getValue('limited_applicable_entity_types'))
      ->save();

    $this->autoGrantLimitedApplicableEntityTypes();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['varbase_workflow.settings'];
  }

  /**
   * Auto grant limited applicable entity types.
   */
  public function autoGrantLimitedApplicableEntityTypes() {
    $vw_settings = $this->config('varbase_workflow.settings')->get('limited_applicable_entity_types');
    $definitions = $this->entityTypeManager->getDefinitions();

    foreach ($definitions as $definition) {
      if (!empty($vw_settings[$definition->id()])
        && isset($vw_settings[$definition->id()])
        && $vw_settings[$definition->id()]) {

        // Grant new access unpublished permissions for anonymous and all authenticated user roles
        $this->grantAccessUnpublishedPermissions('anonymous', $definition->id());
        $this->grantAccessUnpublishedPermissions('authenticated', $definition->id());
      }
    }
  }

  /**
   * Grant Access Unpublished permissions for a user role.
   */
  public function grantAccessUnpublishedPermissions(string $userRole, string $definitionId = '') {
    if ($role = Role::load($userRole)) {

      // Default Access Unpublished permissions.
      $permissions = [];
      $definitions = \Drupal::service('entity_type.manager')->getDefinitions();
      foreach ($definitions as $definition) {
        if (($definitionId == '' || $definition->id() == $definitionId)
          && AccessUnpublished::applicableEntityType($definition)) {

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
        $role->grantPermission($permission);
      }

      $role->trustData()->save();

    }
  }

}
