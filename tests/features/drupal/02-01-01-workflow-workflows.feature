@varbase_workflow @workflows
Feature: Varbase Workflow - content moderation workflows
  As a content administrator
  I want the Varbase Simple and Editorial content moderation workflows
  So that I have an easy publishing workflow with revisions
  (ported from the Varbase profile 05-07-content-workflows)

  Background:
    Given I am a logged in user with the "Webmaster" user

  Scenario: The workflows list shows the Varbase content moderation workflows
    When I go to "/admin/config/workflow/workflows"
    Then I should see "Editorial"
    And I should see "Simple"
    And I should see "Content moderation"

  Scenario: The Simple workflow has its states and transitions
    When I go to "/admin/config/workflow/workflows/manage/varbase_simple_workflow"
    Then I should see "Simple"
    And I should see "Draft"
    And I should see "Published"
    And I should see "Archived / Unpublished"
    And I should see "Create new draft"
    And I should see "Publish"
    And I should see "Archive / Unpublish"
    And I should see "Restore from archive"

  Scenario: The Editorial workflow has its states and transitions
    When I go to "/admin/config/workflow/workflows/manage/varbase_editorial_workflow"
    Then I should see "Editorial"
    And I should see "Draft"
    And I should see "In review"
    And I should see "Published"
    And I should see "Archived / Unpublished"
    And I should see "Create new draft"
    And I should see "Send to review"
    And I should see "Publish"
    And I should see "Archive / Unpublish"
    And I should see "Restore from archive"
