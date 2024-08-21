jQuery(document).ready(function($) {
    $('#fetch_data_button').on('click', function() {
        var postId = $(this).data('post-id');
        var nonce = apiSyncData.nonce;

        $.ajax({
            url: apiSyncData.ajax_url,
            type: 'POST',
            data: {
                action: 'fetch_api_data',
                post_id: postId,
                security: nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Дані успішно отримані');
                    location.reload(); // Перезавантажуємо сторінку для відображення повідомлень
                } else {
                    alert(response.data);
                }
            }
        });
    });
});
