jQuery(document).ready(function($) {
    // Функція для керування відображенням елементів на основі вибору способу отримання даних
    function toggleFetchMethod() {
        var fetchMethod = $('#api_fetch_method').val();
        
        // Якщо обрано "Отримати вручну"
        if (fetchMethod === 'manual') {
            $('#fetch_data_button').show();  // Показати кнопку "Отримати дані"
            $('#fetch_schedule_label').hide(); // Сховати лейбл графіку
            $('#api_fetch_schedule').hide();  // Сховати випадаючий список графіка
        } 
        // Якщо обрано "Отримати за графіком"
        else if (fetchMethod === 'schedule') {
            $('#fetch_data_button').hide();  // Сховати кнопку "Отримати дані"
            $('#fetch_schedule_label').show();  // Показати лейбл графіку
            $('#api_fetch_schedule').show();  // Показати випадаючий список графіка
        } 
        // Якщо обрано "Оберіть спосіб отримання"
        else {
            $('#fetch_data_button').hide();  // Сховати кнопку "Отримати дані"
            $('#fetch_schedule_label').hide();  // Сховати лейбл графіку
            $('#api_fetch_schedule').hide();  // Сховати випадаючий список графіка
        }
    }

    // Виклик функції при завантаженні сторінки для відображення коректних елементів
    toggleFetchMethod();

    // Виклик функції при зміні вибору способу отримання даних
    $('#api_fetch_method').on('change', function() {
        toggleFetchMethod();  // Оновлюємо видимість елементів
    });

    // Обробка AJAX-запиту для кнопки "Отримати дані"
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
                    location.reload();
                } else {
                    alert(response.data);
                }
            }
        });
    });
});
