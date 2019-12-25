/* global Craft */

if (typeof SproutUrlField === typeof undefined) {
  SproutUrlField = {};
}

SproutUrlField = Garnish.Base.extend({

  init: function(namespaceInputId, id, fieldHandle, fieldContext) {
    this.checkSproutUrlField(namespaceInputId, id, fieldHandle, fieldContext);
  },

  checkSproutUrlField: function(namespaceInputId, id, fieldHandle, fieldContext) {

    let sproutUrlFieldId = '#' + namespaceInputId;
    let sproutUrlButtonClass = '.' + id;

    // We use setTimeout to make sure our function works every time
    setTimeout(function() {
      // Set up data for the controller.
      let data = {
        'fieldHandle': fieldHandle,
        'fieldContext': fieldContext,
        'value': $(sproutUrlFieldId).val()
      };

      // Query the controller so the regex validation is all done through PHP.
      Craft.postActionRequest('sprout-base-fields/fields/url-validate', data, function(response) {
        if (response) {
          $(sproutUrlButtonClass).addClass('fade');
          $(sproutUrlButtonClass + ' a').attr("href", data.value);
        } else {
          $(sproutUrlButtonClass).removeClass('fade');
        }
      }, []);

    }, 500);
  }
});