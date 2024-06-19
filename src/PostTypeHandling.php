<?php

namespace Danial;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Rabbit\Contracts\BootablePluginProviderInterface;

class PostTypeHandling extends AbstractServiceProvider implements BootablePluginProviderInterface
{
    protected $provides = [
        'posttype'
    ];

    public function register()
    {

    }

    public function registerBookPostType()
    {
        $labels = [
            'name' => _x('Books', 'Post Type General Name', 'danial-test'),
            'singular_name' => _x('Book', 'Post Type Singular Name', 'danial-test'),
            'menu_name' => _x('Books', 'Admin Menu General Name', 'danial-test'),
            'parent_item_colon' => __('Parent Book:', 'danial-test'),
            'all_items' => __('All Books', 'danial-test'),
            'add_new_item' => __('Add New Book', 'danial-test'),
            'add_new' => __('Add New Book', 'danial-test'),
            'edit_item' => __('Edit Book', 'danial-test'),
            'update_item' => __('Update Book', 'danial-test'),
            'view_item' => __('View Book', 'danial-test'),
            'search_items' => __('Search Books', 'danial-test'),
            'not_found' => __('No books found', 'danial-test'),
            'not_found_in_trash' => __('No books found in Trash', 'danial-test'),
        ];

        $args = [
            'label' => __('Book', 'danial-test'),
            'description' => __('Books and their information', 'danial-test'),
            'labels' => $labels,
            'supports' => ['title', 'editor', 'thumbnail'],
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'book'],
            'menu_icon' => 'dashicons-book',
        ];

        register_post_type('book', $args);
    }

    public function registerBookTaxonomies()
    {
        $labels = array(
            'name' => _x('Publishers', 'taxonomy general name', 'danial-test'),
            'singular_name' => _x('Publisher', 'taxonomy singular name', 'danial-test'),
            'search_items' => __('Search Publishers', 'danial-test'),
            'all_items' => __('All Publishers', 'danial-test'),
            'parent_item' => __('Parent Publisher', 'danial-test'),
            'parent_item_colon' => __('Parent Publisher:', 'danial-test'),
            'edit_item' => __('Edit Publisher', 'danial-test'),
            'update_item' => __('Update Publisher', 'danial-test'),
            'add_new_item' => __('Add New Publisher', 'danial-test'),
            'new_item_name' => __('New Publisher Name', 'danial-test'),
            'menu_name' => __('Publisher', 'danial-test'),
        );

        $args = array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'publisher'),
        );

        register_taxonomy('publisher', array('book'), $args);

        // Register Authors taxonomy
        $labels = array(
            'name' => _x('Authors', 'taxonomy general name', 'danial-test'),
            'singular_name' => _x('Author', 'taxonomy singular name', 'danial-test'),
            'search_items' => __('Search Authors', 'danial-test'),
            'all_items' => __('All Authors', 'danial-test'),
            'parent_item' => __('Parent Author', 'danial-test'),
            'parent_item_colon' => __('Parent Author:', 'danial-test'),
            'edit_item' => __('Edit Author', 'danial-test'),
            'update_item' => __('Update Author', 'danial-test'),
            'add_new_item' => __('Add New Author', 'danial-test'),
            'new_item_name' => __('New Author Name', 'danial-test'),
            'menu_name' => __('Author', 'danial-test'),
        );

        $args = array(
            'hierarchical' => false,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'author'),
        );

        register_taxonomy('author', array('book'), $args);
    }

    public function addIsbnMetaBox()
    {
        add_meta_box(
            'isbn_meta_box',
            'ISBN Number',
            [$this, 'addIsbnMetaBoxHtml'],
            'book'
        );
    }

    public function addIsbnMetaBoxHtml()
    {
        global $post;
        $value = get_post_meta($post->ID, '_isbn_number', true);
        ?>
        <label for="isbn_number">ISBN Number</label>
        <input type="text" id="isbn_number" name="isbn_number" value="<?php echo esc_attr($value); ?>" size="25"/>
        <?php
    }

    public function saveIsbnMetaData()
    {
        global $post_id;
        if (array_key_exists('isbn_number', $_POST)) {
            update_post_meta(
                $post_id,
                '_isbn_number',
                $_POST['isbn_number']
            );
        }
    }

    public function saveIsbnToBookInfo($post_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'books_info';

        if (array_key_exists('isbn_number', $_POST)){
            $isbn_number = $_POST['isbn_number'];
            // Check if the book already exists in the table
            $existing_book = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT ID FROM $table_name WHERE post_id = %d",
                    $post_id
                )
            );

            if ($existing_book) {
                // Update existing record
                $wpdb->update(
                    $table_name,
                    array(
                        'isbn' => $isbn_number
                    ),
                    array('post_id' => $post_id),
                    array(
                        '%s'
                    ),
                    array('%d')
                );
            } else {
                // Insert new record
                $wpdb->insert(
                    $table_name,
                    array(
                        'post_id' => $post_id,
                        'isbn' => $isbn_number
                    ),
                    array(
                        '%d',
                        '%s'
                    )
                );
            }
        }
    }

    public function bootPlugin()
    {
        $instance = $this;
        $this->getContainer()::macro(
            'posttype',
            function () use ($instance) {
                return $instance->getContainer()->get('posttype');
            }
        );

        // Registering the 'book' post type
        add_action('init', [$this, 'registerBookPostType']);
        add_action('init', [$this, 'registerBookTaxonomies']);
        add_action('add_meta_boxes', [$this, 'addIsbnMetaBox']);
        add_action('save_post', [$this, 'saveIsbnMetaData']);
        add_action('save_post', [$this, 'saveIsbnToBookInfo']);
    }
}
