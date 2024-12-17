<?php
/**
 * Add a search form to the WordPress admin bar.
 */
function add_admin_bar_search() {
    global $wp_admin_bar;
    if ( ! is_admin() ) {
        return;
    }

    // Add the modal structure to the page
    echo '<div class="admin-bar-search-modal" style="display: none;">
            <div class="admin-bar-search-modal-content">
                <form role="search" method="get" class="admin-bar-search-form" action="' . esc_url( home_url( '/' ) ) . '">
                    <input type="search" class="admin-bar-search-input" placeholder="Rechercher des articles..." value="' . get_search_query() . '" name="s" />
                    <div class="admin-bar-search-results"></div>
                </form>
            </div>
        </div>';


    // Add a menu item to the admin bar (hidden, used only for positioning)
    $wp_admin_bar->add_menu( array(
        'id'    => 'admin-search',
        'title' => '', // Empty title, we don't want to display anything in the admin bar
        'meta'  => array(
            'class' => 'admin-bar-search-menu',
        ),
    ) );
}
add_action( 'wp_before_admin_bar_render', 'add_admin_bar_search' );


function admin_bar_search_styles() {
    if ( ! is_admin() ) {
        return;
    }
    ?>
    <style type="text/css">
        /* Modal styles */
        .admin-bar-search-modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        }

        .admin-bar-search-modal-content {
            background-color: #000; /* Black background */
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px;
            border: none; /* No border */
            width: 80%; /* Could be more or less, depending on screen size */
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
        }

        .admin-bar-search-input {
            width: calc(100% - 22px); /* Full width minus padding and border */
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 24px; /* Larger font size */
            color: #fff; /* White text */
            background-color: #333; /* Darker background for input */
        }

        .admin-bar-search-input::placeholder { /* Placeholder text color */
            color: #bbb;
        }


        .admin-bar-search-results {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #000; /* Black background for results */
        }

        .admin-bar-search-results a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: #fff; /* White text for results */
            font-size: 18px; /* Larger font size for results */
        }

        .admin-bar-search-results a:hover {
            background-color: #333; /* Darker hover background */
        }
    </style>
    <script>
        // ... (JavaScript remains the same)
    </script>
    <?php
}
add_action( 'admin_head', 'admin_bar_search_styles' );
add_action( 'wp_head', 'admin_bar_search_styles' );


function admin_search_posts_callback() {
    // ... (same AJAX callback logic as before)
}
add_action( 'wp_ajax_admin_search_posts', 'admin_search_posts_callback' );
add_action( 'wp_ajax_nopriv_admin_search_posts', 'admin_search_posts_callback' );
?>
