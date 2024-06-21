<?php
namespace Danial;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class BooksInfoListTable extends \WP_List_Table {

    public function __construct() {
        parent::__construct( [
            'singular' => __( 'Book', 'danial-test' ),
            'plural'   => __( 'Books', 'danial-test' ),
            'ajax'     => false
        ] );
    }

    public static function get_books_info( $per_page = 5, $page_number = 1 ) {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}books_info";

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        }

        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

        return $wpdb->get_results( $sql, 'ARRAY_A' );
    }

    public static function record_count() {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}books_info";

        return $wpdb->get_var( $sql );
    }

    public function no_items() {
        _e( 'No books available.', 'danial-test' );
    }

    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'num':
                return $this->get_column_count();
            case 'isbn':
                return $item[ 'isbn' ];
            case 'bookname':
                $post_id = $item['post_id'];
                $post = get_post( $post_id );
                if ( $post ) {
                    return $post->post_title;
                } else {
                    return __( 'N/A', 'danial-test' );
                }
            default:
                return print_r( $item, true );
        }
    }


    public function get_columns() {
        $columns = [
            'cb'       => '<input type="checkbox" />',
            'num'  => __( 'Num', 'danial-test' ),
            'isbn'     => __( 'ISBN', 'danial-test' ),
            'bookname' => __( 'Book Name', 'danial-test' ),
        ];

        return $columns;
    }


    public function get_sortable_columns() {
        $sortable_columns = array(
            'isbn' => array( 'isbn', true ),
        );

        return $sortable_columns;
    }

    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page( 'books_per_page', 5 );
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page
        ] );

        $data = self::get_books_info( $per_page, $current_page );
        $items = [];

        foreach ( $data as $entry ) {
            $post_id = $entry['post_id'];
            $post = get_post( $post_id );
            $items[] = [
                'post_id' => $post_id,
                'isbn'    => $entry['isbn'],
                'bookname'=> $post ? $post->post_title : __( 'N/A', 'danial-test' ),
            ];
        }

        $this->items = $items;
    }


    public function process_bulk_action() {
        // Handle bulk actions if needed
    }
}
