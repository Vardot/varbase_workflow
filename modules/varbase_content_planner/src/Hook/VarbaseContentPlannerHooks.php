<?php

declare(strict_types=1);

namespace Drupal\varbase_content_planner\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\Element;
use Drupal\Core\Template\Attribute;

/**
 * Hook implementations for the Varbase Content Planner module.
 */
class VarbaseContentPlannerHooks {

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path): array {
    return [
      'varbase_item_list' => [
        'variables' => [
          'items' => [],
          'title' => '',
          'list_type' => 'ul',
          'wrapper_attributes' => [],
          'attributes' => [],
          'empty' => NULL,
          'context' => [],
        ],
      ],
    ];
  }

  /**
   * Implements hook_theme_suggestions_HOOK_alter().
   */
  #[Hook('theme_suggestions_item_list_alter')]
  public function themeSuggestionsItemListAlter(&$suggestions, $variables): void {
    if (isset($variables['items']['content_calendar']) || isset($variables['items']['content_kanban'])) {
      $suggestions[] = 'varbase_item_list';
    }
  }

  /**
   * Implements hook_preprocess_HOOK() for varbase_item_list.
   *
   * Default template: item-list.html.twig.
   */
  #[Hook('preprocess_varbase_item_list')]
  public function preprocessVarbaseItemList(array &$variables): void {
    $variables['wrapper_attributes'] = new Attribute($variables['wrapper_attributes']);
    foreach ($variables['items'] as &$item) {
      $attributes = [];
      // If the item value is an array, then it is a render array.
      if (is_array($item)) {
        // List items support attributes via the '#wrapper_attributes' property.
        if (isset($item['#wrapper_attributes'])) {
          $attributes = $item['#wrapper_attributes'];
        }
        // Determine whether there are any child elements in the item that are
        // not fully-specified render arrays. If there are any, then the child
        // elements present nested lists and we automatically inherit the render
        // array properties of the current list to them.
        foreach (Element::children($item) as $key) {
          $child = &$item[$key];
          // If this child element does not specify how it can be rendered, then
          // we need to inherit the render properties of the current list.
          if (!isset($child['#type']) && !isset($child['#theme']) && !isset($child['#markup'])) {
            // Since item-list.html.twig supports both strings and render arrays
            // as items, the items of the nested list may have been specified as
            // the child elements of the nested list, instead of #items. For
            // convenience, we automatically move them into #items.
            if (!isset($child['#items'])) {
              // This is the same condition as in
              // \Drupal\Core\Render\Element::children(), which cannot be
              // used here, since it triggers an error on string values.
              foreach ($child as $child_key => $child_value) {
                if (is_int($child_key) || $child_key === '' || $child_key[0] !== '#') {
                  $child['#items'][$child_key] = $child_value;
                  unset($child[$child_key]);
                }
              }
            }
            // Lastly, inherit the original theme variables of the current list.
            $child['#theme'] = $variables['theme_hook_original'];
            $child['#list_type'] = $variables['list_type'];
          }
        }
      }

      // Set the item's value and attributes for the template.
      $item = [
        'value' => $item,
        'attributes' => new Attribute($attributes),
      ];
    }
  }

}
