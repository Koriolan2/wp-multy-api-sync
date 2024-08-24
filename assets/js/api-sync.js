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

jQuery(document).ready(function($) {
    // Функція для відображення або приховування елементів в залежності від вибору графіку
    function updateVisibility() {
        var selectedValue = $('#sync_schedule').val();
        if (selectedValue === 'manual') {
            $('#fetch_data_button').show(); // Показуємо кнопку, якщо обрано "Отримати вручну"
            $('#next_scheduled_data').hide(); // Приховуємо поле з наступним отриманням даних
        } else {
            $('#fetch_data_button').hide(); // Приховуємо кнопку, якщо вибрано інший графік
            $('#next_scheduled_data').show(); // Показуємо поле "Наступне отримання даних"
        }
    }

    // Викликаємо функцію при завантаженні сторінки
    updateVisibility();

    // Викликаємо функцію при зміні вибору
    $('#sync_schedule').on('change', function() {
        updateVisibility();
    });
});

