<?php

function sst_settings_init(  ) { 
    register_setting( 'pluginPage', 'sst_settings' );
    add_settings_section(
        'sst_pluginPage_section', 
        __( 'Settings', 'wordpress' ), 
        'sst_settings_section_callback', 
        'pluginPage'
    );

    $settings_fields = [
        [
            'id' => 'sst_override_url_checkbox',
            'title' => __('Override Site URL', 'wordpress'),
            'type' => 'checkbox',
            'description' => __('Use this if the public URL for your WordPress site differs from your WP Site URL.', 'wordpress')
        ],
        [
            'id' => 'sst_override_url_text',
            'title' => __('Override URL', 'wordpress'),
            'type' => 'text',
            'description' => __('Enter the URL of the site you want to test.', 'wordpress')
        ],
        [
            'id' => 'sst_basic_auth_checkbox',
            'title' => __('Use Basic Auth', 'wordpress'),
            'type' => 'checkbox',
            'description' => __('Use this if you want to test a site protected by HTTP Basic Auth.', 'wordpress')
        ],
        [
            'id' => 'sst_basic_auth_user',
            'title' => __('Basic Auth User', 'wordpress'),
            'type' => 'text',
            'description' => __('Enter the username for the Basic Auth account.', 'wordpress')
        ],
        [
            'id' => 'sst_basic_auth_password',
            'title' => __('Basic Auth Password', 'wordpress'),
            'type' => 'text',
            'description' => __('Enter the password for the Basic Auth account.', 'wordpress')
        ],
        [
            'id' => 'sst_api_key',
            'title' => __('API Key', 'wordpress'),
            'type' => 'text',
            'description' => __('Premium accounts can receive an API Key from <a href="https://sitespeedtools.com" target="_blank">SiteSpeedTools.com</>, enabling more features for you.', 'wordpress')
        ],
        [
            'id' => 'sst_unique_token',
            'title' => __('Unique Site Token (auto-generated)', 'wordpress'),
            'type' => 'text',
            'description' => __('This is a unique token generated for your site. It is used to identify your site when making API calls to SiteSpeedTools.com.', 'wordpress'),
            'readonly' => true
        ]
    ];

    array_map(function ($field) {
        add_settings_field(
            $field['id'],
            $field['title'],
            'sst_render_field',
            'pluginPage',
            'sst_pluginPage_section',
            ['id' => $field['id'], 'type' => $field['type'], 'readonly' => $field['readonly'] ?? false]
        );
    }, $settings_fields);
}

function sst_render_field($args) {
    $options = get_option('sst_settings', []);
    $id = $args['id'];
    $type = $args['type'];
    $description = $args['description'] ?? '';
    if ($type === 'checkbox') {
        echo "<input type='checkbox' id='$id' name='sst_settings[$id]' value='1' " . checked(1, isset($options[$id]) ? $options[$id] : 0, false) . ">";
    } else {
        // add readonly attribute if readonly is set to true
        $readonly = isset($args['readonly']) && $args['readonly'] ? 'readonly' : '';
        echo "<input type='$type' id='$id' name='sst_settings[$id]' " . $readonly . " value='" . esc_attr(isset($options[$id]) ? $options[$id] : '') . "'>";
    }
    echo "<p class='description'>" . $description . "</p>";
}

function sst_reset_settings() {
    // delete all options
    delete_option('sst_settings');
    delete_option('sst_api_key');
    delete_option('sst_unique_token');
    // redirect back to settings page
    wp_redirect(admin_url('admin.php?page=site_speed_tools_settings'));
    exit;
}

function sst_options_page() {
    delete_transient( 'sst_api_error' );
    ?>
    <div class="wrap">
        <h1>Site Speed Tools</h1>

        <?php
            // if API key isn't set, show a WP notice with a Button to "Get Free API Key", which submits to sst_get_api_key()
            if (!get_option('sst_api_key')) {
                echo "<div class='notice notice-warning is-dismissible'><p>Site Speed Tools is not yet fully configured. <a href='" . admin_url('admin.php?page=sst-get-api-key') . "'>Generate Free API Key</a></p></div>";
            }


        ?>

        <form action='options.php' method='post'>
            <?php
            settings_fields('pluginPage');
            do_settings_sections('pluginPage');
            submit_button('Save Settings');
            ?>
        </form>

        <?php // a button to "Reset Settings" which submits to sst_reset_settings() 
            echo "<form action='" . admin_url('admin-post.php') . "' method='post'>";
            echo "<input type='hidden' name='action' value='sst_reset_settings'>";
            submit_button('Reset Settings', 'delete', 'submit', false);
            echo "</form>";
        ?>

    </div>
    <script>
        jQuery(document).ready(function($) {
            function updateFieldVisibility() {
                const overrideUrlCheckbox = $('#sst_override_url_checkbox');
                const basicAuthCheckbox = $('#sst_basic_auth_checkbox');
                
                if (overrideUrlCheckbox.is(':checked')) {
                    $('#sst_override_url_text').closest('tr').show();
                } else {
                    $('#sst_override_url_text').closest('tr').hide();
                }
                
                if (basicAuthCheckbox.is(':checked')) {
                    $('#sst_basic_auth_user, #sst_basic_auth_password').closest('tr').show();
                } else {
                    $('#sst_basic_auth_user, #sst_basic_auth_password').closest('tr').hide();
                }
            }
            
            $('#sst_override_url_checkbox, #sst_basic_auth_checkbox').on('change', updateFieldVisibility);
            updateFieldVisibility();
        });
    </script>
    <style>
        .sst-hidden-field {
            display: none;
        }
    </style>
    <?php
}

function sst_settings_section_callback(  ) { 
    echo __( 'Check/adjust your Site Speed Tools settings below:', 'wordpress' );
}