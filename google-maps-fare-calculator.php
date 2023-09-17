<?php

/**
 * Plugin Name: Google Maps Fare Calculator
 * Plugin URI: https://zufaa.com/
 * Description: A simple plugin to calculate fare between two locations in miles.
 * Version: 1.0
 * Author: Shakir Ahmed Joy
 * Author URI: https://zufaa.com/
 */

// Enqueue scripts
function gmfc_print_google_maps_script()
{
    $api_key = get_option('gmfc_api_key');
?>
    <script>
        (g => {
            var c = "google",
                l = "__ib__",
                m = window[c] || (window[c] = {}),
                d = m.maps || (m.maps = {}),
                r = new Set(),
                e = {
                    key: "<?php echo $api_key; ?>",
                    v: "weekly",
                    libraries: "places"
                };
            d.importLibrary ? console.warn("The Google Maps JavaScript API only loads once. Ignoring:", g) : d.importLibrary = (f, ...n) => r.add(f) && ((m = document.createElement("script")).src = `https://maps.${c}apis.com/maps/api/js?key=${e.key}&v=${e.v}&libraries=${e.libraries}&callback=${c}.maps.${l}`, d[l] = () => d.importLibrary(f, ...n), m.nonce = document.querySelector("script[nonce]")?.nonce || "", document.head.append(m));
        })();
    </script>
<?php
    wp_enqueue_script('gmfc-js', plugin_dir_url(__FILE__) . 'gmfc.js', array(), '1.0', true);
    $ride_names = array(
        'FareRyder' => get_option('gmfc_fare_per_mile'),
        'Uber' => get_option('gmfc_fare_per_mile_2'),
        'Lyft' => get_option('gmfc_fare_per_mile_3')
    );
    wp_localize_script('gmfc-js', 'gmfcRideNames', $ride_names);
    wp_add_inline_script('gmfc-js', 'google.maps.event.addDomListener(window, "load", initMap);');
}

add_action('wp_head', 'gmfc_print_google_maps_script');


// Shortcode for front-end
function gmfc_shortcode($atts)
{
    $fare_per_mile = array(
        get_option('gmfc_fare_per_mile', '1'),
        get_option('gmfc_fare_per_mile_2', '1'),
        get_option('gmfc_fare_per_mile_3', '1')
    );

    ob_start();
?>
    <style>
        #gmfc-container {
            width: 100%;
        }

        #map-container {
            height: 400px;
            width: 100%;
        }

        /* Style for the 3-column table */
        #fare table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        #fare table,
        #fare table th,
        #fare table td {
            border: 1px solid #dddddd;
        }

        #fare table th,
        #fare table td {
            padding: 10px;
            text-align: center;
        }

        #fare table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .map-input-wrap {
            display: flex;
            gap: 20px;
            height: auto;
            align-items: center;
            margin-bottom: 20px;
            background-color: #05542d;
            height: 200px;
            padding-left: 20px;
            padding-right: 20px;
        }

        .gmfc-input-wrap {
            display: flex;
            flex-direction: column;
            width: 35%;
            gap: 10px;
        }

        #locationA,
        #locationB {
            height: 50px;

        }


        input#locationA:focus,
        input#locationB:focus {

            background-color: #f9fafb;
        }

        #calculate-fare {
            width: 25%;
            height: 50px;
            margin-top: 35px;
            background-color: #25D366;
        }

        #calculate-fare:hover {
            background-color: #2A2A2A;
        }



        /* Mobile Responsive Styles */
        @media only screen and (max-width: 600px) {
            #map-container {
                height: 300px;
            }

            #fare table th,
            #fare table td {
                padding: 5px;
                font-size: 100px;
            }

            .map-input-wrap {
                display: flex;
                gap: 20px;
                flex-direction: column;
                align-items: flex-start;
                height: auto;
                padding-top: 10px;
                padding-bottom: 20px;
            }

            .gmfc-input-wrap {
                display: flex;
                flex-direction: column;
                width: 100%;
                gap: 10px;
            }

            #calculate-fare {
                width: 100%;
            }
        }

        /* GMFC Table style */
        #gmfc-container .gmfc-responsive-table table th,
        #gmfc-container .gmfc-responsive-table table td {
            width: 25%;
            max-width: 25%;
            box-sizing: border-box;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            background-color: #05542d;
            color: #fff
        }

        @media only screen and (max-width: 600px) {

            #gmfc-container .gmfc-responsive-table table th,
            #gmfc-container .gmfc-responsive-table table td {
                font-size: 14px;
            }
        }

        @media only screen and (min-width: 601px) and (max-width: 1024px) {

            #gmfc-container .gmfc-responsive-table table th,
            #gmfc-container .gmfc-responsive-table table td {
                font-size: 16px;
            }
        }
    </style>

    <div id="gmfc-container">
        <div class="map-input-wrap">
            <div class="gmfc-input-wrap">
                <label for="locationA"><strong><span style="color:#fff">Start</span></strong></label>
                <input id="locationA" type="text" placeholder="Start">
            </div>
            <div class="gmfc-input-wrap">
                <label for="locationB"><strong><span style="color:#fff">Destination</span></strong></label>
                <input id="locationB" type="text" placeholder="Location B">
            </div>
            <button id="calculate-fare"><strong></strong>Get Estimate</button>
        </div>
        <div id="map-container"></div>
        <div id="fare"></div>
    </div>
    <script>
        var gmfcFarePerMile = <?php echo json_encode($fare_per_mile); ?>;
    </script>
<?php

    return ob_get_clean();
}

add_shortcode('google_maps_fare_calculator', 'gmfc_shortcode');

// Admin menu
function gmfc_admin_menu()
{
    add_menu_page('Google Maps Fare Calculator', 'Fare Calculator', 'manage_options', 'gmfc-settings', 'gmfc_settings_page', 'dashicons-location', 90);
}

add_action('admin_menu', 'gmfc_admin_menu');

// Admin settings page
function gmfc_settings_page()
{
?>
    <div class="wrap">
        <h2>Google Maps Fare Calculator</h2>
        <form method="post" action="options.php">
            <?php settings_fields('gmfc-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Google Maps API Key</th>
                    <td><input type="text" name="gmfc_api_key" value="<?php echo esc_attr(get_option('gmfc_api_key')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">FareRyder (Fare Per Mile)</th>
                    <td><input type="text" name="gmfc_fare_per_mile" value="<?php echo esc_attr(get_option('gmfc_fare_per_mile')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Uber (Fare Per Mile)</th>
                    <td><input type="text" name="gmfc_fare_per_mile_2" value="<?php echo esc_attr(get_option('gmfc_fare_per_mile_2')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Lyft (Fare Per Mile)</th>
                    <td><input type="text" name="gmfc_fare_per_mile_3" value="<?php echo esc_attr(get_option('gmfc_fare_per_mile_3')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}

// Register settings
function gmfc_register_settings()
{
    register_setting('gmfc-settings-group', 'gmfc_api_key');
    register_setting('gmfc-settings-group', 'gmfc_fare_per_mile');
    register_setting('gmfc-settings-group', 'gmfc_fare_per_mile_2');
    register_setting('gmfc-settings-group', 'gmfc_fare_per_mile_3');
}

add_action('admin_init', 'gmfc_register_settings');
?>