<?php
/**
 * Search Posts
 *
 * @package       SEARCHPOST
 * @author        Vitalii
 * @license       gplv3
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   Search Posts
 * Plugin URI:    #
 * Description:   Plugin for posts searching.
 * Version:       1.0.0
 * Author:        Vitalii
 * Author URI:    #
 * Text Domain:   search-posts
 * Domain Path:   /languages
 * License:       GPLv3
 * License URI:   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with Search Posts. If not, see <https://www.gnu.org/licenses/gpl-3.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Plugin name
define( 'SEARCHPOST_NAME',			'Search Posts' );

// Plugin version
define( 'SEARCHPOST_VERSION',		'1.0.0' );

// Plugin Root File
define( 'SEARCHPOST_PLUGIN_FILE',	__FILE__ );

// Plugin base
define( 'SEARCHPOST_PLUGIN_BASE',	plugin_basename( SEARCHPOST_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'SEARCHPOST_PLUGIN_DIR',	plugin_dir_path( SEARCHPOST_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'SEARCHPOST_PLUGIN_URL',	plugin_dir_url( SEARCHPOST_PLUGIN_FILE ) );

// Add files
function search_posts_scripts() {
	wp_enqueue_style( 'iyacht-main-style', plugin_dir_url(__FILE__) . '/assets/css/backend-styles.min.css', array());

    wp_enqueue_script('backend-scripts-js', plugin_dir_url(__FILE__) . '/assets/js/backend-scripts.min.js', array('jquery'), null, true);
	wp_localize_script( 
		'backend-scripts-js', 
		'my_ajax', 
		array( 
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce('ajax-nonce') 
		) 
	);
}
add_action('admin_enqueue_scripts', 'search_posts_scripts');

// Add Search Posts page
function search_posts_menu() {
    add_menu_page(
        'Search Posts Settings',
        'Search Posts',
        'manage_options',
        'search-posts-settings',
        'search_posts_page',
        'dashicons-search',
        5
    );
}
add_action('admin_menu', 'search_posts_menu');

function search_posts_page() {
    // Security check
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    ?>
    <div class="wrap search-posts">
        <h2 class="page-title">Search Posts</h2>
		<div class="search-posts-container">
			<input type="text" id="search-posts-input" name="search-posts-input" placeholder="Keyword...">
			<button id="search-posts-button">Search</button>
			<p id="search-posts-message"></p>
		</div>
        <div class="idicators">
			<div class="has-keyword"><span></span><p> - the cell has a keyword</p></div>
			<div class="no-keyword"><span></span><p> - the cell has no keyword</p></div>
		</div>
		<h3 id="current-word">Current keyword: <span></span></h3>
        <div id="search-posts-results">
            <table class="wp-list-table">
                <thead>
                    <tr>
                        <th class="manage-column column-title column-primary">
							<h3>Title</h3>
							<input type="text" id="replace-title" class="post-replace-input" name="replace-title" placeholder="New keyword...">
							<p id="replace-message-title"></p>
							<button id="replace-button-title" class="post-replace-button" data-replace="title"  data-search="">Replace</button>
						</th>
                        <th class="manage-column column-content">
							<h3>Content</h3>
							<input type="text" id="replace-content" class="post-replace-input" name="replace-content" placeholder="New keyword...">
							<p id="replace-message-content"></p>
							<button id="replace-button-content" class="post-replace-button" data-replace="content"  data-search="">Replace</button>
						</th>
                        <th class="manage-column column-meta-title">
							<h3>Meta Title</h3>
							<input type="text" id="replace-meta-title" class="post-replace-input" name="replace-meta-title" placeholder="New keyword...">
							<p id="replace-message-meta-title"></p>
							<button id="replace-button-meta-title" class="post-replace-button" data-replace="meta-title"  data-search="">Replace</button>
						</th>
                        <th class="manage-column column-meta-description">
							<h3>Meta Description</h3>
							<input type="text" id="replace-meta-description" class="post-replace-input" name="replace-meta-description" placeholder="New keyword...">
							<p id="replace-message-meta-description"></p>
							<button id="replace-button-meta-description" class="post-replace-button" data-replace="meta-description"  data-search="">Replace</button>
						</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <?php
}

function search_posts() {
		
	$search = sanitize_text_field($_POST['search']);
	$replace = sanitize_text_field($_POST['replace']);
	$element = sanitize_text_field($_POST['element']);
	
	$args = array(
		's' => $search,
		'post_type' => 'post',
		'post_status' => 'publish',
		'posts_per_page' => -1,
	);
	$query = new WP_Query($args);

	if( $query->found_posts == 0){
		$args2 = array(
			'post_type' => 'post',
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => '_yoast_wpseo_title',
					'value' => $search,
					'compare' => 'LIKE'
				),
				array(
					'key' => '_yoast_wpseo_metadesc',
					'value' => $search,
					'compare' => 'LIKE'
				)
			)
		);
			
		$query = new WP_Query($args2);
	}
	
	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			$title = get_the_title();
			$content = get_the_excerpt();
			$yoast_title = YoastSEO()->meta->for_post(get_the_ID())->title;
			$meta_title = $yoast_title ? $yoast_title : get_the_title();
			$yoast_description = YoastSEO()->meta->for_post(get_the_ID())->description;
			$meta_description = $yoast_description ? $yoast_description : get_the_excerpt();

			$wordToCheck = $search;
			if($element === "title"){
				$title = str_replace($search, $replace, $title);

				$args = array(
					'ID' => get_the_ID(),
					'post_title' => $title,
				);
				wp_update_post($args);

				$wordToCheck = $replace;
			} elseif($element === "content") {
				$content = str_replace($search, $replace, $content);

				$args = array(
					'ID' => get_the_ID(),
					'post_content' => $content,
				);
				wp_update_post($args);

				$wordToCheck = $replace;
			} elseif($element === "meta-title") {
				$meta_title = str_replace($search, $replace, $meta_title);

				if (isset($meta_title)) {
					update_post_meta( get_the_ID(), '_yoast_wpseo_title', $meta_title );
				}

				$wordToCheck = $replace;
			} elseif($element === "meta-description") {
				$meta_description = str_replace($search, $replace, $meta_description);

				if (isset($meta_description)) {
					update_post_meta( get_the_ID(), '_yoast_wpseo_metadesc', $meta_description );
				}

				$wordToCheck = $replace;
			}

			
			$title_class = stripos($title, $wordToCheck) !== false ? 'has' : 'no';
			$content_class = stripos($content, $wordToCheck) !== false ? 'has' : 'no';
			$meta_title_class = stripos($meta_title, $wordToCheck) !== false ? 'has' : 'no';
			$meta_description_class = stripos($meta_description, $wordToCheck) !== false ? 'has' : 'no';

			echo "<tr class='row-posts'>
					<td class='search-result-post post-title " . $title_class . "'><p>" . $title . "</p></td>
					<td class='search-result-post post-content " . $content_class . "'><p>" . $content . "</p></td>
					<td class='search-result-post post-meta-title " . $meta_title_class . "'><p>" . $meta_title . "</p></td>
					<td class='search-result-post post-meta-description " . $meta_description_class . "'><p>" . $meta_description . "</p></td>
				</tr>";
		}
	} else {
		echo '<tr><td colspan="4">No posts found.</td></tr>';
	}
	
	wp_reset_postdata();
	die();
}
add_action('wp_ajax_search_posts', 'search_posts');

