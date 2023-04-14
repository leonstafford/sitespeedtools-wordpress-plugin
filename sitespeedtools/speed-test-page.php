<?php

function sst_speed_test_page() {
    $api_error = get_transient( 'sst_api_error_message' );
    $options = get_option('sst_settings', []);
    delete_transient( 'sst_api_error_message' );
    ?>
    <div class="wrap">
        <h1>Site Speed Tools</h1>
        <h2>Speed Test</h2>
         <?php
        if ($api_error) {
            echo '<div class="notice notice-error"><p>' . $api_error . '</p></div>';
        }
        ?>
        <p>
            Use the Site Speed Tools Speed Test to analyze and fix the most critical issues slowing down your WordPress site.
        </p>
        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
            <input type="hidden" name="action" value="sst_submit">
            <?php submit_button('Run Speed Test'); ?>
        </form>

        <h1>Results</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" id="time" class="manage-column column-time column-primary">Time</th>
                    <th scope="col" id="status" class="manage-column column-status">Status</th>
                    <th scope="col" id="scanned-urls" class="manage-column column-scanned-urls">Scanned URLs</th>
                    <th scope="col" id="score" class="manage-column column-score">Score</th>
                    <th scope="col" id="issues-detected" class="manage-column column-issues-detected">Issues Detected</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="column-time column-primary" data-colname="Time">2019-01-01 12:00:00</td>
                    <td class="column-status" data-colname="Status">Complete</td>
                    <td class="column-scanned-urls" data-colname="Scanned URLs">99</td>
                    <td class="column-score" data-colname="Score">99</td>
                    <td class="column-issues-detected" data-colname="Issues Detected">-1</td>
                </tr>
                <tr>
                    <td class="column-time column-primary" data-colname="Time">2019-01-01 12:00:00</td>
                    <td class="column-status" data-colname="Status">Complete</td>
                    <td class="column-scanned-urls" data-colname="Scanned URLs">99</td>
                    <td class="column-score" data-colname="Score">99</td>
                    <td class="column-issues-detected" data-colname="Issues Detected">-1</td>
                </tr>
                <tr>
                    <td class="column-time column-primary" data-colname="Time">2019-01-01 12:00:00</td>
                    <td class="column-status" data-colname="Status">Complete</td>
                    <td class="column-scanned-urls" data-colname="Scanned URLs">99</td>
                    <td class="column-score" data-colname="Score">99</td>
                    <td class="column-issues-detected" data-colname="Issues Detected">-1</td>
                </tr>
            </tbody>
        </table>

        <div class="sstools-last-poll">
            <p>Last poll: <span id="sstools-last-poll-time">2019-01-01 12:00:00</span>
            <div id="sstools-loading-indicator" class="wp-loading-indicator"></div>
            </p>
        </div>

        <style>
            .wp-loading-indicator {
                width: 16px;
                height: 16px;
                background-image: url('<?php echo admin_url('images/loading.gif'); ?>');
                background-repeat: no-repeat;
                background-position: center;
                display: none;
                vertical-align: middle;
                margin-left: 10px;
            }
        </style>

        <input type="hidden" id="sst-api-key" value="<?php echo $options['sst_api_key'] ?? ''; ?>">
        <input type="hidden" id="sst-uri" value="<?php echo $options['sst_uri'] ?? ''; ?>">
        <input type="hidden" id="sst-url-override" value="<?php echo $options['sst_url_override'] ?? ''; ?>">

        <!-- example JSON data to create the table above 
        {
            "time": "2019-01-01 12:00:00",
            "status": "Complete",
            "scanned_urls": 99,
            "score": 99,
            "issues_detected": -1
        } -->	

        <script>
            function pollApi() {
                jQuery('#sstools-loading-indicator').css('visibility', 'visible');
                jQuery('#sstools-loading-indicator').css('display', 'block');

                const sstools_site_settings = {
                    api_key: jQuery('#sst-api-key').val(),
                    uri: jQuery('#sst-uri').val(),
                    url_override: jQuery('#sst-url-override').val()
                };
                
                sstools_site_settings.last_time = jQuery('table.wp-list-table tbody tr:last-child td.column-time').text();
                if (sstools_site_settings.last_time === '') {
                    sstools_site_settings.last_time = 0;
                }

                const API_ENDPOINT = 'http://apitest.sitespeedtools.com/v1/speed-test-results';
                jQuery.ajax({
                    url: API_ENDPOINT,
                    type: 'GET',
                    data: sstools_site_settings,
                    success: function(data) {
                        if (data) {
                            for (let i = 0; i < data.length; i++) {
                                jQuery('table.wp-list-table tbody').append(
                                    '<tr>' +
                                        '<td class="column-time column-primary" data-colname="Time">' + data[i].time + '</td>' +
                                        '<td class="column-status" data-colname="Status">' + data[i].status + '</td>' +
                                        '<td class="column-scanned-urls" data-colname="Scanned URLs">' + data[i].scanned_urls + '</td>' +
                                        '<td class="column-score" data-colname="Score">' + data[i].score + '</td>' +
                                        '<td class="column-issues-detected" data-colname="Issues Detected">' + data[i].issues_detected + '</td>' +
                                    '</tr>'
                                );
                            }
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    },
                    complete: function() {
                        jQuery('#sstools-loading-indicator').css('visibility', 'hidden');
                        jQuery('#sstools-last-poll-time').text(new Date().toLocaleString());
                    }
                });
            }

            jQuery(document).ready(function() {
                 pollApi();
                setInterval(function() {
                    pollApi();
                }, 5000);
            });
        </script>

    </div>
    <?php
}
