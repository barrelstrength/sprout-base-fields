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
/******/ 	return __webpack_require__(__webpack_require__.s = 4);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/web/assets/selectother/src/js/EditableTable.js":
/*!************************************************************!*\
  !*** ./src/web/assets/selectother/src/js/EditableTable.js ***!
  \************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

/* global Craft */
if (_typeof(Craft.SproutSeo) === ( true ? "undefined" : undefined)) {
  Craft.SproutSeo = {};
}
/**
 * Editable table class
 * @todo - move to Sprout SEO
 */


Craft.SproutSeo.EditableTable = Garnish.Base.extend({
  initialized: false,
  id: null,
  baseName: null,
  columns: null,
  sorter: null,
  biggestId: -1,
  $table: null,
  $tbody: null,
  $addRowBtn: null,
  init: function init(id, baseName, columns, settings) {
    this.id = id;
    this.baseName = baseName;
    this.columns = columns;
    this.setSettings(settings, Craft.SproutSeo.EditableTable.defaults);
    this.$table = $('#' + id);
    this.$tbody = this.$table.children('tbody');
    this.sorter = new Craft.DataTableSorter(this.$table, {
      helperClass: 'editabletablesorthelper',
      copyDraggeeInputValuesToHelper: true
    });

    if (this.isVisible()) {
      this.initialize();
    } else {
      this.addListener(Garnish.$win, 'resize', 'initializeIfVisible');
    }
  },
  isVisible: function isVisible() {
    return this.$table.height() > 0;
  },
  initialize: function initialize() {
    if (this.initialized) {
      return;
    }

    this.initialized = true;
    this.removeListener(Garnish.$win, 'resize');
    var $rows = this.$tbody.children();

    for (var i = 0; i < $rows.length; i++) {
      new Craft.SproutSeo.EditableTable.Row(this, $rows[i]);
    }

    this.$addRowBtn = this.$table.next('.buttons').children('.add');
    this.addListener(this.$addRowBtn, 'activate', 'addRow');
  },
  initializeIfVisible: function initializeIfVisible() {
    if (this.isVisible()) {
      this.initialize();
    }
  },
  addRow: function addRow() {
    var rowId = this.settings.rowIdPrefix + (this.biggestId + 1),
        rowHtml = Craft.SproutSeo.EditableTable.getRowHtml(rowId, this.columns, this.baseName, {}),
        $tr = $(rowHtml).appendTo(this.$tbody);
    new Craft.SproutSeo.EditableTable.Row(this, $tr);
    this.sorter.addItems($tr); // Focus the first input in the row

    $tr.find('input,textarea,select').first().focus();
    this.settings.onAddRow($tr);
  }
}, {
  textualColTypes: ['singleline', 'multiline', 'number'],
  defaults: {
    rowIdPrefix: '',
    onAddRow: $.noop,
    onDeleteRow: $.noop
  },
  getRowHtml: function getRowHtml(rowId, columns, baseName, values) {
    var rowHtml = '<tr data-id="' + rowId + '">';

    for (var colId in columns) {
      var col = columns[colId],
          name = baseName + '[' + rowId + '][' + colId + ']',
          value = typeof values[colId] !== 'undefined' ? values[colId] : '',
          textual = Craft.inArray(col.type, Craft.SproutSeo.EditableTable.textualColTypes);
      rowHtml += '<td class="' + (textual ? 'textual' : '') + ' ' + (typeof col['class'] !== 'undefined' ? col['class'] : '') + '"' + (typeof col['width'] !== 'undefined' ? ' width="' + col['width'] + '"' : '') + '>';

      switch (col.type) {
        case 'selectOther':
          {
            var isOwnership = baseName.indexOf('ownership') > -1;

            if (isOwnership) {
              rowHtml += '<div class="field sprout-selectother"><div class="select sprout-selectotherdropdown"><select onchange="setDefault(this)" name="' + name + '">';
            } else {
              rowHtml += '<div class="field sprout-selectother"><div class="select sprout-selectotherdropdown"><select name="' + name + '">';
            }

            var hasOptgroups = false;
            var firstRow = 'disabled selected';

            for (var key in col.options) {
              var option = col.options[key];

              if (typeof option.optgroup !== 'undefined') {
                if (hasOptgroups) {
                  rowHtml += '</optgroup>';
                } else {
                  hasOptgroups = true;
                }

                rowHtml += '<optgroup label="' + option.optgroup + '">';
              } else {
                var optionLabel = typeof option.label !== 'undefined' ? option.label : option,
                    optionValue = typeof option.value !== 'undefined' ? option.value : key,
                    optionDisabled = typeof option.disabled !== 'undefined' ? option.disabled : false;
                rowHtml += '<option ' + firstRow + ' value="' + optionValue + '"' + (optionValue === value ? ' selected' : '') + (optionDisabled ? ' disabled' : '') + '>' + optionLabel + '</option>';
              }

              firstRow = '';
            }

            if (hasOptgroups) {
              rowHtml += '</optgroup>';
            }

            rowHtml += '</select></div>';
            rowHtml += '<div class="texticon clearable sprout-selectothertext hidden"><input class="text fullwidth" type="text" name="' + name + '" value="" autocomplete="off"><div class="clear" title="Clear"></div></div>';
            rowHtml += '</div>';
            break;
          }

        case 'checkbox':
          {
            rowHtml += '<input type="hidden" name="' + name + '">' + '<input type="checkbox" name="' + name + '" value="1"' + (value ? ' checked' : '') + '>';
            break;
          }

        default:
          {
            rowHtml += '<input class="text fullwidth" type="text" name="' + name + '" value="' + value + '">';
          }
      }

      rowHtml += '</td>';
    }

    rowHtml += '<td class="thin action"><a class="move icon" title="' + Craft.t('sprout-base-fields', 'Reorder') + '"></a></td>' + '<td class="thin action"><a class="delete icon" title="' + Craft.t('sprout-base-fields', 'Delete') + '"></a></td>' + '</tr>';
    return rowHtml;
  }
});
/**
 * Editable table row class
 */

Craft.SproutSeo.EditableTable.Row = Garnish.Base.extend({
  table: null,
  id: null,
  niceTexts: null,
  $tr: null,
  $tds: null,
  $textareas: null,
  $deleteBtn: null,
  init: function init(table, tr) {
    this.table = table;
    this.$tr = $(tr);
    this.$tds = this.$tr.children(); // Get the row ID, sans prefix

    var id = parseInt(this.$tr.attr('data-id').substr(this.table.settings.rowIdPrefix.length));

    if (id > this.table.biggestId) {
      this.table.biggestId = id;
    }

    this.$textareas = $();
    this.niceTexts = [];
    var textareasByColId = {};
    var i = 0;

    for (var colId in this.table.columns) {
      var col = this.table.columns[colId];

      if (Craft.inArray(col.type, Craft.SproutSeo.EditableTable.textualColTypes)) {
        var $textarea = $('textarea', this.$tds[i]);
        this.$textareas = this.$textareas.add($textarea);
        this.addListener($textarea, 'focus', 'onTextareaFocus');
        this.addListener($textarea, 'mousedown', 'ignoreNextTextareaFocus');
        this.niceTexts.push(new Garnish.NiceText($textarea, {
          onHeightChange: $.proxy(this, 'onTextareaHeightChange')
        }));

        if (col.type === 'singleline' || col.type === 'number') {
          this.addListener($textarea, 'keypress', {
            type: col.type
          }, 'validateKeypress');
          this.addListener($textarea, 'textchange', {
            type: col.type
          }, 'validateValue');
        }

        textareasByColId[colId] = $textarea;
      }

      i++;
    }

    this.initSproutFields(); // Now that all of the text cells have been nice-ified, let's normalize the heights

    this.onTextareaHeightChange(); // Now look for any autopopulate columns

    for (var _colId in this.table.columns) {
      /**
       * @param {boolean} col.autopopulate
       */
      var _col = this.table.columns[_colId];

      if (_col.autopopulate && typeof textareasByColId[_col.autopopulate] !== 'undefined' && !textareasByColId[_colId].val()) {
        new Craft.HandleGenerator(textareasByColId[_colId], textareasByColId[_col.autopopulate]);
      }
    }

    var $deleteBtn = this.$tr.children().last().find('.delete');
    this.addListener($deleteBtn, 'click', 'deleteRow');
  },
  initSproutFields: function initSproutFields() {
    Craft.SproutFields.initFields(this.$tr);
  },
  onTextareaFocus: function onTextareaFocus(ev) {
    this.onTextareaHeightChange();
    var $textarea = $(ev.currentTarget);

    if ($textarea.data('ignoreNextFocus')) {
      $textarea.data('ignoreNextFocus', false);
      return;
    }

    setTimeout(function () {
      var val = $textarea.val(); // Does the browser support setSelectionRange()?

      if (typeof $textarea[0].setSelectionRange !== 'undefined') {
        // Select the whole value
        var length = val.length * 2;
        $textarea[0].setSelectionRange(0, length);
      } else {
        // Refresh the value to get the cursor positioned at the end
        $textarea.val(val);
      }
    }, 0);
  },
  ignoreNextTextareaFocus: function ignoreNextTextareaFocus(ev) {
    $.data(ev.currentTarget, 'ignoreNextFocus', true);
  },
  validateKeypress: function validateKeypress(ev) {
    var keyCode = ev.keyCode ? ev.keyCode : ev.charCode;

    if (!Garnish.isCtrlKeyPressed(ev) && (keyCode === Garnish.RETURN_KEY || ev.data.type === 'number' && !Craft.inArray(keyCode, Craft.SproutSeo.EditableTable.Row.numericKeyCodes))) {
      ev.preventDefault();
    }
  },
  validateValue: function validateValue(ev) {
    var safeValue;

    if (ev.data.type === 'number') {
      // Only grab the number at the beginning of the value (if any)
      var match = ev.currentTarget.value.match(/^\s*(-?[\d.]*)/);

      if (match !== null) {
        safeValue = match[1];
      } else {
        safeValue = '';
      }
    } else {
      // Just strip any newlines
      safeValue = ev.currentTarget.value.replace(/[\r\n]/g, '');
    }

    if (safeValue !== ev.currentTarget.value) {
      ev.currentTarget.value = safeValue;
    }
  },
  onTextareaHeightChange: function onTextareaHeightChange() {
    // Keep all the textareas' heights in sync
    var tallestTextareaHeight = -1;

    for (var i = 0; i < this.niceTexts.length; i++) {
      if (this.niceTexts[i].height > tallestTextareaHeight) {
        tallestTextareaHeight = this.niceTexts[i].height;
      }
    }

    this.$textareas.css('min-height', tallestTextareaHeight); // If the <td> is still taller, go with that insted

    var tdHeight = this.$textareas.first().parent().height();

    if (tdHeight > tallestTextareaHeight) {
      this.$textareas.css('min-height', tdHeight);
    }
  },
  deleteRow: function deleteRow() {
    this.table.sorter.removeItems(this.$tr);
    this.$tr.remove(); // onDeleteRow callback

    this.table.settings.onDeleteRow(this.$tr);
  }
}, {
  numericKeyCodes: [9
  /* (tab) */
  , 8
  /* (delete) */
  , 37, 38, 39, 40
  /* (arrows) */
  , 45, 91
  /* (minus) */
  , 46, 190
  /* period */
  , 48, 49, 50, 51, 52, 53, 54, 55, 56, 57
  /* (0-9) */
  ]
});

/***/ }),

/***/ "./src/web/assets/selectother/src/js/SelectOtherField.js":
/*!***************************************************************!*\
  !*** ./src/web/assets/selectother/src/js/SelectOtherField.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

/* global Craft */
if (_typeof(Craft.SproutFields) === ( true ? "undefined" : undefined)) {
  Craft.SproutFields = {};
}

(function ($) {
  // Set all the standard Craft.SproutFields.* stuff
  $.extend(Craft.SproutFields, {
    initFields: function initFields($container) {
      $('.sprout-selectother', $container).sproutSelectOther();
    }
  }); // -------------------------------------------
  //  Custom jQuery plugins
  // -------------------------------------------

  $.extend($.fn, {
    sproutSelectOther: function sproutSelectOther() {
      return this.each(function () {
        if (!$.data(this, 'sprout-selectother')) {
          new Craft.SproutFields.SelectOtherField(this);
        }
      });
    }
  });
  Craft.SproutFields.SelectOtherField = Garnish.Base.extend({
    $container: null,
    $dropdownField: null,
    $textField: null,
    $clearIcon: null,
    init: function init(container) {
      this.$container = $(container);
      this.$dropdownField = this.$container.find('.sprout-selectotherdropdown select');
      this.$textField = this.$container.find('.sprout-selectothertext input');
      this.$clearIcon = this.$container.find('.sprout-selectothertext .clear');
      this.addListener(this.$dropdownField, 'change', 'handleSelectOtherChange');
      this.addListener(this.$clearIcon, 'click', 'handleCancelOther');
    },
    handleSelectOtherChange: function handleSelectOtherChange() {
      var selectedValue = this.$dropdownField.val();

      if (selectedValue === 'custom') {
        // Hide the Select Field and it's wrapping div
        this.$dropdownField.parent().addClass('hidden'); // Show the Text Field and display the existing value for editing

        this.$textField.parent().removeClass('hidden');

        if (this.$textField.val().indexOf('{') > -1) {
          // If the setting is using custom Twig syntax, don't clear the field
          this.$textField.focus().select();
        } else {
          // If the setting is not using Twig syntax, clear the field so the user sees the placeholder example
          this.$textField.val('').focus().select();
        }
      } else {
        // Store the selected value in the other field, as it takes precedence
        this.$textField.val(selectedValue);
      }
    },
    handleCancelOther: function handleCancelOther() {
      // Hide our Custom text field
      this.$textField.parent().addClass('hidden'); // Show our dropdown again

      this.$dropdownField.parent().removeClass('hidden');
    }
  }); // Add support to Sprout Forms edit modal window

  var content = $("#sprout-content");

  if (content.length === 0) {
    content = $("#content");
  } // Initialize the SelectOther Field


  Craft.SproutFields.initFields($(content));
})(jQuery);

/***/ }),

/***/ 4:
/*!**************************************************************************************************************************!*\
  !*** multi ./src/web/assets/selectother/src/js/EditableTable.js ./src/web/assets/selectother/src/js/SelectOtherField.js ***!
  \**************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! /var/www/html/plugins/sprout-base-fields/src/web/assets/selectother/src/js/EditableTable.js */"./src/web/assets/selectother/src/js/EditableTable.js");
module.exports = __webpack_require__(/*! /var/www/html/plugins/sprout-base-fields/src/web/assets/selectother/src/js/SelectOtherField.js */"./src/web/assets/selectother/src/js/SelectOtherField.js");


/***/ })

/******/ });