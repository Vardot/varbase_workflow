# Varbase Workflow

![Varbase Logo](logo.png)

This module gives you content workflow tools that work out of the box. It's
built on top of Drupal's Content Moderation module, so you get all the core
functionality plus some extra features that make workflows easier to use.

## What it does

Basically, this module sets up content workflows for you. Instead of having to
configure everything from scratch, you get two workflows ready to go, plus some
nice UI improvements and planning tools.

## The workflows you get

### Simple Workflow
**Draft → Published → Archived**

This is what most sites need. You write content, publish it, and archive it
when you're done. Nothing fancy, just works.

### Editorial Workflow  
**Draft → In Review → Published → Archived**

This one adds a review step. Good if you have editors who need to check
content before it goes live. You can add more states if you need them.

## What's included

- Two workflows set up and ready to use
- Some UI tweaks to make the moderation interface less confusing
- Permission setup for different user roles
- A content planning submodule (see below)
- Styling to make the workflow controls look decent

The content planner gives you:
- A dashboard showing what content is in what state
- A calendar view for scheduling content
- A kanban board (think Trello) for moving content through workflow states

## Getting started

Just install it like any other module:

```bash
composer require drupal/varbase_workflow
drush en varbase_workflow -y
```

## Setting it up

After you enable the module, you need to tell it which content types should
use workflows:

1. Go to `/admin/config/varbase/varbase-workflow`
2. Check off the content types you want to put through workflows
3. Set up permissions for your user roles
4. That's it

For each content type you want to moderate:
1. Edit the content type at **Structure > Content types**
2. Pick which workflow to use (Simple or Editorial)
3. Save it

The permissions are pretty straightforward - you can control who can create
content in different states, who can move content between states, and who can
see content in different states.

## How to use it

### If you're writing content
When you create content, you'll see a "Moderation state" field. Start with
"Draft" - your content won't be visible to the public until you change it to
"Published".

### If you're editing/reviewing content
You'll see buttons to move content between states. Content in "Draft" or "In
Review" won't be visible to site visitors. Click the buttons to move content
along.

### If you're managing a team
Check out the Content Planner submodule. It gives you a dashboard to see what
everyone's working on, a calendar for planning content, and a kanban board for
tracking progress.

## Requirements and dependencies

You need Drupal 11.2+ and PHP 8.1+. The module will pull in these other
modules automatically:
- Content Planner
- Scheduler  
- Scheduler Content Moderation Integration
- Content Moderation Notifications
- Admin Audit Trail
- Access Unpublished

## How it works

The module creates a settings form, adds some JavaScript to sync workflow
dropdowns when there are multiple on a page, and includes some CSS to make
everything look decent. It hooks into Drupal's form system to add workflow
options and integrates with the permission system.

## Making your own workflows

You can create additional workflows if the two provided ones don't fit your
needs. Just go to `/admin/config/workflow/workflows` and create a new one. You
can add whatever states and transitions you want.

If you need custom code to run when content moves between states, you can use
the standard Drupal hooks or create event subscribers.

## Common problems

**Workflow options not showing up?** Make sure you've configured the content
type to use a workflow.

**Can't move content between states?** Check the permissions - users need
specific permissions to transition content.

**Content not showing correct state?** Clear the cache, sometimes the
interface gets out of sync.

## Debugging

If you're having issues, you can enable debug mode by adding this to your
settings.php:
```php
$settings['varbase_workflow_debug'] = TRUE;
```

## Issues and contributions

File bugs or feature requests at https://www.drupal.org/project/issues/varbase_workflow

This module is maintained by [Vardot](https://www.drupal.org/vardot).
