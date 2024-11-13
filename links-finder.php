<?php
/*
Plugin Name: Links Finder
Plugin URI: http://dplugins.com/
Description: A plugin to preview all post links with option to replace it and display it in a table.
Version: 1.0
Author: Marko Krstić
Author URI: http://dplugins.com/
License: GPL2
*/

// Hook to add the settings page
add_action('admin_menu', 'links_finder_add_settings_page');

function links_finder_add_settings_page()
{
    add_options_page(
        'Links Finder Settings', // Page title
        'Links Finder',          // Menu title
        'manage_options',          // Capability
        'links-finder',          // Menu slug
        'links_finder_render_settings_page' // Callback function
    );
}

function links_finder_render_settings_page()
{
    // Fetch the latest 3 posts
    $args = array(
        'numberposts' => -1, // Adjust the number of posts as needed
        'post_type'   => 'post', // Ensure we are fetching posts
        'post_status' => 'publish' // Only published posts
    );
    $posts = get_posts($args);
?>
    <style>
        table {
            width: 100%;
        }

        #findAndReplaceForm {
            display: flex;
            gap: 10px;
            margin: 30px 0;

        }

        .form-group {
            position: relative;
        }

        .form-group label {
            position: absolute;
            top: -20px;
            left: 0;
        }
    </style>
    <div class="wrap">
        <h1>Links Finder Table</h1>

        <h2>Find and Replace</h2>
        <form method="post" id="findAndReplaceForm">
            <div class="form-group">
                <label for="find">Find:</label>
                <input type="text" id="find" name="find" value="<?php echo isset($_POST['find']) ? esc_attr($_POST['find']) : ''; ?>" />
            </div>
            <div class="form-group">
                <label for="replace">Replace with:</label>
                <input type="text" id="replace" name="replace" value="<?php echo isset($_POST['replace']) ? esc_attr($_POST['replace']) : ''; ?>" />
            </div>
            <input class="button button-primary" type="submit" value="Replace" />
        </form>

        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Finders Table</h2>
            <div>
                <button class="button button-secondary" id="copyButton">Copy Table</button>
                <button class="button button-secondary" id="saveButton">Save as CSV</button>
            </div>
        </div>
        <table class="widefat" id="findersTable">
            <thead>
                <tr>
                    <th>status</th>
                    <th>url_from</th>
                    <th>url_to</th>
                    <th>type</th>
                    <th>query parameters</th>
                    <th>regex</th>
                    <th>position</th>
                    <th>case_insensitive</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post) : ?>
                    <tr>
                        <td>enabled</td>
                        <td><?php
                            $url_from = esc_url(get_permalink($post->ID));
                            echo $url_from;
                            ?></td>
                        <td><?php
                            $replace_with = isset($_POST['replace']) ? esc_attr($_POST['replace']) : '';
                            $find = isset($_POST['find']) ? esc_attr($_POST['find']) : '';
                            echo str_replace($find, $replace_with, $url_from);
                            ?></td>
                        <td>301</td>
                        <td>ignore</td>
                        <td></td>
                        <td>0</td>
                        <td>disabled</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
        document.getElementById('copyButton').addEventListener('click', function() {
            var table = document.getElementById('findersTable');
            var rows = Array.from(table.querySelectorAll('tr'));
            var csv = rows.map(function(row) {
                var cells = row.querySelectorAll('th, td');
                return Array.from(cells).map(function(cell) {
                    return cell.textContent.trim();
                }).join(',');
            }).join('\n');

            // Check if the Clipboard API is available
            if (navigator.clipboard) {
                navigator.clipboard.writeText(csv).then(function() {
                    alert('Table copied to clipboard in CSV format!');
                }).catch(function(error) {
                    console.error('Copy failed', error);
                });
            } else {
                // Fallback for copying text
                var textarea = document.createElement('textarea');
                textarea.value = csv;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('Table copied to clipboard in CSV format! (Fallback method)');
            }
        });

        function downloadCSV(csv, filename) {
            var blob = new Blob([csv], {
                type: 'text/csv;charset=utf-8;'
            });
            var link = document.createElement("a");
            var url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        document.getElementById('saveButton').addEventListener('click', function() {
            var table = document.getElementById('findersTable');
            var rows = Array.from(table.querySelectorAll('tr'));
            var csv = rows.map(function(row) {
                var cells = row.querySelectorAll('th, td');
                return Array.from(cells).map(function(cell) {
                    return cell.textContent.trim();
                }).join(',');
            }).join('\n');
            downloadCSV(csv, 'finders_table.csv');
        });
    </script>
<?php
}
