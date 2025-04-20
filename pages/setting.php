<?php

/**
 * Settings page for the plugin.
 */

require_once plugin_dir_path( __FILE__ ) . '../utility/random_generator.php';
require_once plugin_dir_path( __FILE__ ) . '../utility/Variables.php';

/**
 * Displays the settings page for the plugin.
 *
 * @return void
 */
function ssl_support_for_webbylife_platform_settings_page() {
	// check if the user is allowed to access this page
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// check if the user is logged in
	if ( ! is_user_logged_in() ) {
		return;
	}

	// check if the user has the required capability
	if ( ! current_user_can( 'administrator' ) ) {
		return;
	}

	// get the auth key from the database
	$auth_key = get_option( AUTH_KEY_SSL_SUPPORT_TOOL_WEBBYLIFE_PLATFORM );

	// display the settings page
	?>

    <!-- display notice if the key is not valid or valid -->
	<?php if ( isset( $_GET['is_key_valid'] ) && $_GET['is_key_valid'] == 'false' ) : ?>
        <div class="notice notice-error is-dismissible">
            <p>The key length is not valid. Please enter a value between 16 and 512.</p>
        </div>
	<?php elseif ( isset( $_GET['is_key_valid'] ) && $_GET['is_key_valid'] == 'true' ) : ?>
        <div class="notice notice-success is-dismissible">
            <p>The key has been updated successfully.</p>
        </div>
	<?php endif; ?>

        <!-- Add big notice if the api is not working correctly -->
    <?php if (!make_get_request_to_api_integration()) : ?>
        <div class="notice notice-error is-dismissible">
            <p>The API is not working correctly.
                Maybe your server has automatic ACME certificate issuer, or another application or server intercepting request at
                <?php get_site_url()?>/.well-known/acme-challenge/</p> <br>
            <p>To fix this issue, please check your server configuration and make sure that the request is not intercepted by another application or server.</p>
        </div>

    <?php else : ?>
        <div class="notice notice-success is-dismissible">
            <p>The API is working correctly and normally. You can generate a new key.</p>
        </div>

    <?php endif; ?>



    <div class="wrap">
    <h1>Ssl Support For Webbylife Platform</h1>
    <p>To generate ssl certificate for your domain, please visit <a href="https://utility.webbylife.com">https://utility.webbylife.com</a>
    </p>
    <p>Your auth key is: <strong><?php echo esc_html( $auth_key ); ?></strong></p>
    <p>Please keep this key safe and do not share it with anyone.
    </p> Click the below button to copy the auth key to clipboard: <br>

    <!-- add copy auth key button -->
    <button id="copy-auth-key" class="button button-secondary">Copy Auth Key</button>
    <br><br>

    <form method="post" action="">
        <!-- nonce field for security -->
		<?php wp_nonce_field( 'ssl_support_for_webbylife_platform_nonce', 'ssl_support_for_webbylife_platform_nonce' ); ?>
        <!-- add the optional security key length, can be changed by the user -->
        <label for="ssl_support_for_webbylife_platform_key_length">Key Length: (min 16 - max 512)</label>
        <input type="number" name="ssl_support_for_webbylife_platform_key_length"
               id="ssl_support_for_webbylife_platform_key_length" value="<?php echo esc_attr( strlen( $auth_key ) ); ?>"
               min="16" max="512" required>
        <input type="submit" name="ssl_support_for_webbylife_platform_generate_key" class="button button-primary"
               value="Generate New Key">

    </form>

    <script>
        document.getElementById('copy-auth-key').addEventListener('click', function () {
            const authKey = "<?php echo esc_js( $auth_key ); ?>";
            navigator.clipboard.writeText(authKey).then(function () {
                alert('Auth key copied to clipboard');
            }, function () {
                alert('Could not copy auth key');
            });
        });

    </script>

	<?php


}

// add the settings page to the admin menu
add_action( 'admin_menu', function () {
	add_menu_page(
		'SSL Support Plugin',
		'SSL Support API',
		'manage_options',
		'ssl-support-for-webbylife-platform',
		'ssl_support_for_webbylife_platform_settings_page',
		'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IS0tIFVwbG9hZGVkIHRvOiBTVkcgUmVwbywgd3d3LnN2Z3JlcG8uY29tLCBHZW5lcmF0b3I6IFNWRyBSZXBvIE1peGVyIFRvb2xzIC0tPgo8c3ZnIGZpbGw9IiMwMDAwMDAiIHdpZHRoPSI4MDBweCIgaGVpZ2h0PSI4MDBweCIgdmlld0JveD0iMCAwIDE0IDE0IiByb2xlPSJpbWciIGZvY3VzYWJsZT0iZmFsc2UiIGFyaWEtaGlkZGVuPSJ0cnVlIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGQ9Im0gMTIuMjIxNjI2LDYuNzcwODEgLTEuNTA5NDQ1LDAgYyAtMC4yNDI0MTQsMCAtMC40Mzg5OTEsLTAuMTk2NTYgLTAuNDM4OTkxLC0wLjQzOSAwLC0wLjI0MjQzIDAuMTk2NTUzLC0wLjQzODk5IDAuNDM4OTkxLC0wLjQzODk5IGwgMS41MDk0NDUsMCBjIDAuMjQyNDE0LDAgMC40Mzg5OTIsMC4xOTY1NiAwLjQzODk5MiwwLjQzODk5IC0yLjVlLTUsMC4yNDI0NCAtMC4xOTY1NzgsMC40MzkgLTAuNDM4OTkyLDAuNDM5IHogTSA5LjY3MzExLDQuNDE4NzcgQyA5LjU0NjI2NSw0LjQxODc3IDkuNDIwNTQ1LDQuMzY0MTcgOS4zMzM3ODksNC4yNTg1NiA5LjE3OTc5NSw0LjA3MTMgOS4yMDY3NDgsMy43OTQ2NyA5LjM5NDAwOSwzLjY0MDY4IGwgMS4xODI3NDQsLTAuOTcyNTIgYyAwLjE4NzQ1NSwtMC4xNTQwNyAwLjQ2NDAxNCwtMC4xMjY5NCAwLjYxNzg4NiwwLjA2MDMgMC4xNTM5OTUsMC4xODcyNiAwLjEyNzA0MSwwLjQ2Mzg5IC0wLjA2MDIyLDAuNjE3ODggTCA5Ljk1MTY3NSw0LjMxODg2IEMgOS44Njk5MzUsNC4zODYwNiA5Ljc3MTE5MSw0LjQxODc2IDkuNjczMTEsNC40MTg3NiBaIE0gNi45OTk5NzUsMy4zODQ5OCBjIC0wLjI0MjQxNCwwIC0wLjQzODk5MiwtMC4xOTY1NSAtMC40Mzg5OTIsLTAuNDM4OTkgbCAwLC0xLjUwNyBDIDYuNTYwOTgzLDEuMTk2NTUgNi43NTc1MzcsMSA2Ljk5OTk3NSwxIDcuMjQyMzg5LDEgNy40Mzg5NjcsMS4xOTY1NSA3LjQzODk2NywxLjQzODk5IGwgMCwxLjUwNyBDIDcuNDM4OTQzLDMuMTg4NDMgNy4yNDIzODksMy4zODQ5OCA2Ljk5OTk3NSwzLjM4NDk4IFogTSA0LjMyNjg5LDQuNDE4NzcgYyAtMC4wOTgwOCwwIC0wLjE5Njg0NywtMC4wMzI3IC0wLjI3ODU2NSwtMC4wOTk5IEwgMi44NjU1ODEsMy4zNDYzNiBDIDIuNjc4MzIyLDMuMTkyMzYgMi42NTEzNjgsMi45MTU3MyAyLjgwNTM2MSwyLjcyODQ3IDIuOTU5MzA3LDIuNTQxMjYgMy4yMzU4NjYsMi41MTQxNiAzLjQyMzI0NywyLjY2ODE3IGwgMS4xODI3NDQsMC45NzI1MiBjIDAuMTg3MjU5LDAuMTU0IDAuMjE0MjEzLDAuNDMwNjMgMC4wNjAyMiwwLjYxNzg5IC0wLjA4Njc2LDAuMTA1NTQgLTAuMjEyNTUsMC4xNjAxOCAtMC4zMzkzMjEsMC4xNjAxOCB6IG0gLTEuMDUzMjgzLDIuMzUyMDQgLTEuNDk1MjMzLDAgYyAtMC4yNDI0MTQsMCAtMC40Mzg5OTIsLTAuMTk2NTYgLTAuNDM4OTkyLC0wLjQzOSAwLC0wLjI0MjQzIDAuMTk2NTUzLC0wLjQzODk5IDAuNDM4OTkyLC0wLjQzODk5IGwgMS40OTUyMzMsMCBjIDAuMjQyNDE1LDAgMC40Mzg5OTIsMC4xOTY1NiAwLjQzODk5MiwwLjQzODk5IDAsMC4yNDI0NCAtMC4xOTY1NzcsMC40MzkgLTAuNDM4OTkyLDAuNDM5IHogbSA2LjgyNDYyNywwLjY4MzQxIC02LjE5NjQ2OCwwIGMgLTAuMjY0NTUsMCAtMC40ODA5ODgsMC4yMTY0NiAtMC40ODA5ODgsMC40ODA5OSBsIDAsNC41ODM3OCBDIDMuNDIwNzc4LDEyLjc4MzU0IDMuNjM3MjE2LDEzIDMuOTAxNzY2LDEzIGwgNi4xOTY0NjgsMCBjIDAuMjY0NTQ5LDAgMC40ODA5ODgsLTAuMjE2NDYgMC40ODA5ODgsLTAuNDgxMDEgbCAwLC00LjU4Mzc4IGMgMCwtMC4yNjQ1NSAtMC4yMTY0MzksLTAuNDgwOTkgLTAuNDgwOTg4LC0wLjQ4MDk5IHogbSAtMi43ODQxNzksMy4wMDk1NCAwLDAuNjIxMDkgYyAwLDAuMTczMzQgLTAuMTQwNjE2LDAuMzEzOTggLTAuMzEzOTgyLDAuMzEzOTggLTAuMTczMzY2LDAgLTAuMzEzOTgxLC0wLjE0MDYxIC0wLjMxMzk4MSwtMC4zMTM5OCBsIDAsLTAuNjIwOTkgQyA2LjQ5MjkzOCwxMC4zNTQyNiA2LjM2MjQyNSwxMC4xNDcxOSA2LjM2MjQyNSw5LjkwOTI4IDYuMzYyNDI1LDkuNTU3MTYgNi42NDc4ODcsOS4yNzE3IDcsOS4yNzE3IGMgMC4zNTIxMTMsMCAwLjYzNzU3NSwwLjI4NTQ2IDAuNjM3NTc1LDAuNjM3NTggMCwwLjIzNzkxIC0wLjEzMDQ4OSwwLjQ0NDk1IC0wLjMyMzUyLDAuNTU0NDggeiBtIDIuMjAyMTI2LC0zLjAwOTU0IC0xLjQ0MzAxNCwwIDAsLTAuNzQwODkgQyA4LjA3MzE2Nyw2LjEyMTY0IDcuNTkxNjksNS42NDAxNiA3LDUuNjQwMTYgYyAtMC41OTE2OSwwIC0xLjA3MzE2OCwwLjQ4MTQ4IC0xLjA3MzE2OCwxLjA3MzE3IGwgMCwwLjc0MDg5IC0xLjQ0MzAxMywwIDAsLTAuNzQwODkgQyA0LjQ4MzgxOSw1LjMyNTkxIDUuNjEyNTgyLDQuMTk3MTUgNyw0LjE5NzE1IGMgMS4zODc0MTgsMCAyLjUxNjE4MSwxLjEyODc2IDIuNTE2MTgxLDIuNTE2MTggbCAwLDAuNzQwODkgeiIvPjwvc3ZnPg=='
	);

	// ADD submenu page to manage acme challenge data
	add_submenu_page(
		'ssl-support-for-webbylife-platform',
		'Manage Acme Challenge Data',
		'Manage Acme Challenge Data',
		'manage_options',
		'manage-acme-challenge-data',
		'ssl_support_for_webbylife_platform_manage_acme_challenge_data_page'
	);
} );

function ssl_support_for_webbylife_platform_manage_acme_challenge_data_page() {

	// require once
	require_once plugin_dir_path( __FILE__ ) . 'manage_acme_entry.php';

	ssl_support_for_webbylife_platform_acme_challenge_data_page();

}


// add the settings link to the plugin page
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	$settings_link = '<a href="' . admin_url( 'admin.php?page=ssl-support-for-webbylife-platform' ) . '">Settings</a>';
	$links[]       = $settings_link;

	return $links;
} );

// add the settings link to the plugin page
add_filter( 'plugin_row_meta', function ( $links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$settings_link = '<a href="' . admin_url( 'admin.php?page=ssl-support-for-webbylife-platform' ) . '">Settings</a>';
		$links[]       = $settings_link;
	}

	return $links;
}, 10, 2 );

// add the settings link to the plugin page
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	$settings_link = '<a href="' . admin_url( 'admin.php?page=ssl-support-for-webbylife-platform' ) . '">Settings</a>';
	$links[]       = $settings_link;

	return $links;
} );

/**
 * this method makes a get request to the /.well-known/acme-challenge/ with the auth key
 * and returns the response, false if the response is not valid
 *
 * @return bool the response from the server, false if the response is not valid
 */
function make_get_request_to_api_integration() : bool {


    // build the url: wordpressaddress/.well-known/acme-challenge/auth_key
    $url = get_site_url() . '/.well-known/acme-challenge/ping';


	// make a get request to the url
	$response = wp_remote_get( $url );

    // parse the response
    if ( is_wp_error( $response ) ) {
        return false;
    }
    $response_code = wp_remote_retrieve_response_code( $response );
    $response_body = wp_remote_retrieve_body( $response );
    // check if the response code is 200
    if ( $response_code != 200 ) {
        return false;
    }
    // check if the response body is by checking json decode, status is success and key is ping
    $response_body = json_decode( $response_body, true );
    if ( $response_body == null ) {
        return false;
    }
    if ( ! isset( $response_body['status'] ) || $response_body['status'] != 'success' ) {
        return false;
    }
    if ( ! isset( $response_body['key'] ) || $response_body['key'] != 'ping' ) {
        return false;
    }


    // return true
    return true;
}


