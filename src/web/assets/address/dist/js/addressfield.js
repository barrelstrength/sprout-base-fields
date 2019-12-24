/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/web/assets/address/src/js/AddressBox.js":
/*!*****************************************************!*\
  !*** ./src/web/assets/address/src/js/AddressBox.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

/* global Craft */
if (_typeof(Craft.SproutBase) === ( true ? "undefined" : undefined)) {
  Craft.SproutBase = {};
}

(function ($) {
  // Set all the standard Craft.SproutBase.* stuff
  $.extend(Craft.SproutBase, {
    initFields: function initFields($container) {
      $('.sproutaddressinfo-box', $container).SproutAddressBox();
    }
  }); // -------------------------------------------
  //  Custom jQuery plugins
  // -------------------------------------------

  $.extend($.fn, {
    SproutAddressBox: function SproutAddressBox() {
      var $container = $(this);
      return this.each(function () {
        new Craft.SproutBase.AddressBox($container);
      });
    }
  });
  Craft.SproutBase.AddressBox = Garnish.Base.extend({
    $addressBox: null,
    $addButtons: null,
    $editButtons: null,
    $addressFormat: null,
    $addButton: null,
    $updateButton: null,
    $clearButton: null,
    $queryButton: null,
    addressId: null,
    addressInfo: null,
    $addressForm: null,
    countryCode: null,
    showAddressOnInitialLoad: null,
    $none: null,
    modal: null,
    init: function init($addressBox, settings) {
      this.$addressBox = $addressBox;
      this.$addButton = this.$addressBox.find('.address-add-button a');
      this.$updateButton = this.$addressBox.find('.address-edit-buttons a.update-button');
      this.$clearButton = this.$addressBox.find('.address-edit-buttons a.clear-button');
      this.$queryButton = $('.query-button');
      this.$addButtons = this.$addressBox.find('.address-add-button');
      this.$editButtons = this.$addressBox.find('.address-edit-buttons');
      this.$addressFormat = this.$addressBox.find('.address-format');
      this.settings = settings;

      if (this.settings.namespace == null) {
        this.settings.namespace = 'address';
      }

      this.addressId = this.$addressBox.data('addressId');
      this.showAddressOnInitialLoad = this.$addressBox.data('showAddressOnInitialLoad');
      this.renderAddress();
      this.addListener(this.$addButton, 'click', 'editAddressBox');
      this.addListener(this.$updateButton, 'click', 'editAddressBox');
      this.addListener(this.$clearButton, 'click', 'clearAddressBox');
      this.addListener(this.$queryButton, 'click', 'queryAddressCoordinatesFromGoogleMaps');
    },
    renderAddress: function renderAddress() {
      if (this.addressId || this.showAddressOnInitialLoad) {
        this.$addButtons.addClass('hidden');
        this.$editButtons.removeClass('hidden');
        this.$addressFormat.removeClass('hidden');
      } else {
        this.$addButtons.removeClass('hidden');
        this.$editButtons.addClass('hidden');
        this.$addressFormat.addClass('hidden');
      }

      this.$addressForm = this.$addressBox.find('.sproutfields-address-formfields');
      this.getAddressFormFieldsHtml();
    },
    editAddressBox: function editAddressBox(event) {
      event.preventDefault();
      this.$target = $(event.currentTarget);
      var countryCode = this.$addressForm.find('.sprout-address-country-select select').val();
      this.modal = new Craft.SproutBase.EditAddressModal(this.$addressForm, {
        onSubmit: $.proxy(this, 'getAddressDisplayHtml'),
        countryCode: countryCode,
        namespace: this.settings.namespace
      }, this.$target);
    },
    getAddressDisplayHtml: function getAddressDisplayHtml(data) {
      var self = this;
      /**
       * @param {string} response.countryCodeHtml
       * @param {string} response.addressFormHtml
       * @param {Array} response.errors
       */

      Craft.postActionRequest('sprout-base-fields/fields-address/get-address-display-html', data, $.proxy(function (response) {
        if (response.result === true) {
          this.$addressBox.find('.address-format').html(response.html);
          self.$addressForm.empty();
          self.$addressForm.append(response.countryCodeHtml);
          self.$addressForm.append(response.addressFormHtml);
          self.$addButtons.addClass('hidden');
          self.$editButtons.removeClass('hidden');
          self.$addressFormat.removeClass('hidden');
          this.modal.hide();
          this.modal.destroy();
        } else {
          Garnish.shake(this.modal.$form);
          var errors = response.errors;
          $.each(errors, function (key, value) {
            $.each(value, function (key2, value2) {
              Craft.cp.displayError(Craft.t('sprout-base-fields', value2));
            });
          });
        }
      }, this), []);
    },
    // Called on renderAddress and clearAddress
    getAddressFormFieldsHtml: function getAddressFormFieldsHtml() {
      var self = this;
      var addressId = this.$addressBox.data('addressId');
      var defaultCountryCode = this.$addressBox.data('defaultCountryCode');
      var addressJson = this.$addressBox.data('addressJson');
      var actionUrl = 'sprout-base-fields/fields-address/get-address-form-fields-html';
      var formFieldHtmlActionUrl = this.$addressBox.data('formFieldHtmlActionUrl'); // Give integrations like Sprout SEO a chance to retrieve the address in a different way

      if (formFieldHtmlActionUrl) {
        actionUrl = formFieldHtmlActionUrl;
      }

      Craft.postActionRequest(actionUrl, {
        addressId: addressId,
        defaultCountryCode: defaultCountryCode,
        addressJson: addressJson
      }, $.proxy(function (response) {
        this.$addressBox.find('.address-format .spinner').remove();
        self.$addressBox.find('.address-format').empty();
        self.$addressBox.find('.address-format').append(response.html);
      }, this), []);
    },
    clearAddressBox: function clearAddressBox(event) {
      event.preventDefault();
      var self = this;
      this.$addButtons.removeClass('hidden');
      this.$editButtons.addClass('hidden');
      this.$addressFormat.addClass('hidden');
      this.$addressForm.find("[name='" + this.settings.namespace + "[delete]']").val(1);
      self.addressId = null;
      this.$addressBox.find('.sprout-address-onchange-country').remove();
      this.emptyForm();
      this.getAddressFormFieldsHtml();
    },
    emptyForm: function emptyForm() {
      var formKeys = ['countryCode', 'administrativeArea', 'locality', 'dependentLocality', 'postalCode', 'sortingCode', 'address1', 'address2'];
      var self = this;
      $.each(formKeys, function (index, el) {
        self.$addressBox.find("[name='" + self.settings.namespace + "[" + el + "]']").attr('value', '');
      });
    },
    queryAddressCoordinatesFromGoogleMaps: function queryAddressCoordinatesFromGoogleMaps(event) {
      event.preventDefault();
      var self = this;
      var spanValues = [];
      var $addressFormat = $(".address-format");
      $addressFormat.each(function () {
        spanValues.push($(this).text());
      });
      self.addressInfo = spanValues.join("|");

      if ($addressFormat.is(':hidden')) {
        Craft.cp.displayError(Craft.t('sprout-base-fields', 'Please add an address'));
        return false;
      }

      var data = {
        addressInfo: self.addressInfo
      };
      /**
       * @param {JSON} response[].geo
       * @param {Array} response.errors
       */

      Craft.postActionRequest('sprout-base-fields/fields-address/query-address-coordinates-from-google-maps', data, $.proxy(function (response) {
        if (response.result === true) {
          var latitude = response.geo.latitude;
          var longitude = response.geo.longitude; // @todo - add generic name?

          $("input[name='sproutseo[globals][identity][latitude]']").val(latitude);
          $("input[name='sproutseo[globals][identity][longitude]']").val(longitude);
          Craft.cp.displayNotice(Craft.t('sprout-base-fields', 'Latitude and Longitude updated.'));
        } else {
          Craft.cp.displayError(Craft.t('sprout-base-fields', 'Unable to find the address: ' + response.errors));
        }
      }, this), []);
    }
  });
})(jQuery);

/***/ }),

/***/ "./src/web/assets/address/src/js/EditAddressModal.js":
/*!***********************************************************!*\
  !*** ./src/web/assets/address/src/js/EditAddressModal.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

/* global Craft */
if (_typeof(Craft.SproutBase) === ( true ? "undefined" : undefined)) {
  Craft.SproutBase = {};
}

Craft.SproutBase.EditAddressModal = Garnish.Modal.extend({
  id: null,
  init: function init($addressForm, settings) {
    this.setSettings(settings, Garnish.Modal.defaults);
    this.$form = $('<form class="sprout-address-modal modal fitted" method="post" accept-charset="UTF-8"/>').appendTo(Garnish.$bod);
    this.$body = $('<div class="body sprout-address-body"></div>').appendTo(this.$form);
    this.$bodyMeta = $('<div class="meta"></div>').appendTo(this.$body);
    this.$addressForm = $addressForm;
    this.$addressFormHtml = $addressForm.html();
    $(this.$addressFormHtml).appendTo(this.$bodyMeta);
    this.modalTitle = Craft.t('sprout-base-fields', 'Update Address');
    this.submitLabel = Craft.t('sprout-base-fields', 'Update'); // Footer and buttons

    var $footer = $('<div class="footer"/>').appendTo(this.$form);
    var $btnGroup = $('<div class="btngroup left"/>').appendTo($footer);
    var $mainBtnGroup = $('<div class="btngroup right"/>').appendTo($footer);
    this.$updateBtn = $('<input type="button" class="btn submit" value="' + this.submitLabel + '"/>').appendTo($mainBtnGroup);
    this.$footerSpinner = $('<div class="spinner right hidden"/>').appendTo($footer);
    this.$cancelBtn = $('<input type="button" class="btn" value="' + Craft.t('sprout-base-fields', 'Cancel') + '"/>').appendTo($btnGroup);
    this.addListener(this.$cancelBtn, 'click', 'hide');
    this.addListener(this.$updateBtn, 'click', $.proxy(function (ev) {
      ev.preventDefault();
      this.updateAddress();
    }, this));
    this.addListener('.sprout-address-country-select select', 'change', function (ev) {
      this.changeFormInput(ev.currentTarget);
    }); // Select the country dropdown again for some reason it does not get right value at the form box

    var $countrySelectField = this.$form.find(".sprout-address-country-select select");
    $countrySelectField.val(this.settings.countryCode); // And trigger the onchange event manually to ensure the form displays after values have been cleared

    if (this.$form.find(".sprout-address-delete").val()) {
      $countrySelectField.change();
    }

    this.base(this.$form, settings);
  },
  changeFormInput: function changeFormInput(target) {
    var $target = $(target);
    var countryCode = $(target).val();
    var $parents = $target.parents('.sprout-address-body');
    var addressId = this.$addressForm.find('.sprout-address-id').val();
    var fieldId = this.$addressForm.find('.sprout-address-field-id').val();
    Craft.postActionRequest('sprout-base-fields/fields-address/update-address-form-html', {
      addressId: addressId,
      fieldId: fieldId,
      countryCode: countryCode,
      namespace: this.settings.namespace
    }, $.proxy(function (response) {
      // Cleanup some duplicate fields because the country dropdown is already on the page
      // @todo - refactor how this HTML is built so Country Dropdown we don't need to use sleight of hand like this
      $parents.find('.sprout-address-onchange-country').remove();
      $parents.find('.sprout-address-delete').first().remove();
      $parents.find('.sprout-address-field-id').first().remove();
      $parents.find('.sprout-address-id').first().remove();

      if (response.html) {
        $parents.find('.meta').append(response.html);
      }
    }, this));
  },
  enableUpdateBtn: function enableUpdateBtn() {
    this.$updateBtn.removeClass('disabled');
  },
  disableUpdateBtn: function disableUpdateBtn() {
    this.$updateBtn.addClass('disabled');
  },
  showFooterSpinner: function showFooterSpinner() {
    this.$footerSpinner.removeClass('hidden');
  },
  hideFooterSpinner: function hideFooterSpinner() {
    this.$footerSpinner.addClass('hidden');
  },
  updateAddress: function updateAddress() {
    var namespace = this.settings.namespace;
    var formKeys = ['id', 'fieldId', 'countryCode', 'administrativeAreaCode', 'locality', 'dependentLocality', 'postalCode', 'sortingCode', 'address1', 'address2'];
    var formValues = {};
    var self = this;
    $.each(formKeys, function (index, el) {
      formValues[el] = self.$form.find("[name='" + namespace + "[" + el + "]']").val();
    });
    var data = {
      formValues: formValues
    };
    data.namespace = this.settings.namespace;
    this.settings.onSubmit(data, $.proxy(function (errors) {
      $.each(errors, function (index, val) {
        var errorHtml = "<ul class='errors'>";
        var $element = self.$form.find("[name='" + namespace + "[" + index + "]']");
        $element.parent().addClass('errors');
        errorHtml += "<li>" + val + "</li>";
        errorHtml += "</ul>";

        if ($element.parent().find('.errors') != null) {
          $element.parent().find('.errors').remove();
        }

        $element.after(errorHtml);
      });
    }));
  },
  defaults: {
    onSubmit: $.noop,
    onUpdate: $.noop
  }
});

/***/ }),

/***/ "./src/web/assets/address/src/scss/addressfield.scss":
/*!***********************************************************!*\
  !*** ./src/web/assets/address/src/scss/addressfield.scss ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./src/web/assets/email/src/scss/emailfield.scss":
/*!*******************************************************!*\
  !*** ./src/web/assets/email/src/scss/emailfield.scss ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./src/web/assets/phone/src/scss/phonefield.scss":
/*!*******************************************************!*\
  !*** ./src/web/assets/phone/src/scss/phonefield.scss ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./src/web/assets/url/src/scss/urlfield.scss":
/*!***************************************************!*\
  !*** ./src/web/assets/url/src/scss/urlfield.scss ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 0:
/*!***************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** multi ./src/web/assets/address/src/js/AddressBox.js ./src/web/assets/address/src/js/EditAddressModal.js ./src/web/assets/address/src/scss/addressfield.scss ./src/web/assets/email/src/scss/emailfield.scss ./src/web/assets/phone/src/scss/phonefield.scss ./src/web/assets/url/src/scss/urlfield.scss ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! /var/www/html/plugins/sprout-base-fields/src/web/assets/address/src/js/AddressBox.js */"./src/web/assets/address/src/js/AddressBox.js");
__webpack_require__(/*! /var/www/html/plugins/sprout-base-fields/src/web/assets/address/src/js/EditAddressModal.js */"./src/web/assets/address/src/js/EditAddressModal.js");
__webpack_require__(/*! /var/www/html/plugins/sprout-base-fields/src/web/assets/address/src/scss/addressfield.scss */"./src/web/assets/address/src/scss/addressfield.scss");
__webpack_require__(/*! /var/www/html/plugins/sprout-base-fields/src/web/assets/email/src/scss/emailfield.scss */"./src/web/assets/email/src/scss/emailfield.scss");
__webpack_require__(/*! /var/www/html/plugins/sprout-base-fields/src/web/assets/phone/src/scss/phonefield.scss */"./src/web/assets/phone/src/scss/phonefield.scss");
module.exports = __webpack_require__(/*! /var/www/html/plugins/sprout-base-fields/src/web/assets/url/src/scss/urlfield.scss */"./src/web/assets/url/src/scss/urlfield.scss");


/***/ })

/******/ });