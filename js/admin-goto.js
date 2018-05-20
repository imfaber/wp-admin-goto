/**
 * Created by imfaber on 19/05/2018.
 */

/**
 * @file
 * JavaScript file for the AdminGoto plugin.
 */

(function ($) {

  'use strict';

  // console.log(AdminGoto);

  $(function() {
    // Remap the filter functions for autocomplete to recognise the
    // extra value "command".
    var proto      = $.ui.autocomplete.prototype;
    var initSource = proto._initSource;

    function filter(array, term) {
      var matcher = new RegExp($.ui.autocomplete.escapeRegex(term), 'i');
      return $.grep(array, function (value) {
        return matcher.test(value.title);
      });
    }

    $.extend(proto, {
      _initSource: function () {
        if ($.isArray(this.options.source)) {
          this.source = function (request, response) {
            response(filter(this.options.source, request.term));
          };
        }
        else {
          initSource.call(this);
        }
      }
    });

    var body = $('body');

    /**
     * The HTML elements.
     */
    AdminGoto.label = $('<label for="admin-goto-q" />').text('Query');
    AdminGoto.results = $('<div id="admin-goto-results" />');
    AdminGoto.wrapper = $('<div class="admin-goto-form-wrapper" />');
    AdminGoto.form    = $('<form id="admin-goto-form" action="#" />');
    AdminGoto.bg      = $('<div id="admin-goto-bg" />').click(function () {
      AdminGoto.admin_goto_close();
    });
    AdminGoto.field   = $('<input id="admin-goto-q" type="text" autocomplete="off" placeholder="Go to..." />');

    AdminGoto.bg.appendTo(body).hide();
    AdminGoto.wrapper.appendTo('body').addClass('hide-form');
    AdminGoto.form
      .append(AdminGoto.label)
      .append(AdminGoto.field)
      .append(AdminGoto.results)
      .wrapInner('<div id="admin-goto-form-inner" />')
      .appendTo(AdminGoto.wrapper);

    AdminGoto.isItemSelected = false;

    var autocomplete_data_element = 'ui-autocomplete';

    // Apply autocomplete plugin on show.
    var $autocomplete = $(AdminGoto.field).autocomplete({
      source:   AdminGoto.toolbar_nodes,
      focus:    function (event, ui) {
        // Prevents replacing the value of the input field.
        AdminGoto.isItemSelected = true;
        event.preventDefault();
      },
      change:   function (event, ui) {
        AdminGoto.isItemSelected = false;
      },
      select:   function (event, ui) {
        AdminGoto.redirect(ui.item.href, event.metaKey);
        event.preventDefault();
        return false;
      },
      delay:    0,
      appendTo: AdminGoto.results
    });

    $autocomplete.data(autocomplete_data_element)._renderItem = function (ul, item) {
      return $('<li></li>')
        .data('item.autocomplete', item)
        .append('<a href="' + item.href + '">' + item.title + '<small class="description">' + item.href + '</small></a>')
        .appendTo(ul);
    };

    // We want to limit the number of results.
    $(AdminGoto.field).data(autocomplete_data_element)._renderMenu = function (ul, items) {
      var self = this;
      items    = items.slice(0, 10);
      $.each(items, function (index, item) {
        self._renderItemData(ul, item);
      });
    };

    AdminGoto.form.keydown(function (event) {
      if (event.keyCode === 13) {
        var openInNewWindow = false;

        if (event.metaKey) {
          openInNewWindow = true;
        }

        if (!AdminGoto.isItemSelected) {
          var $firstItem = $(AdminGoto.results).find('li:first').data('item.autocomplete');
          if (typeof $firstItem === 'object') {
            AdminGoto.redirect($firstItem.href, openInNewWindow);
            event.preventDefault();
          }
        }
      }
    });

    $('#wp-admin-bar-admin_goto a').click(function (event) {
      event.preventDefault();
      AdminGoto.admin_goto_show();
    });
    // Key events.
    $(document).keydown(function (event) {

      // Show the form with alt + D. Use 2 keycodes as 'D' can be uppercase or lowercase.
      if (AdminGoto.wrapper.hasClass('hide-form') &&
        event.altKey === true &&
        // 68/206 = d/D, 75 = k.
        (event.keyCode === 68 || event.keyCode === 206 || event.keyCode === 75)) {
        AdminGoto.admin_goto_show();
        event.preventDefault();
      }
      // Close the form with esc or alt + D.
      else {
        if (!AdminGoto.wrapper.hasClass('hide-form') && (event.keyCode === 27 || (event.altKey === true && (event.keyCode === 68 || event.keyCode === 206)))) {
          AdminGoto.admin_goto_close();
          event.preventDefault();
        }
      }
    });
    // Prefix the open and close functions to avoid
    // conflicts with autocomplete plugin.
    /**
     * Open the form and focus on the search field.
     */
    AdminGoto.admin_goto_show = function () {
      AdminGoto.wrapper.removeClass('hide-form');
      AdminGoto.bg.show();
      AdminGoto.field.focus();
      $(AdminGoto.field).autocomplete({enable: true});
    };

    /**
     * Close the form and destroy all data.
     */
    AdminGoto.admin_goto_close = function () {
      AdminGoto.field.val('');
      AdminGoto.wrapper.addClass('hide-form');
      AdminGoto.bg.hide();
      $(AdminGoto.field).autocomplete({enable: false});
    };

    /**
     * Close the AdminGoto form and redirect.
     *
     * @param {string} path
     *   URL to redirect to.
     * @param {bool} openInNewWindow
     *   Indicates if the URL should be open in a new window.
     */
    AdminGoto.redirect = function (path, openInNewWindow) {

      AdminGoto.admin_goto_close();
      
      if (openInNewWindow) {
        window.open(path);
      }
      else {
        document.location = path;
      }
    };

  });

})(jQuery);
