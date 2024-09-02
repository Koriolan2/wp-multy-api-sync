jQuery(document).ready(function ($) {
    $('#api_selection_field').on('change', function () {
        var apiSelection = $(this).val();
        var postId = $('#post_ID').val();

        // Очищаємо попередні поля в мета-боксах "API Settings" та "Schedule Settings"
        console.log("Clearing API Settings and Schedule Settings");
        $('#api_settings .inside').empty();
        $('#schedule_settings .inside').empty();

        console.log("Sending AJAX request...");
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
                    // Оновлюємо вміст мета-боксів "API Settings" та "Schedule Settings"
                    $('#api_settings .inside').html(response.data.api_settings);
                    $('#schedule_settings .inside').html(response.data.schedule_settings);
                } else {
                    // Обробка помилки, якщо потрібно
                    alert('Error updating API Settings and Schedule Settings');
                }
            },
        });
    });
});
