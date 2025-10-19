/**
 * @file
 * Behaviors of Varbase Moderation State for state dropdown change.
 */

(function ($, _, Drupal) {
  Drupal.behaviors.varbaseModerationStateSync = {
    attach(context) {
      // Look for the two dropdowns that commonly appear
      const dropdown1 = $('[id="edit-moderation-state-0-state"]', context);
      const dropdown2 = $('[id="edit-moderation-state-0-state--2"]', context);

      // Only do something if both dropdowns exist
      if (dropdown1.length && dropdown2.length) {
        // When dropdown1 changes, update dropdown2
        dropdown1.on('change', function syncToDropdown2() {
          const selectedValue = $(this).val();
          dropdown2.val(selectedValue).trigger('change');
        });

        // When dropdown2 changes, update dropdown1
        dropdown2.on('change', function syncToDropdown1() {
          const selectedValue = $(this).val();
          dropdown1.val(selectedValue).trigger('change');
        });
      }
    },
  };
})(window.jQuery, window._, window.Drupal);
