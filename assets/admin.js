jQuery(document).ready(function ($) {
    $('#api_selection_field').on('change', function () {
        var apiSelection = $(this).val();
        var postId = $('#post_ID').val();

        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'save_api_selection',
                api_selection: apiSelection,
                post_id: postId,
            },
            success: function (response) {
                if (response.success) {
                    // Оновлюємо вміст мета-боксу "API Settings"
                    $('#api_settings .inside').html(response.data);
                } else {
                    // Обробка помилки, якщо потрібно
                    alert('Error updating API Settings');
                }
            },
        });
    });
});
