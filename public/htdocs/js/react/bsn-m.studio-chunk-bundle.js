(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["bsn-m"],{

/***/ "./src/Modals/BunitSetName.js":
/*!************************************!*\
  !*** ./src/Modals/BunitSetName.js ***!
  \************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/classCallCheck */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/classCallCheck.js\");\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createClass */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createClass.js\");\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/possibleConstructorReturn */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/possibleConstructorReturn.js\");\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/getPrototypeOf */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/getPrototypeOf.js\");\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_inherits__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/inherits */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/inherits.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! react */ \"./node_modules/react/index.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_5__);\n/* harmony import */ var mobx_react__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! mobx-react */ \"./node_modules/mobx-react/index.module.js\");\n/* harmony import */ var _wrapper_ModalWrapper__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./wrapper/ModalWrapper */ \"./src/Modals/wrapper/ModalWrapper.js\");\n/* harmony import */ var _wrapper_ModalHeader__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./wrapper/ModalHeader */ \"./src/Modals/wrapper/ModalHeader.js\");\n/* harmony import */ var _wrapper_ModalFooter__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./wrapper/ModalFooter */ \"./src/Modals/wrapper/ModalFooter.js\");\n/* harmony import */ var _library_utils_Util__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ../library/utils/Util */ \"./src/library/utils/Util.js\");\n\n\n\n\n\n\nvar _dec,\n    _class,\n    _jsxFileName = \"D:\\\\git\\\\synctree-studio\\\\SynctreeStudiov2.1\\\\react-for-studio\\\\src\\\\Modals\\\\BunitSetName.js\";\n\n\n\n\n\n\n\n/* global global_data */\n\nvar BunitSetName = (_dec = Object(mobx_react__WEBPACK_IMPORTED_MODULE_6__[\"inject\"])('modalStore', 'bizStore'), _dec(_class = Object(mobx_react__WEBPACK_IMPORTED_MODULE_6__[\"observer\"])(_class =\n/*#__PURE__*/\nfunction (_Component) {\n  Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_inherits__WEBPACK_IMPORTED_MODULE_4__[\"default\"])(BunitSetName, _Component);\n\n  function BunitSetName(props) {\n    var _this;\n\n    Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(this, BunitSetName);\n\n    _this = Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(this, Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__[\"default\"])(BunitSetName).call(this, props));\n    _this.formEl = react__WEBPACK_IMPORTED_MODULE_5___default.a.createRef();\n    return _this;\n  }\n\n  Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createClass__WEBPACK_IMPORTED_MODULE_1__[\"default\"])(BunitSetName, [{\n    key: \"setName\",\n    value: function setName(e) {\n      e.preventDefault();\n      var valid = _library_utils_Util__WEBPACK_IMPORTED_MODULE_10__[\"default\"].formCheckRequired($(this.formEl.current));\n\n      if (valid) {\n        this.props.bizStore.saveBizUnit();\n        this.closeModal();\n      }\n    }\n  }, {\n    key: \"onChangeName\",\n    value: function onChangeName(e) {\n      var bizStore = this.props.bizStore;\n      bizStore.setClientTitle(e.target.value);\n      e.preventDefault();\n    }\n  }, {\n    key: \"closeModal\",\n    value: function closeModal() {\n      this.props.modalStore.hideModal();\n    }\n  }, {\n    key: \"render\",\n    value: function render() {\n      var _global_data = global_data,\n          dictionary = _global_data.dictionary;\n      var clientTitle = this.props.bizStore.clientTitle;\n      return react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(_wrapper_ModalWrapper__WEBPACK_IMPORTED_MODULE_7__[\"default\"], {\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 40\n        },\n        __self: this\n      }, react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"form\", {\n        className: \"modal-content\",\n        ref: this.formEl,\n        onSubmit: this.setName.bind(this),\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 41\n        },\n        __self: this\n      }, react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(_wrapper_ModalHeader__WEBPACK_IMPORTED_MODULE_8__[\"default\"], {\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 45\n        },\n        __self: this\n      }, react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"i\", {\n        className: \"fa fa-lg fa-fw fa-puzzle-piece\",\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 46\n        },\n        __self: this\n      }), ' ', react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"span\", {\n        id: \"\",\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 47\n        },\n        __self: this\n      }, \"Node Name\")), react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"div\", {\n        className: \"modal-body\",\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 49\n        },\n        __self: this\n      }, react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"div\", {\n        className: \"widget-body no-padding\",\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 50\n        },\n        __self: this\n      }, react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"div\", {\n        className: \"smart-form\",\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 51\n        },\n        __self: this\n      }, react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"fieldset\", {\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 52\n        },\n        __self: this\n      }, react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"section\", {\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 53\n        },\n        __self: this\n      }, react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"label\", {\n        className: \"label\",\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 54\n        },\n        __self: this\n      }, \"Name\"), react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"label\", {\n        className: \"input\",\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 55\n        },\n        __self: this\n      }, react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"input\", {\n        type: \"text\",\n        onChange: this.onChangeName.bind(this),\n        placeholder: \"Input client name\",\n        required: \"required\",\n        value: clientTitle,\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 56\n        },\n        __self: this\n      }))))))), react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(_wrapper_ModalFooter__WEBPACK_IMPORTED_MODULE_9__[\"default\"], {\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 71\n        },\n        __self: this\n      }, react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"button\", {\n        type: \"submit\",\n        className: \"btn btn-success\",\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 72\n        },\n        __self: this\n      }, react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"i\", {\n        className: \"fa fa-save\",\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 73\n        },\n        __self: this\n      }), ' ', dictionary.button.save))));\n    }\n  }]);\n\n  return BunitSetName;\n}(react__WEBPACK_IMPORTED_MODULE_5__[\"Component\"])) || _class) || _class);\n/* harmony default export */ __webpack_exports__[\"default\"] = (BunitSetName);//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9zcmMvTW9kYWxzL0J1bml0U2V0TmFtZS5qcy5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy8uL3NyYy9Nb2RhbHMvQnVuaXRTZXROYW1lLmpzPzc5NTEiXSwic291cmNlc0NvbnRlbnQiOlsiaW1wb3J0IFJlYWN0LCB7IENvbXBvbmVudCB9IGZyb20gJ3JlYWN0JztcclxuaW1wb3J0IHsgaW5qZWN0LCBvYnNlcnZlciB9IGZyb20gJ21vYngtcmVhY3QnO1xyXG5pbXBvcnQgTW9kYWxXcmFwcGVyIGZyb20gJy4vd3JhcHBlci9Nb2RhbFdyYXBwZXInO1xyXG5pbXBvcnQgTW9kYWxIZWFkZXIgZnJvbSAnLi93cmFwcGVyL01vZGFsSGVhZGVyJztcclxuaW1wb3J0IE1vZGFsRm9vdGVyIGZyb20gJy4vd3JhcHBlci9Nb2RhbEZvb3Rlcic7XHJcbmltcG9ydCBVdGlsIGZyb20gJy4uL2xpYnJhcnkvdXRpbHMvVXRpbCc7XHJcbi8qIGdsb2JhbCBnbG9iYWxfZGF0YSAqL1xyXG5cclxuQGluamVjdCgnbW9kYWxTdG9yZScsICdiaXpTdG9yZScpXHJcbkBvYnNlcnZlclxyXG5jbGFzcyBCdW5pdFNldE5hbWUgZXh0ZW5kcyBDb21wb25lbnQge1xyXG4gICAgY29uc3RydWN0b3IocHJvcHMpIHtcclxuICAgICAgICBzdXBlcihwcm9wcyk7XHJcbiAgICAgICAgdGhpcy5mb3JtRWwgPSBSZWFjdC5jcmVhdGVSZWYoKTtcclxuICAgIH1cclxuXHJcbiAgICBzZXROYW1lKGUpIHtcclxuICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XHJcbiAgICAgICAgbGV0IHZhbGlkID0gVXRpbC5mb3JtQ2hlY2tSZXF1aXJlZCgkKHRoaXMuZm9ybUVsLmN1cnJlbnQpKTtcclxuICAgICAgICBpZiAodmFsaWQpIHtcclxuICAgICAgICAgICAgdGhpcy5wcm9wcy5iaXpTdG9yZS5zYXZlQml6VW5pdCgpO1xyXG4gICAgICAgICAgICB0aGlzLmNsb3NlTW9kYWwoKTtcclxuICAgICAgICB9XHJcbiAgICB9XHJcbiAgICBvbkNoYW5nZU5hbWUoZSkge1xyXG4gICAgICAgIGNvbnN0IHsgYml6U3RvcmUgfSA9IHRoaXMucHJvcHM7XHJcbiAgICAgICAgYml6U3RvcmUuc2V0Q2xpZW50VGl0bGUoZS50YXJnZXQudmFsdWUpO1xyXG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcclxuICAgIH1cclxuXHJcbiAgICBjbG9zZU1vZGFsKCkge1xyXG4gICAgICAgIHRoaXMucHJvcHMubW9kYWxTdG9yZS5oaWRlTW9kYWwoKTtcclxuICAgIH1cclxuXHJcbiAgICByZW5kZXIoKSB7XHJcbiAgICAgICAgY29uc3QgeyBkaWN0aW9uYXJ5IH0gPSBnbG9iYWxfZGF0YTtcclxuICAgICAgICBjb25zdCB7IGNsaWVudFRpdGxlIH0gPSB0aGlzLnByb3BzLmJpelN0b3JlO1xyXG5cclxuICAgICAgICByZXR1cm4gKFxyXG4gICAgICAgICAgICA8TW9kYWxXcmFwcGVyPlxyXG4gICAgICAgICAgICAgICAgPGZvcm1cclxuICAgICAgICAgICAgICAgICAgICBjbGFzc05hbWU9XCJtb2RhbC1jb250ZW50XCJcclxuICAgICAgICAgICAgICAgICAgICByZWY9e3RoaXMuZm9ybUVsfVxyXG4gICAgICAgICAgICAgICAgICAgIG9uU3VibWl0PXt0aGlzLnNldE5hbWUuYmluZCh0aGlzKX0+XHJcbiAgICAgICAgICAgICAgICAgICAgPE1vZGFsSGVhZGVyPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8aSBjbGFzc05hbWU9XCJmYSBmYS1sZyBmYS1mdyBmYS1wdXp6bGUtcGllY2VcIiAvPnsnICd9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDxzcGFuIGlkPVwiXCI+Tm9kZSBOYW1lPC9zcGFuPlxyXG4gICAgICAgICAgICAgICAgICAgIDwvTW9kYWxIZWFkZXI+XHJcbiAgICAgICAgICAgICAgICAgICAgPGRpdiBjbGFzc05hbWU9XCJtb2RhbC1ib2R5XCI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3NOYW1lPVwid2lkZ2V0LWJvZHkgbm8tcGFkZGluZ1wiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRpdiBjbGFzc05hbWU9XCJzbWFydC1mb3JtXCI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGZpZWxkc2V0PlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8c2VjdGlvbj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxsYWJlbCBjbGFzc05hbWU9XCJsYWJlbFwiPk5hbWU8L2xhYmVsPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGxhYmVsIGNsYXNzTmFtZT1cImlucHV0XCI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGlucHV0XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHR5cGU9XCJ0ZXh0XCJcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb25DaGFuZ2U9e3RoaXMub25DaGFuZ2VOYW1lLmJpbmQoXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB0aGlzXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICl9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHBsYWNlaG9sZGVyPVwiSW5wdXQgY2xpZW50IG5hbWVcIlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICByZXF1aXJlZD1cInJlcXVpcmVkXCJcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdmFsdWU9e2NsaWVudFRpdGxlfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIC8+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L2xhYmVsPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3NlY3Rpb24+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC9maWVsZHNldD5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvZGl2PlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cclxuICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cclxuICAgICAgICAgICAgICAgICAgICA8TW9kYWxGb290ZXI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDxidXR0b24gdHlwZT1cInN1Ym1pdFwiIGNsYXNzTmFtZT1cImJ0biBidG4tc3VjY2Vzc1wiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPGkgY2xhc3NOYW1lPVwiZmEgZmEtc2F2ZVwiIC8+eycgJ31cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHtkaWN0aW9uYXJ5LmJ1dHRvbi5zYXZlfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8L2J1dHRvbj5cclxuICAgICAgICAgICAgICAgICAgICA8L01vZGFsRm9vdGVyPlxyXG4gICAgICAgICAgICAgICAgPC9mb3JtPlxyXG4gICAgICAgICAgICA8L01vZGFsV3JhcHBlcj5cclxuICAgICAgICApO1xyXG4gICAgfVxyXG59XHJcbmV4cG9ydCBkZWZhdWx0IEJ1bml0U2V0TmFtZTtcclxuIl0sIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFHQTs7Ozs7QUFDQTtBQUFBO0FBQ0E7QUFEQTtBQUNBO0FBQUE7QUFDQTtBQUZBO0FBR0E7QUFDQTs7O0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOzs7QUFDQTtBQUFBO0FBRUE7QUFDQTtBQUNBOzs7QUFFQTtBQUNBO0FBQ0E7OztBQUVBO0FBQUE7QUFBQTtBQUFBO0FBSUE7QUFDQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFFQTtBQUNBO0FBQ0E7QUFIQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFJQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFDQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUNBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBRUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFDQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUNBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQ0E7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQ0E7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQ0E7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFDQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUVBO0FBQ0E7QUFHQTtBQUNBO0FBQ0E7QUFQQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFlQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFDQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQ0E7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFPQTs7OztBQXJFQTtBQXVFQSIsInNvdXJjZVJvb3QiOiIifQ==\n//# sourceURL=webpack-internal:///./src/Modals/BunitSetName.js\n");

/***/ }),

/***/ "./src/Modals/wrapper/ModalFooter.js":
/*!*******************************************!*\
  !*** ./src/Modals/wrapper/ModalFooter.js ***!
  \*******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/classCallCheck */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/classCallCheck.js\");\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createClass */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createClass.js\");\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/possibleConstructorReturn */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/possibleConstructorReturn.js\");\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/getPrototypeOf */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/getPrototypeOf.js\");\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_inherits__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/inherits */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/inherits.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! react */ \"./node_modules/react/index.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_5__);\n/* harmony import */ var mobx_react__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! mobx-react */ \"./node_modules/mobx-react/index.module.js\");\n\n\n\n\n\n\nvar _dec,\n    _class,\n    _jsxFileName = \"D:\\\\git\\\\synctree-studio\\\\SynctreeStudiov2.1\\\\react-for-studio\\\\src\\\\Modals\\\\wrapper\\\\ModalFooter.js\";\n\n\n\n/* global global_data */\n\nvar ModalFooter = (_dec = Object(mobx_react__WEBPACK_IMPORTED_MODULE_6__[\"inject\"])('modalStore'), _dec(_class = Object(mobx_react__WEBPACK_IMPORTED_MODULE_6__[\"observer\"])(_class =\n/*#__PURE__*/\nfunction (_Component) {\n  Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_inherits__WEBPACK_IMPORTED_MODULE_4__[\"default\"])(ModalFooter, _Component);\n\n  function ModalFooter() {\n    Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(this, ModalFooter);\n\n    return Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(this, Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__[\"default\"])(ModalFooter).apply(this, arguments));\n  }\n\n  Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createClass__WEBPACK_IMPORTED_MODULE_1__[\"default\"])(ModalFooter, [{\n    key: \"closeModal\",\n    value: function closeModal() {\n      if (this.props.closeModal) {\n        this.props.closeModal();\n        return;\n      }\n\n      this.props.modalStore.hideModal();\n    }\n  }, {\n    key: \"render\",\n    value: function render() {\n      var _global_data = global_data,\n          dictionary = _global_data.dictionary;\n      return react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"div\", {\n        className: \"modal-footer\",\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 17\n        },\n        __self: this\n      }, react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"button\", {\n        type: \"button\",\n        className: \"btn btn-default\",\n        onClick: this.closeModal.bind(this),\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 18\n        },\n        __self: this\n      }, react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"i\", {\n        className: \"fa fa-ban\",\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 22\n        },\n        __self: this\n      }), \" \", dictionary.button.close), this.props.children);\n    }\n  }]);\n\n  return ModalFooter;\n}(react__WEBPACK_IMPORTED_MODULE_5__[\"Component\"])) || _class) || _class);\n/* harmony default export */ __webpack_exports__[\"default\"] = (ModalFooter);//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9zcmMvTW9kYWxzL3dyYXBwZXIvTW9kYWxGb290ZXIuanMuanMiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9zcmMvTW9kYWxzL3dyYXBwZXIvTW9kYWxGb290ZXIuanM/MGI0MCJdLCJzb3VyY2VzQ29udGVudCI6WyJpbXBvcnQgUmVhY3QsIHsgQ29tcG9uZW50IH0gZnJvbSAncmVhY3QnO1xyXG5pbXBvcnQgeyBpbmplY3QsIG9ic2VydmVyIH0gZnJvbSAnbW9ieC1yZWFjdCc7XHJcbi8qIGdsb2JhbCBnbG9iYWxfZGF0YSAqL1xyXG5AaW5qZWN0KCdtb2RhbFN0b3JlJylcclxuQG9ic2VydmVyXHJcbmNsYXNzIE1vZGFsRm9vdGVyIGV4dGVuZHMgQ29tcG9uZW50IHtcclxuICAgIGNsb3NlTW9kYWwoKSB7XHJcbiAgICAgICAgaWYgKHRoaXMucHJvcHMuY2xvc2VNb2RhbCkge1xyXG4gICAgICAgICAgICB0aGlzLnByb3BzLmNsb3NlTW9kYWwoKTtcclxuICAgICAgICAgICAgcmV0dXJuO1xyXG4gICAgICAgIH1cclxuICAgICAgICB0aGlzLnByb3BzLm1vZGFsU3RvcmUuaGlkZU1vZGFsKCk7XHJcbiAgICB9XHJcbiAgICByZW5kZXIoKSB7XHJcbiAgICAgICAgY29uc3QgeyBkaWN0aW9uYXJ5IH0gPSBnbG9iYWxfZGF0YTtcclxuICAgICAgICByZXR1cm4gKFxyXG4gICAgICAgICAgICA8ZGl2IGNsYXNzTmFtZT1cIm1vZGFsLWZvb3RlclwiPlxyXG4gICAgICAgICAgICAgICAgPGJ1dHRvblxyXG4gICAgICAgICAgICAgICAgICAgIHR5cGU9XCJidXR0b25cIlxyXG4gICAgICAgICAgICAgICAgICAgIGNsYXNzTmFtZT1cImJ0biBidG4tZGVmYXVsdFwiXHJcbiAgICAgICAgICAgICAgICAgICAgb25DbGljaz17dGhpcy5jbG9zZU1vZGFsLmJpbmQodGhpcyl9PlxyXG4gICAgICAgICAgICAgICAgICAgIDxpIGNsYXNzTmFtZT1cImZhIGZhLWJhblwiIC8+IHtkaWN0aW9uYXJ5LmJ1dHRvbi5jbG9zZX1cclxuICAgICAgICAgICAgICAgIDwvYnV0dG9uPlxyXG4gICAgICAgICAgICAgICAge3RoaXMucHJvcHMuY2hpbGRyZW59XHJcbiAgICAgICAgICAgIDwvZGl2PlxyXG4gICAgICAgICk7XHJcbiAgICB9XHJcbn1cclxuXHJcbmV4cG9ydCBkZWZhdWx0IE1vZGFsRm9vdGVyO1xyXG4iXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUVBOzs7Ozs7Ozs7Ozs7O0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQUE7QUFDQTs7O0FBQ0E7QUFBQTtBQUFBO0FBRUE7QUFDQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUVBO0FBQ0E7QUFDQTtBQUhBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUlBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBS0E7Ozs7QUFyQkE7QUF3QkEiLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///./src/Modals/wrapper/ModalFooter.js\n");

/***/ }),

/***/ "./src/Modals/wrapper/ModalHeader.js":
/*!*******************************************!*\
  !*** ./src/Modals/wrapper/ModalHeader.js ***!
  \*******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/classCallCheck */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/classCallCheck.js\");\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createClass */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createClass.js\");\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/possibleConstructorReturn */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/possibleConstructorReturn.js\");\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/getPrototypeOf */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/getPrototypeOf.js\");\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_inherits__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/inherits */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/inherits.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! react */ \"./node_modules/react/index.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_5__);\n/* harmony import */ var mobx_react__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! mobx-react */ \"./node_modules/mobx-react/index.module.js\");\n\n\n\n\n\n\nvar _dec,\n    _class,\n    _jsxFileName = \"D:\\\\git\\\\synctree-studio\\\\SynctreeStudiov2.1\\\\react-for-studio\\\\src\\\\Modals\\\\wrapper\\\\ModalHeader.js\";\n\n\n\nvar ModalHeader = (_dec = Object(mobx_react__WEBPACK_IMPORTED_MODULE_6__[\"inject\"])('modalStore'), _dec(_class = Object(mobx_react__WEBPACK_IMPORTED_MODULE_6__[\"observer\"])(_class =\n/*#__PURE__*/\nfunction (_Component) {\n  Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_inherits__WEBPACK_IMPORTED_MODULE_4__[\"default\"])(ModalHeader, _Component);\n\n  function ModalHeader() {\n    Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(this, ModalHeader);\n\n    return Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(this, Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__[\"default\"])(ModalHeader).apply(this, arguments));\n  }\n\n  Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createClass__WEBPACK_IMPORTED_MODULE_1__[\"default\"])(ModalHeader, [{\n    key: \"closeModal\",\n    value: function closeModal() {\n      if (this.props.closeModal) {\n        this.props.closeModal();\n        return;\n      }\n\n      this.props.modalStore.hideModal();\n    }\n  }, {\n    key: \"render\",\n    value: function render() {\n      return react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"div\", {\n        className: \"modal-header\",\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 15\n        },\n        __self: this\n      }, react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"button\", {\n        type: \"button\",\n        className: \"close\",\n        onClick: this.closeModal.bind(this),\n        \"aria-hidden\": \"true\",\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 16\n        },\n        __self: this\n      }, \"\\xD7\"), react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"h4\", {\n        className: \"modal-title\",\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 23\n        },\n        __self: this\n      }, this.props.children));\n    }\n  }]);\n\n  return ModalHeader;\n}(react__WEBPACK_IMPORTED_MODULE_5__[\"Component\"])) || _class) || _class);\n/* harmony default export */ __webpack_exports__[\"default\"] = (ModalHeader);//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9zcmMvTW9kYWxzL3dyYXBwZXIvTW9kYWxIZWFkZXIuanMuanMiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9zcmMvTW9kYWxzL3dyYXBwZXIvTW9kYWxIZWFkZXIuanM/OTFjOSJdLCJzb3VyY2VzQ29udGVudCI6WyJpbXBvcnQgUmVhY3QsIHsgQ29tcG9uZW50IH0gZnJvbSAncmVhY3QnO1xyXG5pbXBvcnQgeyBpbmplY3QsIG9ic2VydmVyIH0gZnJvbSAnbW9ieC1yZWFjdCc7XHJcbkBpbmplY3QoJ21vZGFsU3RvcmUnKVxyXG5Ab2JzZXJ2ZXJcclxuY2xhc3MgTW9kYWxIZWFkZXIgZXh0ZW5kcyBDb21wb25lbnQge1xyXG4gICAgY2xvc2VNb2RhbCgpIHtcclxuICAgICAgICBpZiAodGhpcy5wcm9wcy5jbG9zZU1vZGFsKSB7XHJcbiAgICAgICAgICAgIHRoaXMucHJvcHMuY2xvc2VNb2RhbCgpO1xyXG4gICAgICAgICAgICByZXR1cm47XHJcbiAgICAgICAgfVxyXG4gICAgICAgIHRoaXMucHJvcHMubW9kYWxTdG9yZS5oaWRlTW9kYWwoKTtcclxuICAgIH1cclxuICAgIHJlbmRlcigpIHtcclxuICAgICAgICByZXR1cm4gKFxyXG4gICAgICAgICAgICA8ZGl2IGNsYXNzTmFtZT1cIm1vZGFsLWhlYWRlclwiPlxyXG4gICAgICAgICAgICAgICAgPGJ1dHRvblxyXG4gICAgICAgICAgICAgICAgICAgIHR5cGU9XCJidXR0b25cIlxyXG4gICAgICAgICAgICAgICAgICAgIGNsYXNzTmFtZT1cImNsb3NlXCJcclxuICAgICAgICAgICAgICAgICAgICBvbkNsaWNrPXt0aGlzLmNsb3NlTW9kYWwuYmluZCh0aGlzKX1cclxuICAgICAgICAgICAgICAgICAgICBhcmlhLWhpZGRlbj1cInRydWVcIj5cclxuICAgICAgICAgICAgICAgICAgICDDl1xyXG4gICAgICAgICAgICAgICAgPC9idXR0b24+XHJcbiAgICAgICAgICAgICAgICA8aDQgY2xhc3NOYW1lPVwibW9kYWwtdGl0bGVcIj57dGhpcy5wcm9wcy5jaGlsZHJlbn08L2g0PlxyXG4gICAgICAgICAgICA8L2Rpdj5cclxuICAgICAgICApO1xyXG4gICAgfVxyXG59XHJcblxyXG5leHBvcnQgZGVmYXVsdCBNb2RhbEhlYWRlcjtcclxuIl0sIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7Ozs7Ozs7Ozs7O0FBQUE7QUFDQTtBQUdBOzs7Ozs7Ozs7Ozs7O0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQUE7QUFDQTs7O0FBQ0E7QUFDQTtBQUNBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFKQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFPQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUdBOzs7O0FBckJBO0FBd0JBIiwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./src/Modals/wrapper/ModalHeader.js\n");

/***/ }),

/***/ "./src/Modals/wrapper/ModalWrapper.js":
/*!********************************************!*\
  !*** ./src/Modals/wrapper/ModalWrapper.js ***!
  \********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/classCallCheck */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/classCallCheck.js\");\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createClass */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/createClass.js\");\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/possibleConstructorReturn */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/possibleConstructorReturn.js\");\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/getPrototypeOf */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/getPrototypeOf.js\");\n/* harmony import */ var D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_inherits__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/inherits */ \"./node_modules/babel-preset-react-app/node_modules/@babel/runtime/helpers/esm/inherits.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! react */ \"./node_modules/react/index.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_5__);\n\n\n\n\n\nvar _jsxFileName = \"D:\\\\git\\\\synctree-studio\\\\SynctreeStudiov2.1\\\\react-for-studio\\\\src\\\\Modals\\\\wrapper\\\\ModalWrapper.js\";\n\n\nvar ModalWrapper =\n/*#__PURE__*/\nfunction (_Component) {\n  Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_inherits__WEBPACK_IMPORTED_MODULE_4__[\"default\"])(ModalWrapper, _Component);\n\n  function ModalWrapper() {\n    Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_classCallCheck__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(this, ModalWrapper);\n\n    return Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(this, Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__[\"default\"])(ModalWrapper).apply(this, arguments));\n  }\n\n  Object(D_git_synctree_studio_SynctreeStudiov2_1_react_for_studio_node_modules_babel_preset_react_app_node_modules_babel_runtime_helpers_esm_createClass__WEBPACK_IMPORTED_MODULE_1__[\"default\"])(ModalWrapper, [{\n    key: \"render\",\n    value: function render() {\n      return react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"div\", {\n        className: \"fade in\",\n        tabIndex: \"-1\",\n        role: \"dialog\",\n        \"aria-labelledby\": \"myModalLabel\",\n        \"aria-hidden\": \"true\",\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 5\n        },\n        __self: this\n      }, react__WEBPACK_IMPORTED_MODULE_5___default.a.createElement(\"div\", {\n        className: \"modal-dialog\",\n        __source: {\n          fileName: _jsxFileName,\n          lineNumber: 11\n        },\n        __self: this\n      }, this.props.children));\n    }\n  }]);\n\n  return ModalWrapper;\n}(react__WEBPACK_IMPORTED_MODULE_5__[\"Component\"]);\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (ModalWrapper);//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9zcmMvTW9kYWxzL3dyYXBwZXIvTW9kYWxXcmFwcGVyLmpzLmpzIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vLy4vc3JjL01vZGFscy93cmFwcGVyL01vZGFsV3JhcHBlci5qcz80ZGYwIl0sInNvdXJjZXNDb250ZW50IjpbImltcG9ydCBSZWFjdCwgeyBDb21wb25lbnQgfSBmcm9tICdyZWFjdCc7XHJcbmNsYXNzIE1vZGFsV3JhcHBlciBleHRlbmRzIENvbXBvbmVudCB7XHJcbiAgICByZW5kZXIoKSB7XHJcbiAgICAgICAgcmV0dXJuIChcclxuICAgICAgICAgICAgPGRpdlxyXG4gICAgICAgICAgICAgICAgY2xhc3NOYW1lPVwiZmFkZSBpblwiXHJcbiAgICAgICAgICAgICAgICB0YWJJbmRleD1cIi0xXCJcclxuICAgICAgICAgICAgICAgIHJvbGU9XCJkaWFsb2dcIlxyXG4gICAgICAgICAgICAgICAgYXJpYS1sYWJlbGxlZGJ5PVwibXlNb2RhbExhYmVsXCJcclxuICAgICAgICAgICAgICAgIGFyaWEtaGlkZGVuPVwidHJ1ZVwiPlxyXG4gICAgICAgICAgICAgICAgPGRpdiBjbGFzc05hbWU9XCJtb2RhbC1kaWFsb2dcIj57dGhpcy5wcm9wcy5jaGlsZHJlbn08L2Rpdj5cclxuICAgICAgICAgICAgPC9kaXY+XHJcbiAgICAgICAgKTtcclxuICAgIH1cclxufVxyXG5cclxuZXhwb3J0IGRlZmF1bHQgTW9kYWxXcmFwcGVyO1xyXG4iXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7Ozs7O0FBQUE7QUFDQTtBQUFBOzs7Ozs7Ozs7Ozs7O0FBQ0E7QUFDQTtBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFMQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFNQTtBQUFBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUdBOzs7O0FBWkE7QUFDQTtBQWNBIiwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./src/Modals/wrapper/ModalWrapper.js\n");

/***/ })

}]);
//# sourceMappingURL=studio-bundle.js.map