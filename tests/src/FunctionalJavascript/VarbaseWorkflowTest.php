<?php

namespace Drupal\Tests\varbase_workflow\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Tests Varbase Workflow moderation test.
 *
 * @group varbase_workflow
 */
class VarbaseWorkflowTest extends WebDriverTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $profile = 'standard';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'olivero';

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'workflows',
    'content_moderation',
    'moderation_sidebar',
    'scheduler_content_moderation_integration',
    'admin_audit_trail_workflows',
    'varbase_workflow',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Insall the Claro admin theme.
    $this->container->get('theme_installer')->install(['claro']);

    // Set the Claro theme as the default admin theme.
    $this->config('system.theme')->set('admin', 'claro')->save();

    drupal_flush_all_caches();

  }

  /**
   * Check Varbase Workflow moderation.
   */
  public function testCheckVarbaseWorkflowModeration() {

    // Given that the root super user was logged in to the site.
    $this->drupalLogin($this->rootUser);

    $this->drupalGet('admin/config/workflow/workflows');
    $this->assertSession()->pageTextContains('Simple');
    $this->assertSession()->pageTextContains('Editorial');

    // Create a testing content type.
    $this->drupalGet('admin/structure/types/add');
    $this->assertSession()->pageTextContains('Add content type');

    $page = $this->getSession()->getPage();
    $page->fillField('name', 'Post');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $edit_type_button = $this->assertSession()->waitForElementVisible('css', '#edit-name-machine-name-suffix .link');
    $edit_type_button->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->fillField('type', 'post');

    $submit = $page->findButton('op');
    $submit->click();

    drupal_flush_all_caches();

    $this->drupalGet('admin/config/workflow/workflows/manage/varbase_editorial_workflow');
    $this->assertSession()->pageTextContains('Edit Editorial workflow');
    $this->assertSession()->pageTextContains('Draft');
    $this->assertSession()->pageTextContains('In review');
    $this->assertSession()->pageTextContains('Published');
    $this->assertSession()->pageTextContains('Archived / Unpublished');

    $this->assertSession()->pageTextContains('Create new draft');
    $this->assertSession()->pageTextContains('Send to review');
    $this->assertSession()->pageTextContains('Publish');
    $this->assertSession()->pageTextContains('Archive / Unpublish');
    $this->assertSession()->pageTextContains('Restore from archive');

    $this->assertSession()->pageTextNotContains('Post');

    $this->drupalGet('admin/config/workflow/workflows/manage/varbase_simple_workflow');
    $this->assertSession()->pageTextContains('Draft');
    $this->assertSession()->pageTextNotContains('In review');
    $this->assertSession()->pageTextContains('Published');
    $this->assertSession()->pageTextContains('Archived / Unpublished');

    $this->assertSession()->pageTextContains('Create new draft');
    $this->assertSession()->pageTextNotContains('Send to review');
    $this->assertSession()->pageTextContains('Publish');
    $this->assertSession()->pageTextContains('Archive / Unpublish');
    $this->assertSession()->pageTextContains('Restore from archive');

    $this->assertSession()->pageTextContains('Post');

  }

  /**
   * Check Varbase Workflow moderation sidebar.
   */
  public function testCheckVarbaseWorkflowModerationSidebar() {

    // Given that the root super user was logged in to the site.
    $this->drupalLogin($this->rootUser);

    // Create a testing content type.
    $this->drupalGet('admin/structure/types/add');
    $this->assertSession()->pageTextContains('Add content type');

    $page = $this->getSession()->getPage();
    $page->fillField('name', 'News');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $edit_type_button = $this->assertSession()->waitForElementVisible('css', '#edit-name-machine-name-suffix .link');
    $edit_type_button->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->fillField('type', 'news');

    $submit = $page->findButton('op');
    $submit->click();

    drupal_flush_all_caches();

    // Create a testing node.
    $this->drupalCreateNode([
      'title' => 'Test News Contnet',
      'type' => 'news',
      'body' => [
         [
          'value' => 'Test body for test news content.',
        ],
      ],
    ]);

    $this->drupalGet('admin/content');
    $this->assertSession()->pageTextContains('Test News Contnet');
    $this->clickLink('Test News Contnet');
    $this->assertSession()->pageTextContains('Tasks');

  }

}
