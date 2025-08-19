(function ($, Drupal, drupalSettings, once) {
  'use strict';

  function getScrollOffset() {
    var extra = 0;
    var $toolbar = $('#toolbar-bar:visible');
    if ($toolbar.length) extra += $toolbar.outerHeight();
    var $fixedHeader = $('.site-header.is-fixed:visible, header.fixed:visible');
    if ($fixedHeader.length) extra += $fixedHeader.outerHeight();
    return extra + 12;
  }
  function smoothScrollTo(el) {
    if (!el) return;
    var $el = $(el);
    if (!$el.length) return;
    var top = Math.max(0, $el.offset().top - getScrollOffset());
    $('html, body').stop(true).animate({ scrollTop: top }, 250);
    $el.attr('tabindex', '-1').focus({ preventScroll: true });
  }

  Drupal.behaviors.aiSearchHeaderCapture = {
    attach(context) {
      once('aiSearchHeaderCapture', 'form#ai_search_header_form', context)
        .forEach(function (formEl) {
          $(formEl).on('submit', function () {
            var term = ($(formEl).find('input[name="q"]').val() || '').trim();
            console.log('AI Search Debug (captured from header):', term);
            try { sessionStorage.setItem('ai_search_prefill', term); } catch (err) {}
          });
        });
    }
  };

  function executeDrupalAjax(submitBtn, formEl, attempt) {
    attempt = attempt || 0;
    if (!submitBtn) return false;
    var $submit = $(submitBtn);
    var id = $submit.attr('id');
    var ajaxObj = (Drupal.ajax && id) ? Drupal.ajax[id] : null;

    if (ajaxObj) {
      console.log('AI Search Debug: executing Drupal.ajax for', id);
      ajaxObj.execute();
      return true;
    }
    if (attempt < 5) {
      setTimeout(function () { executeDrupalAjax(submitBtn, formEl, attempt + 1); }, 100);
      return true;
    }
    console.warn('AI Search Debug: Ajax never attached; falling back to jQuery submit');
    $(formEl).trigger('submit');
    return true;
  }

  Drupal.behaviors.aiSearchHeaderAutoRun = {
    attach(context) {
      once('aiSearchHeaderAutoRun', '.ai-search-block-form', context)
        .forEach(function (formEl) {
          var term = null;
          try { term = sessionStorage.getItem('ai_search_prefill'); } catch (e) {}
          if (!term) return;

          console.log('AI Search Debug (injecting into AI block):', term);

          smoothScrollTo(formEl);

          var input =
            formEl.querySelector('[data-drupal-selector="edit-query"]') ||
            formEl.querySelector('input[name="query"]');
          if (!input) {
            console.warn('AI Search Debug: query input not found on AI form.');
            return;
          }
          if ($(formEl).data('ai-autorun-done')) return;
          $(formEl).data('ai-autorun-done', true);

          input.value = term;
          input.dispatchEvent(new Event('input',  { bubbles: true }));
          input.dispatchEvent(new Event('change', { bubbles: true }));
          try { sessionStorage.removeItem('ai_search_prefill'); } catch (e) {}

          var submitBtn = formEl.querySelector('[data-drupal-selector="edit-submit"]');
          if (submitBtn) {
            executeDrupalAjax(submitBtn, formEl, 0);
          } else {
            $(formEl).trigger('submit');
          }
        });
    }
  };

})(jQuery, Drupal, drupalSettings, once);
