<?php
/**
 * Storefront engine room
 *
 * @package storefront
 */

/**
 * Assign the Storefront version to a var
 */
$theme              = wp_get_theme( 'storefront' );
$storefront_version = $theme['Version'];

function add_admin_scripts( $hook ) {
    $ct_checkjs_key = rand(0,100);
    wp_enqueue_script( 'myscript', get_stylesheet_directory_uri().'/assets/js/script.js', array(), $ct_checkjs_key );
    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'add_admin_scripts', 10, 1 );
add_action( 'wp_enqueue_scripts',  'add_admin_scripts');

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 980; /* pixels */
}

$storefront = (object) array(
	'version'    => $storefront_version,

	/**
	 * Initialize all the things.
	 */
	'main'       => require 'inc/class-storefront.php',
	'customizer' => require 'inc/customizer/class-storefront-customizer.php',
);

require 'inc/storefront-functions.php';
require 'inc/storefront-template-hooks.php';
require 'inc/storefront-template-functions.php';
require 'inc/wordpress-shims.php';

if ( class_exists( 'Jetpack' ) ) {
	$storefront->jetpack = require 'inc/jetpack/class-storefront-jetpack.php';
}

if ( storefront_is_woocommerce_activated() ) {
	$storefront->woocommerce            = require 'inc/woocommerce/class-storefront-woocommerce.php';
	$storefront->woocommerce_customizer = require 'inc/woocommerce/class-storefront-woocommerce-customizer.php';

	require 'inc/woocommerce/class-storefront-woocommerce-adjacent-products.php';

	require 'inc/woocommerce/storefront-woocommerce-template-hooks.php';
	require 'inc/woocommerce/storefront-woocommerce-template-functions.php';
	require 'inc/woocommerce/storefront-woocommerce-functions.php';
}

if ( is_admin() ) {
	$storefront->admin = require 'inc/admin/class-storefront-admin.php';

	require 'inc/admin/class-storefront-plugin-install.php';
}

/**
 * NUX
 * Only load if wp version is 4.7.3 or above because of this issue;
 * https://core.trac.wordpress.org/ticket/39610?cversion=1&cnum_hist=2
 */
if ( version_compare( get_bloginfo( 'version' ), '4.7.3', '>=' ) && ( is_admin() || is_customize_preview() ) ) {
	require 'inc/nux/class-storefront-nux-admin.php';
	require 'inc/nux/class-storefront-nux-guided-tour.php';
	require 'inc/nux/class-storefront-nux-starter-content.php';
}

/**
 * Note: Do not add any custom code here. Please use a custom plugin so that your customizations aren't lost during updates.
 * https://github.com/woocommerce/theme-customisations
 */


/* Создание селекта и поля с датой */
add_action( 'woocommerce_product_options_general_product_data', 'add_custom_fields' );
function add_custom_fields() {
    global $product, $post;
    echo '<div class="options_group">';
    echo '</div>';

    woocommerce_wp_select( array(
        'id'      => '_select',
        'label'   => 'Тип продукта',
        'options' => array(
            'null'  => __(''),
            'one'   => __( 'rare', 'woocommerce' ),
            'two'   => __( 'frequent', 'woocommerce' ),
            'three' => __( 'unusual', 'woocommerce' ),
        ),
    ));
    ?>

    <p class="form-field custom_field_type_date">
        <label for="custom_field_type">Дата</label>
        <input id = "_date_create" class = "date_create" name = "_date_create" type = "date" value = "<?php echo get_post_meta( $post->ID, '_date_create', true ); ?>"/>
    </p>

<?php }

/*Сохранение полей*/
add_action( 'woocommerce_process_product_meta', 'custom_fields_save', 10 );
function custom_fields_save( $post_id ) {

    $woocommerce_select = $_POST['_select'];
    if ( ! empty( $woocommerce_select ) ) {
        update_post_meta( $post_id, '_select', esc_attr( $woocommerce_select ) );
    }

    $woocommerce_date_create = $_POST['_date_create'];
    var_dump($woocommerce_date_create);
    if ( ! empty( $woocommerce_date_create ) ) {
        update_post_meta( $post_id, '_date_create', "$woocommerce_date_create" );
    }

    if( isset( $_POST['_listing_cover_image'] ) ) {
        $image_id = $_POST['_listing_cover_image'];
        update_post_meta( $post_id, '_listing_image_id', $image_id );
    }
}



/*Создание Метабокса дял картинки*/
add_action( 'add_meta_boxes', 'listing_image_add_metabox' );
function listing_image_add_metabox () {
    add_meta_box( 'listingimagediv', __( 'Кастомное изображение товара', 'text-domain' ), 'listing_image_metabox', 'product', 'side', 'low');
}



function listing_image_metabox ( $post ) {
    global $content_width, $_wp_additional_image_sizes;

    $image_id = get_post_meta( $post->ID, '_listing_image_id', true );

    $old_content_width = $content_width;
    $content_width = 254;

    if ( $image_id && get_post( $image_id ) ) {

        if ( ! isset( $_wp_additional_image_sizes['post-thumbnail'] ) ) {
            $thumbnail_html = wp_get_attachment_image( $image_id, array( $content_width, $content_width ) );
        } else {
            $thumbnail_html = wp_get_attachment_image( $image_id, 'post-thumbnail' );
        }

        if ( ! empty( $thumbnail_html ) ) {
            $content = $thumbnail_html;
            $content .= '<p class="hide-if-no-js"><a href="javascript:;" id="remove_listing_image_button" >' . esc_html__( 'Удалить картинку', 'storefront' ) . '</a></p>';
            $content .= '<input type="hidden" id="upload_listing_image" name="_listing_cover_image" value="' . esc_attr( $image_id ) . '" />';
        }

        $content_width = $old_content_width;
    } else {

        $content = '<img src="" style="width:' . esc_attr( $content_width ) . 'px;height:auto;border:0;display:none;" />';
        $content .= '<p class="hide-if-no-js"><a title="' . esc_attr__( 'Выбрать картинку', 'storefront' ) . '" href="javascript:;" id="upload_listing_image_button" id="set-listing-image" data-uploader_title="' . esc_attr__( 'Choose an image', 'storefront' ) . '" data-uploader_button_text="' . esc_attr__( 'Выбрать картинку', 'storefront' ) . '">' . esc_html__( 'Выбрать картинку', 'storefront' ) . '</a></p>';
        $content .= '<input type="hidden" id="upload_listing_image" name="_listing_cover_image" value="" />';

    }

    echo $content;
}


/*Создание Метабокса для кнопки обновить*/
add_action( 'add_meta_boxes', 'new_button_update' );
function new_button_update() {

    add_meta_box(
        'new_button_update',
        'Кастомный MetaBox',
        'new_button_update_callback',
        'product',
        'side',
        'low'
    );
}



function new_button_update_callback( $post ) { ?>

<?php }


/* Изменение отображаемой колонки с картинкой */
add_filter('manage_product_posts_columns', function($defaults) {
    $columns = [];
    foreach($defaults as $field => $value) {
        if($field == 'name') {
            $columns['img'] = __('Img', 'woocommerce');
        }
        $columns[$field] = $value;
    }
    return $columns;
}, 100);



add_action('manage_product_posts_custom_column', function($column_name, $post_ID) {
    switch($column_name) {
        case 'img':
            $shop_id = get_post_meta($post_ID, '_listing_image_id', true);
            $attributes = wp_get_attachment_image_src( $shop_id, array(60,60) );

            echo $attributes ? '<img src="' . $attributes[0] . '"' : __('No img', 'woocommerce');
            break;
    }
}, 10, 2);


/* Изменение отображаемой картинки фо фронте */
add_action('init', function(){
    remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
    add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
});

function woocommerce_template_loop_product_thumbnail () {
    global $product, $post;
    $columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
    $post_thumbnail_id = $product->get_image_id();

    $img_id = get_post_meta($post->ID, '_listing_image_id', true);
    $img = wp_get_attachment_image_src( $img_id, array(300, 300) ); ?>

    <div class="product-img" data-columns="<?php echo esc_attr( $columns ); ?>">
        <?php
        if ( $img_id ) {
            $html  = '<div class="woocommerce-product-archive-img">';
            $html .= sprintf( '<img src="' . $img[0] . '" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
            $html .= '</div>';
        } else {
            $html  = '<div class="woocommerce-product-archive-img">';
            $html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
            $html .= '</div>';
        }

        echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $post_thumbnail_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped

        do_action( 'woocommerce_product_thumbnails' );
        ?>
    </div>
<?php }