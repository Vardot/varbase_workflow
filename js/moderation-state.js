/**
 * @file
 * Behaviors of Varbase Moderation State for state dropdown change.
 */

(function ($, _, Drupal) {
  Drupal.behaviors.varbaseModerationStateSync = {
    attach: function (context, settings) {
      const dropdown1 = $('[id="edit-moderation-state-0-state"]', context);
      const dropdown2 = $('[id="edit-moderation-state-0-state--2"]', context);

      if (dropdown1.length && dropdown2.length) {
        // Sync dropdown2 when dropdown1 changes.
        dropdown1.on('change', function () {
          dropdown2.val($(this).val()).trigger('change');
        });

        // Sync dropdown1 when dropdown2 changes.
        dropdown2.on('change', function () {
          dropdown1.val($(this).val()).trigger('change');
        });
      }
    }
  };
})(window.jQuery, window._, window.Drupal);
