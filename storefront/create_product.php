<?php
/*
Template Name: Custom template
*/
if (isset($_POST['publish'])) {
    header("Location: ".$_SERVER['REQUEST_URI']);
}

get_header();
?>

<h1><?php echo get_the_title() ?></h1>
    <?php global $post;
    require_once ABSPATH . 'wp-admin/includes/file.php';
    ?>
<form name="post" action="" method="post" id="post">
    <div id="titlewrap">
        <label class="" id="title-prompt-text" for="title">Название товара</label>
        <input type="text" name="post_title" size="30" value="" id="title" spellcheck="true" autocomplete="off">
    </div>

    <p class="form-field _regular_price_field ">
        <label for="_regular_price">Базовая цена (₽)</label>
        <input type="number" class="short wc_input_price" style="" name="_regular_price" id="_regular_price" value="" placeholder="">
    </p>

    <p class=" form-field _select_field">
        <label for="_select">Тип продукта</label>
        <select style="" id="_select" name="_select" class="select short">
            <option value="null"></option>
            <option value="one">rare</option>
            <option value="two">frequent</option>
            <option value="three">unusual</option>
        </select>
    </p>

    <p class="form-field custom_field_type_date">
        <label for="custom_field_type">Дата</label>
        <input
            id = "_date_create"
            class = "date_create"
            name = "_date_create"
            type = "date"
            value = "<?php echo get_post_meta( $post->ID, '_date_create', true ); ?>"/>
    </p>

    <div id="listingimagediv" class="postbox ">
        <div class="inside">
            <?php listing_image_metabox($post); ?>
        <div>
    </div>

    <div id="publishing-action">
        <span class="spinner"></span>
        <input name="original_publish" type="hidden" id="original_publish" value="Создать товар">
        <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="Создать товар">
    </div>
</form>


<?php
$product_title = $_POST['post_title'];
$product_price = $_POST['_regular_price'];
$product_select = $_POST['_select'];
$product_date = $_POST['_date_create'];
$product_img = $_POST['_listing_cover_image'];

$post = array(
    'post_status' => "publish",
    'post_title' => $product_title,
    'post_type' => "product",
);

$product_id = wp_insert_post( $post, __('Cannot create product', 'izzycart-function-code') );

wp_set_object_terms($product_id, 'simple', 'product_type');

update_post_meta( $product_id, '_regular_price', $product_price );
update_post_meta( $product_id, '_price', $product_price );
update_post_meta( $product_id, '_date_create', $product_date );
update_post_meta( $product_id, '_select', $product_select );
update_post_meta( $product_id, '_listing_image_id', $product_img );
update_post_meta( $product_id, '_visibility', 'visible' );
update_post_meta( $product_id, '_stock_status', 'instock' );

get_footer();