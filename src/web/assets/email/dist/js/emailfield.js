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
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/web/assets/email/src/js/emailfield.js":
/*!***************************************************!*\
  !*** ./src/web/assets/email/src/js/emailfield.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("function _typeof(obj) { if (typeof Symbol === \"function\" && typeof Symbol.iterator === \"symbol\") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === \"function\" && obj.constructor === Symbol && obj !== Symbol.prototype ? \"symbol\" : typeof obj; }; } return _typeof(obj); }\n\n/* global Craft */\nif ((typeof SproutEmailField === \"undefined\" ? \"undefined\" : _typeof(SproutEmailField)) === ( true ? \"undefined\" : undefined)) {\n  SproutEmailField = {};\n}\n\nSproutEmailField = Garnish.Base.extend({\n  init: function init(namespaceInputId, id, elementId, fieldHandle, fieldContext) {\n    this.checkSproutEmailField(namespaceInputId, id, elementId, fieldHandle, fieldContext);\n  },\n  checkSproutEmailField: function checkSproutEmailField(namespaceInputId, id, elementId, fieldHandle, fieldContext) {\n    var sproutEmailFieldId = '#' + namespaceInputId;\n    var sproutEmailButtonClass = '.' + id; // We use setTimeout to make sure our function works every time\n\n    setTimeout(function () {\n      // Set up data for the controller.\n      var data = {\n        'elementId': elementId,\n        'fieldContext': fieldContext,\n        'fieldHandle': fieldHandle,\n        'value': $(sproutEmailFieldId).val()\n      }; // Query the controller so the regex validation is all done through PHP.\n\n      Craft.postActionRequest('sprout-base-fields/fields/email-validate', data, function (response) {\n        if (response) {\n          $(sproutEmailButtonClass).addClass('fade');\n          $(sproutEmailButtonClass + ' a').attr(\"href\", \"mailto:\" + data.value);\n        } else {\n          $(sproutEmailButtonClass).removeClass('fade');\n        }\n      }, []);\n    }, 500);\n  }\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9zcmMvd2ViL2Fzc2V0cy9lbWFpbC9zcmMvanMvZW1haWxmaWVsZC5qcz9jYjg4Il0sIm5hbWVzIjpbIlNwcm91dEVtYWlsRmllbGQiLCJHYXJuaXNoIiwiQmFzZSIsImV4dGVuZCIsImluaXQiLCJuYW1lc3BhY2VJbnB1dElkIiwiaWQiLCJlbGVtZW50SWQiLCJmaWVsZEhhbmRsZSIsImZpZWxkQ29udGV4dCIsImNoZWNrU3Byb3V0RW1haWxGaWVsZCIsInNwcm91dEVtYWlsRmllbGRJZCIsInNwcm91dEVtYWlsQnV0dG9uQ2xhc3MiLCJzZXRUaW1lb3V0IiwiZGF0YSIsIiQiLCJ2YWwiLCJDcmFmdCIsInBvc3RBY3Rpb25SZXF1ZXN0IiwicmVzcG9uc2UiLCJhZGRDbGFzcyIsImF0dHIiLCJ2YWx1ZSIsInJlbW92ZUNsYXNzIl0sIm1hcHBpbmdzIjoiOztBQUFBO0FBRUEsSUFBSSxRQUFPQSxnQkFBUCx5Q0FBT0EsZ0JBQVAsd0NBQUosRUFBa0Q7QUFDOUNBLGtCQUFnQixHQUFHLEVBQW5CO0FBQ0g7O0FBRURBLGdCQUFnQixHQUFHQyxPQUFPLENBQUNDLElBQVIsQ0FBYUMsTUFBYixDQUFvQjtBQUVuQ0MsTUFBSSxFQUFFLGNBQVNDLGdCQUFULEVBQTJCQyxFQUEzQixFQUErQkMsU0FBL0IsRUFBMENDLFdBQTFDLEVBQXVEQyxZQUF2RCxFQUFxRTtBQUN2RSxTQUFLQyxxQkFBTCxDQUEyQkwsZ0JBQTNCLEVBQTZDQyxFQUE3QyxFQUFpREMsU0FBakQsRUFBNERDLFdBQTVELEVBQXlFQyxZQUF6RTtBQUNILEdBSmtDO0FBTW5DQyx1QkFBcUIsRUFBRSwrQkFBU0wsZ0JBQVQsRUFBMkJDLEVBQTNCLEVBQStCQyxTQUEvQixFQUEwQ0MsV0FBMUMsRUFBdURDLFlBQXZELEVBQXFFO0FBRXhGLFFBQUlFLGtCQUFrQixHQUFHLE1BQU1OLGdCQUEvQjtBQUNBLFFBQUlPLHNCQUFzQixHQUFHLE1BQU1OLEVBQW5DLENBSHdGLENBS3hGOztBQUNBTyxjQUFVLENBQUMsWUFBVztBQUNsQjtBQUNBLFVBQUlDLElBQUksR0FBRztBQUNQLHFCQUFhUCxTQUROO0FBRVAsd0JBQWdCRSxZQUZUO0FBR1AsdUJBQWVELFdBSFI7QUFJUCxpQkFBU08sQ0FBQyxDQUFDSixrQkFBRCxDQUFELENBQXNCSyxHQUF0QjtBQUpGLE9BQVgsQ0FGa0IsQ0FTbEI7O0FBQ0FDLFdBQUssQ0FBQ0MsaUJBQU4sQ0FBd0IsMENBQXhCLEVBQW9FSixJQUFwRSxFQUEwRSxVQUFTSyxRQUFULEVBQW1CO0FBQ3pGLFlBQUlBLFFBQUosRUFBYztBQUNWSixXQUFDLENBQUNILHNCQUFELENBQUQsQ0FBMEJRLFFBQTFCLENBQW1DLE1BQW5DO0FBQ0FMLFdBQUMsQ0FBQ0gsc0JBQXNCLEdBQUcsSUFBMUIsQ0FBRCxDQUFpQ1MsSUFBakMsQ0FBc0MsTUFBdEMsRUFBOEMsWUFBWVAsSUFBSSxDQUFDUSxLQUEvRDtBQUNILFNBSEQsTUFHTztBQUNIUCxXQUFDLENBQUNILHNCQUFELENBQUQsQ0FBMEJXLFdBQTFCLENBQXNDLE1BQXRDO0FBQ0g7QUFDSixPQVBELEVBT0csRUFQSDtBQVNILEtBbkJTLEVBbUJQLEdBbkJPLENBQVY7QUFvQkg7QUFoQ2tDLENBQXBCLENBQW5CIiwiZmlsZSI6Ii4vc3JjL3dlYi9hc3NldHMvZW1haWwvc3JjL2pzL2VtYWlsZmllbGQuanMuanMiLCJzb3VyY2VzQ29udGVudCI6WyIvKiBnbG9iYWwgQ3JhZnQgKi9cblxuaWYgKHR5cGVvZiBTcHJvdXRFbWFpbEZpZWxkID09PSB0eXBlb2YgdW5kZWZpbmVkKSB7XG4gICAgU3Byb3V0RW1haWxGaWVsZCA9IHt9O1xufVxuXG5TcHJvdXRFbWFpbEZpZWxkID0gR2FybmlzaC5CYXNlLmV4dGVuZCh7XG5cbiAgICBpbml0OiBmdW5jdGlvbihuYW1lc3BhY2VJbnB1dElkLCBpZCwgZWxlbWVudElkLCBmaWVsZEhhbmRsZSwgZmllbGRDb250ZXh0KSB7XG4gICAgICAgIHRoaXMuY2hlY2tTcHJvdXRFbWFpbEZpZWxkKG5hbWVzcGFjZUlucHV0SWQsIGlkLCBlbGVtZW50SWQsIGZpZWxkSGFuZGxlLCBmaWVsZENvbnRleHQpO1xuICAgIH0sXG5cbiAgICBjaGVja1Nwcm91dEVtYWlsRmllbGQ6IGZ1bmN0aW9uKG5hbWVzcGFjZUlucHV0SWQsIGlkLCBlbGVtZW50SWQsIGZpZWxkSGFuZGxlLCBmaWVsZENvbnRleHQpIHtcblxuICAgICAgICBsZXQgc3Byb3V0RW1haWxGaWVsZElkID0gJyMnICsgbmFtZXNwYWNlSW5wdXRJZDtcbiAgICAgICAgbGV0IHNwcm91dEVtYWlsQnV0dG9uQ2xhc3MgPSAnLicgKyBpZDtcblxuICAgICAgICAvLyBXZSB1c2Ugc2V0VGltZW91dCB0byBtYWtlIHN1cmUgb3VyIGZ1bmN0aW9uIHdvcmtzIGV2ZXJ5IHRpbWVcbiAgICAgICAgc2V0VGltZW91dChmdW5jdGlvbigpIHtcbiAgICAgICAgICAgIC8vIFNldCB1cCBkYXRhIGZvciB0aGUgY29udHJvbGxlci5cbiAgICAgICAgICAgIGxldCBkYXRhID0ge1xuICAgICAgICAgICAgICAgICdlbGVtZW50SWQnOiBlbGVtZW50SWQsXG4gICAgICAgICAgICAgICAgJ2ZpZWxkQ29udGV4dCc6IGZpZWxkQ29udGV4dCxcbiAgICAgICAgICAgICAgICAnZmllbGRIYW5kbGUnOiBmaWVsZEhhbmRsZSxcbiAgICAgICAgICAgICAgICAndmFsdWUnOiAkKHNwcm91dEVtYWlsRmllbGRJZCkudmFsKClcbiAgICAgICAgICAgIH07XG5cbiAgICAgICAgICAgIC8vIFF1ZXJ5IHRoZSBjb250cm9sbGVyIHNvIHRoZSByZWdleCB2YWxpZGF0aW9uIGlzIGFsbCBkb25lIHRocm91Z2ggUEhQLlxuICAgICAgICAgICAgQ3JhZnQucG9zdEFjdGlvblJlcXVlc3QoJ3Nwcm91dC1iYXNlLWZpZWxkcy9maWVsZHMvZW1haWwtdmFsaWRhdGUnLCBkYXRhLCBmdW5jdGlvbihyZXNwb25zZSkge1xuICAgICAgICAgICAgICAgIGlmIChyZXNwb25zZSkge1xuICAgICAgICAgICAgICAgICAgICAkKHNwcm91dEVtYWlsQnV0dG9uQ2xhc3MpLmFkZENsYXNzKCdmYWRlJyk7XG4gICAgICAgICAgICAgICAgICAgICQoc3Byb3V0RW1haWxCdXR0b25DbGFzcyArICcgYScpLmF0dHIoXCJocmVmXCIsIFwibWFpbHRvOlwiICsgZGF0YS52YWx1ZSk7XG4gICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgJChzcHJvdXRFbWFpbEJ1dHRvbkNsYXNzKS5yZW1vdmVDbGFzcygnZmFkZScpO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0sIFtdKTtcblxuICAgICAgICB9LCA1MDApO1xuICAgIH1cbn0pOyJdLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///./src/web/assets/email/src/js/emailfield.js\n");

/***/ }),

/***/ 1:
/*!*********************************************************!*\
  !*** multi ./src/web/assets/email/src/js/emailfield.js ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/benparizek/Projects/Plugins-Craft3/barrelstrength/sprout-base-fields/src/web/assets/email/src/js/emailfield.js */"./src/web/assets/email/src/js/emailfield.js");


/***/ })

/******/ });