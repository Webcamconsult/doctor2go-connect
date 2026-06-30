jQuery(function ($) {
  $(document).on('click', '#d2gc-translate-current-btn', function () {
    const $button = $(this);
    const postId = $button.data('post-id');
    const $result = $('#d2gc-translate-result');
    const translatePostFields = $('#d2gc-translate-post-fields').is(':checked') ? '1' : '0';

    if (!postId) {
      $result.html('<span style="color:red;">Missing post ID.</span>');
      return;
    }

    $button.prop('disabled', true).text('Translating...');
    $result.html('');

    $.post(d2gcAiTranslateCurrent.ajaxUrl, {
      action: 'd2gc_translate_doctor_fields_into_current_post',
      nonce: d2gcAiTranslateCurrent.nonce,
      post_id: postId,
      translate_post_fields: translatePostFields
    })
      .done(function (response) {
        if (response && response.success) {
          const postFields = Array.isArray(response.data.updated_post_fields) ? response.data.updated_post_fields.join(', ') : '';
          const metaFields = Array.isArray(response.data.saved_keys) ? response.data.saved_keys.join(', ') : '';

          let html = '<div style="color:green;">' + response.data.message;

          if (postFields) {
            html += '<br><small>Post fields: ' + postFields + '</small>';
          }

          if (metaFields) {
            html += '<br><small>Meta fields: ' + metaFields + '</small>';
          }

          html += '<br><small>Reloading…</small></div>';
          $result.html(html);

          setTimeout(function () {
            window.location.reload();
          }, 1200);
        } else {
          const message = response && response.data && response.data.message
            ? response.data.message
            : 'Unknown error.';
          $result.html('<div style="color:red;">' + message + '</div>');
          $button.prop('disabled', false).text('Translate into this post');
        }
      })
      .fail(function (xhr) {
        let message = 'AJAX request failed.';
        if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
          message = xhr.responseJSON.data.message;
        }
        $result.html('<div style="color:red;">' + message + '</div>');
        $button.prop('disabled', false).text('Translate into this post');
      });
  });
});