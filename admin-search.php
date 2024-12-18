function add_admin_bar_search() {
    global $wp_admin_bar;
    if ( ! is_admin() ) {
        return;
    }
    $wp_admin_bar->add_menu( array(
        'id'    => 'admin-search',
        'title' => '<div class="admin-bar-search-container" style="display:none;">
                        <form role="search" method="get" class="admin-bar-search-form" action="' . esc_url( home_url( '/' ) ) . '">
                        <input type="search" class="admin-bar-search-input" placeholder="Rechercher..." value="' . get_search_query() . '" name="s" />
                        </form>
                        <div class="admin-bar-search-results" style="display:none;"></div>
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
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            width: 80%;
            max-width: 600px;
            background-color: rgba(40, 42, 54, 0.9);
            border-radius: 4px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            display: none; /* Masqué par défaut */
            padding: 20px; /* Un peu de rembourrage */
        }
        .admin-bar-search-form {
            display: flex;
            align-items: center;
            padding: 5px;
        }
        .admin-bar-search-input {
            border: 1px solid #6272a4;
            padding: 10px;
            border-radius: 4px;
            font-size: 16px;
            color: #f8f8f2;
            background-color: #282a36;
            width: 100%;
        }
        .admin-bar-search-input:focus {
            border-color: #bd93f9;
            outline: none;
            box-shadow: 0 0 0 1px #bd93f9;
        }
        .admin-bar-search-results {
            position: relative;
            background: #282a36;
            border: 1px solid #6272a4;
            border-radius: 0 0 4px 4px;
            display: none;
            z-index: 1000;
            margin-top: 10px; /* Pour séparer les résultats de la recherche */
        }
        .admin-bar-search-results a {
            display: block;
            padding: 8px 12px;
            text-decoration: none;
            color: #f8f8f2;
        }
        .admin-bar-search-results a:hover {
            background-color: #44475a;
        }
        .admin-bar-search-input::placeholder {
            color: #6272a4;
        }
    </style>
    <script>
         document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.admin-bar-search-input');
            const searchResults = document.querySelector('.admin-bar-search-results');
            const searchContainer = document.querySelector('.admin-bar-search-container');

            function showSearch() {
                searchContainer.style.display = 'block';
                searchInput.focus();
            }

            function hideSearch() {
                searchContainer.style.display = 'none';
                searchInput.value = '';
                searchResults.innerHTML = '';
            }

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
                        noResults.textContent = 'Aucun résultat trouvé';
                        searchResults.appendChild(noResults);
                        searchResults.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erreur :', error);
                    searchResults.innerHTML = '<p>Erreur lors de la récupération des résultats</p>';
                    searchResults.style.display = 'block';
                });
            });

            document.addEventListener('keydown', function(event) {
                if (event.ctrlKey && event.key === 'k' || event.metaKey && event.key === 'k') {
                    event.preventDefault();
                    showSearch();
                }
                if (event.key === 'Escape') {
                    hideSearch();
                }
            });

            // Masquer le champ de recherche en cliquant en dehors
            document.addEventListener('click', function(event) {
                if (!searchContainer.contains(event.target) && searchContainer.style.display === 'block') {
                    hideSearch();
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
