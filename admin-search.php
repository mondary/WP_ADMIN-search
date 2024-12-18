
/**
 * Add a search form to the WordPress admin bar.
 */
function add_admin_bar_search() {
    global $wp_admin_bar;
    if ( ! is_admin() ) {
        return;
    }
    $wp_admin_bar->add_menu( array(
        'id'    => 'admin-search',
        'title' => '<div class="admin-bar-search-container">
                        <form role="search" method="get" class="admin-bar-search-form" action="' . esc_url( home_url( '/' ) ) . '">
                            <input type="search" class="admin-bar-search-input" placeholder="Rechercher des articles..." value="' . get_search_query() . '" name="s" />
                            <div class="admin-bar-search-results"></div>
                        </form>
                    </div>',
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
        .admin-bar-search-menu {
            padding: 0;
        }
        .admin-bar-search-container {
            position: relative;
        }
        .admin-bar-search-form {
            display: flex;
            align-items: center;
            margin: 0;
            padding: 0;
        }
        .admin-bar-search-input {
            border: 1px solid #ddd;
            padding: 5px 10px;
            border-radius: 4px;
            margin: 0;
            font-size: 13px;
            line-height: 1.5;
            height: 30px;
            transition: width 0.3s ease;
            width: 150px;
        }
        .admin-bar-search-input:focus {
            border-color: #007cba;
            outline: none;
            box-shadow: 0 0 0 1px #007cba;
            width: 300px;
        }
        .admin-bar-search-results {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: #fff;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 4px 4px;
            display: none;
            z-index: 1000;
        }
        .admin-bar-search-input:focus + .admin-bar-search-results {
            display: block;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.admin-bar-search-input');
            const searchResults = document.querySelector('.admin-bar-search-results');

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value;
                if (searchTerm.length < 3) {
                    searchResults.innerHTML = '';
                    searchResults.style.display = 'none';
                    return;
                }

                fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=admin_search_posts&s=' + encodeURIComponent(searchTerm),
                })
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(item => {
                            const resultItem = document.createElement('a');
                            resultItem.href = item.link;
                            resultItem.textContent = item.title;
                            searchResults.appendChild(resultItem);
                        });
                        searchResults.style.display = 'block';
                    } else {
                        const noResults = document.createElement('p');
                        noResults.textContent = 'No results found';
                        searchResults.appendChild(noResults);
                        searchResults.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    searchResults.innerHTML = '<p>Error fetching results</p>';
                    searchResults.style.display = 'block';
                });
            });

            document.addEventListener('keydown', function(event) {
                if (event.ctrlKey && event.key === 'k') {
                    event.preventDefault();
                    searchInput.focus();
                }
            });
        });
    </script>
    <?php
}
add_action( 'admin_head', 'admin_bar_search_styles' );
add_action( 'wp_head', 'admin_bar_search_styles' );

function admin_search_posts_callback() {
    $search_term = isset( $_POST['s'] ) ? sanitize_text_field( $_POST['s'] ) : '';
    $args = array(
        's'              => $search_term,
        'post_type'      => 'post',
        'posts_per_page' => 5,
    );
    $query = new WP_Query( $args );
    $results = array();
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $results[] = array(
                'title' => get_the_title(),
                'link'  => get_permalink(),
            );
        }
        wp_reset_postdata();
    }
    wp_send_json( $results );
}
add_action( 'wp_ajax_admin_search_posts', 'admin_search_posts_callback' );
add_action( 'wp_ajax_nopriv_admin_search_posts', 'admin_search_posts_callback' );
