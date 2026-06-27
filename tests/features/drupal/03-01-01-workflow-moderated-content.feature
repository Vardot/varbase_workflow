@varbase_workflow @moderation
Feature: Varbase Workflow - moderated content
  As a content editor
  I want the moderated content overview

  Background:
    Given I am a logged in user with the "Webmaster" user

  Scenario: The moderated content overview is available
    When I go to "/admin/content/moderated"
    Then I should see "Moderated content"

  Scenario: The content moderation permissions are configurable
    When I go to "/admin/people/permissions/module/content_moderation"
    Then I should see "Content Moderation"
