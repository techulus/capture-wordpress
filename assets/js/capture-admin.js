jQuery(document).ready(function($) {
    $('#capture-test-btn').on('click', function() {
        var $button = $(this);
        var $result = $('#capture-test-result');
        
        // Show loading state
        $button.addClass('loading').prop('disabled', true);
        $button.text('Testing...');
        $result.removeClass('success error').addClass('loading').text('Testing connection to Capture API...');
        
        $.ajax({
            url: capture_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'capture_test_connection',
                nonce: capture_ajax.nonce
            },
            success: function(response) {
                // Reset button state
                $button.removeClass('loading').prop('disabled', false);
                $button.text('Test Connection');
                $result.removeClass('loading');
                
                if (response.success) {
                    $result.addClass('success').text(response.message);
                } else {
                    $result.addClass('error').text(response.message);
                }
            },
            error: function() {
                // Reset button state
                $button.removeClass('loading').prop('disabled', false);
                $button.text('Test Connection');
                $result.removeClass('loading').addClass('error').text('Connection test failed. Please try again.');
            }
        });
    });
    
    // Show/hide API secret
    $('input[name="capture_page_settings[api_secret]"]').after(
        '<button type="button" class="button button-small" id="toggle-secret" style="margin-left: 10px;">Show</button>'
    );
    
    $('#toggle-secret').on('click', function() {
        var $input = $('input[name="capture_page_settings[api_secret]"]');
        var $button = $(this);
        
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $button.text('Hide');
        } else {
            $input.attr('type', 'password');
            $button.text('Show');
        }
    });
});