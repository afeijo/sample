/**
 * @file
 * Drupal element embed plugin.
 */

(function ($, Drupal, CKEDITOR) {

  "use strict";

  function getFocusedWidget(editor) {
    var widget = editor.widgets.focused;

    if (widget && widget.name === 'drupalelement') {
      return widget;
    }

    return null;
  }

  CKEDITOR.plugins.add('drupalelement', {
    // This plugin requires the Widgets System defined in the 'widget' plugin.
    requires: 'widget',

    // The plugin initialization logic goes inside this method.
    beforeInit: function (editor) {
      // Configure CKEditor DTD for custom drupal-element element.
      // @see https://www.drupal.org/node/2448449#comment-9717735
      var dtd = CKEDITOR.dtd, tagName;
      dtd['drupal-element'] = {'#': 1};
      // Register drupal-element element as allowed child, in each tag that can
      // contain a div element.
      for (tagName in dtd) {
        if (dtd[tagName].div) {
          dtd[tagName]['drupal-element'] = 1;
        }
      }

      // Generic command for adding/editing entities of all types.
      editor.addCommand('editdrupalelement', {
        allowedContent: 'drupal-element[data-element-type,data-element-settings]',
        requiredContent: 'drupal-element[data-element-type,data-element-settings]',
        modes: { wysiwyg : 1 },
        canUndo: true,
        exec: function (editor, data) {
          data = data || {};

          var existingElement = getSelectedEmbeddedElement(editor);
          var existingWidget = (existingElement) ? editor.widgets.getByElement(existingElement, true) : null;

          var existingValues = {};

          if (existingWidget) {
            existingValues = existingWidget.data.attributes;
          }

          var embed_button_id = data.id ? data.id : existingValues['data-embed-button'];

          var dialogSettings = {
            title: existingElement ? editor.config.DrupalElement_dialogTitleEdit : editor.config.DrupalElement_dialogTitleAdd,
            dialogClass: 'element-embed-select-dialog',
            resizable: false,
            minWidth: 800
          };

          var saveCallback = function (values) {
            editor.fire('saveSnapshot');
            if (!existingElement) {
              var element = editor.document.createElement('drupal-element');
              var attributes = values.attributes;
              for (var key in attributes) {
                element.setAttribute(key, attributes[key]);
              }
              editor.insertHtml(element.getOuterHtml());
            }
            else {
              existingWidget.setData({ attributes: values.attributes });
            }
            editor.fire('saveSnapshot');
          };

          // Open the PDS embed dialog for corresponding EmbedButton.
          Drupal.ckeditor.openDialog(editor, Drupal.url('element-embed/dialog/' + editor.config.drupal.format + '/' + embed_button_id), existingValues, saveCallback, dialogSettings);
        }
      });

      // Register the PDS embed widget.
      editor.widgets.add('drupalelement', {
        // Minimum HTML which is required by this widget to work.
        allowedContent: 'drupal-element[data-element-type,data-element-settings]',
        requiredContent: 'drupal-element[data-element-type,data-element-settings]',

        // Simply recognize the element as our own. The inner markup if fetched
        // and inserted the init() callback, since it requires the actual DOM
        // element.
        upcast: function (element, data) {
          var attributes = element.attributes;
          if (element.name !== 'drupal-element' || attributes['data-element-type'] === undefined || attributes['data-element-settings'] === undefined) {
            return;
          }
          data.attributes = CKEDITOR.tools.copy(attributes);
          // Generate an ID for the element, so that we can use the Ajax
          // framework.
          //data.attributes.id = generateEmbedId();
          return element;
        },

        init: function () {
          /** @type {CKEDITOR.dom.element} */
          var element = this.element;

          // See https://www.drupal.org/node/2544018.
          if (element.hasAttribute('data-embed-button')) {
            var buttonId = element.getAttribute('data-embed-button');
            if (editor.config.DrupalElement_buttons[buttonId]) {
              var button = editor.config.DrupalElement_buttons[buttonId];
              this.wrapper.data('cke-display-name', Drupal.t('Embedded @buttonLabel', {'@buttonLabel': button.label}));
            }
          }
        },

        data: function (event) {
          if (this._previewNeedsServersideUpdate()) {
            editor.fire('lockSnapshot');
            this._loadPreview(function (widget) {
              editor.fire('unlockSnapshot');
              editor.fire('saveSnapshot');
            });
          }

          // Allow entity_embed.editor.css to respond to changes (for example in alignment).
          this.element.setAttributes(this.data.attributes);

          // Track the previous state, to allow for smarter decisions.
          this.oldData = CKEDITOR.tools.clone(this.data);
        },

        // Downcast the element.
        downcast: function () {
          // Only keep the wrapping element.
          //element.setHtml('');
          // Remove the auto-generated ID.
          //delete element.attributes.id;
          //return element;
          var downcastElement = new CKEDITOR.htmlParser.element('drupal-element', this.data.attributes);
          return downcastElement;
        },

        _previewNeedsServersideUpdate: function () {
          // When the widget is first loading, it of course needs to still get a preview!
          if (!this.ready) {
            return true;
          }

          return this._hashData(this.oldData) !== this._hashData(this.data);
        },

        /**
         * Computes a hash of the data that can only be previewed by the server.
         */
        _hashData: function (data) {
          var dataToHash = CKEDITOR.tools.clone(data);
          return JSON.stringify(dataToHash);
        },

        /**
         * Loads an embed preview, calls a callback after insertion.
         *
         * @param {function} callback
         *   A callback function that will be called after the preview has loaded, and receives the widget instance.
         */
        _loadPreview: function (callback) {
          // Use the Ajax framework to fetch the HTML, so that we can retrieve
          // out-of-band assets (JS, CSS...).
          var widget = this;

          jQuery.get({
            url: Drupal.url('embed/preview/' + editor.config.drupal.format),
            data: {
              'text': this.downcast().getOuterHtml(),
            },
            dataType: 'html',
            headers: {
              'X-Drupal-EmbedPreview-CSRF-Token': editor.config.drupalEmbed_previewCsrfToken
            },
            success: (previewHtml) => {
              this.element.setHtml(previewHtml);
              callback(this);
            },
          });

          /*var elementEmbedPreview = Drupal.ajax({
            base: widget.element.getId(),
            element: widget.element.$,
            url: Drupal.url('embed/preview/' + editor.config.drupal.format + '?' + $.param({
              value: this.downcast().getOuterHtml()
            })),
            progress: {type: 'none'},
            // Use a custom event to trigger the call.
            event: 'drupalelement_embed_dummy_event'
          });
          elementEmbedPreview.execute();*/
        }

      });

      editor.widgets.on('instanceCreated', function (event) {
        var widget = event.data;

        if (widget.name !== 'drupalelement') {
          return;
        }

        widget.on('edit', function (event) {
          event.cancel();
          // @see https://www.drupal.org/node/2544018
          if (isEditableElementWidget(editor, event.sender.wrapper)) {
            editor.execCommand('editdrupalelement');
          }
        });
      });


      // Register the toolbar buttons.
      if (editor.ui.addButton) {
        for (var key in editor.config.DrupalElement_buttons) {
          var button = editor.config.DrupalElement_buttons[key];
          editor.ui.addButton(button.id, {
            label: button.label,
            data: button,
            allowedContent: 'drupal-element[!data-element-type,!data-element-settings,!data-embed-button]',
            click: function(editor) {
              editor.execCommand('editdrupalelement', this.data);
            },
            icon: button.image,
            modes: {wysiwyg: 1, source: 0}
          });
        }
      }

      // Register context menu option for editing widget.
      if (editor.contextMenu) {
        editor.addMenuGroup('drupalelement');
        editor.addMenuItem('drupalelement', {
          label: Drupal.t('Edit embedded element'),
          command: 'editdrupalelement',
          group: 'drupalelement'
        });

        editor.contextMenu.addListener(function(element) {
          if (isEmbeddedElementWidget(editor, element)) {
            return { drupalelement: CKEDITOR.TRISTATE_OFF };
          }
        });
      }

      // Execute widget editing action on double click.
      editor.on('doubleclick', function (evt) {
        var element = getSelectedEmbeddedElement(editor) || evt.data.element;

        if (isEmbeddedElementWidget(editor, element)) {
          editor.execCommand('editdrupalelement');
        }
      });
    }
  });

  /**
   * Get the surrounding drupalelement widget element.
   *
   * @param {CKEDITOR.editor} editor
   */
  function getSelectedEmbeddedElement(editor) {
    var selection = editor.getSelection();
    var selectedElement = selection.getSelectedElement();
    if (isEmbeddedElementWidget(editor, selectedElement)) {
      return selectedElement;
    }

    return null;
  }

  /**
   * Returns whether or not the given element is a drupalelement widget.
   *
   * @param {CKEDITOR.editor} editor
   * @param {CKEDITOR.htmlParser.element} element
   */
  function isEmbeddedElementWidget (editor, element) {
    var widget = editor.widgets.getByElement(element, true);
    return widget && widget.name === 'drupalelement';
  }

  /**
   * Checks if the given element is an editable drupalelement widget.
   *
   * @param {CKEDITOR.editor} editor
   * @param {CKEDITOR.htmlParser.element} element
   */
  function isEditableElementWidget (editor, element) {
    var widget = editor.widgets.getByElement(element, true);
    if (!widget || widget.name !== 'drupalelement') {
      return false;
    }

    var button = element.$.firstChild.getAttribute('data-embed-button');
    if (!button) {
      // If there was no data-embed-button attribute, not editable.
      return false;
    }

    // The button itself must be valid.
    return editor.config.DrupalElement_buttons.hasOwnProperty(button);
  }

  /**
   * Generates unique HTML IDs for the widgets.
   *
   * @returns {string}
   */
  function generateEmbedId() {
    if (typeof generateEmbedId.counter == 'undefined') {
      generateEmbedId.counter = 0;
    }
    return 'drupal-element-embed-' + generateEmbedId.counter++;
  }


})(jQuery, Drupal, CKEDITOR);
