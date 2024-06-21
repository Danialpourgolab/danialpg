<?php
/**
 * Plugin Name:     Danial Pourgolab
 * Plugin URI:      https://danial.me
 * Plugin Prefix:   DP
 * Description:     A test plugin
 * Author:          Danial Pourgolab
 * Author URI:      https://danial.me
 * Text Domain:     danial-test
 * Domain Path:     /languages
 * Version:         1.0.0
 */

use Danial\PostTypeHandling;
use League\Container\Container;
use Rabbit\Application;
use Rabbit\Plugin;
use Rabbit\Redirects\AdminNotice;
use Rabbit\Utils\Singleton;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require dirname(__FILE__) . '/vendor/autoload.php';
}

class DanialPlugin extends Singleton
{
    private Container $application;

    public function __construct()
    {
        $this->application = Application::get()->loadPlugin(__DIR__, __FILE__, 'config');
        $this->init();
    }

    public function init()
    {
        try {

            $this->application->addServiceProvider(PostTypeHandling::class);



            $this->application->onActivation(function () {
                global $wpdb;
                $charset_collate = $wpdb->get_charset_collate();
                $table_name = $wpdb->prefix . 'books_info';

                $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                ID int(11) NOT NULL AUTO_INCREMENT,
                post_id int(11) NOT NULL,
                isbn varchar(13) NOT NULL,
                PRIMARY KEY (ID),
                UNIQUE KEY post_id (post_id)
                ) $charset_collate;";

                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
            });

            $this->application->boot(function (Plugin $plugin) {
                $plugin->loadPluginTextDomain();
                add_action('admin_menu', function ( ){
                    add_submenu_page(
                        'edit.php?post_type=book',
                        __('Books ISBN Data', 'danial-test'),
                        __('ISBN Data', 'danial-test'),
                        'manage_options',
                        'books-isbn-data',
                        [$this , 'displayBooksInfo']
                    );
                });
                add_action('admin_head', function ( ){
                    $page = $_GET['page'] ?? '';
                    if ($page === 'books-isbn-data'){
                        ?>
                        <style>
                            .column-comments, .column-links, .column-posts, .widefat .num {
                                text-align: right;
                            }
                        </style>
                        <?php
                    }
                }, 999);
            });

        } catch (Exception $e) {
            add_action('admin_notices', function () use ($e) {
                AdminNotice::permanent(['type' => 'error', 'message' => $e->getMessage()]);
            });

            add_action('init', function () use ($e) {
                if ($this->application->has('logger')) {
                    $this->application->get('logger')->warning($e->getMessage());
                }
            });
        }
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function displayBooksInfo()
    {
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">Books Info</h1>';

        $booksListTable = new Danial\BooksInfoListTable();
        $booksListTable->prepare_items();
        $booksListTable->display();

        echo '</div>';
    }
}

function danialTestPlugin()
{
    return DanialPlugin::get();
}

danialTestPlugin();
