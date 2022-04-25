jQuery (function ($) {
    $(document).ready(function () {
        var $place_btn_update = $("#new_button_update");
        var $new_btn = '<div id="new_button_update-action"><span class="spinner"></span><input name="original_publish" type="hidden" id="original_publish" value="Обновить"><input type="submit" name="save" id="publish" class="button button-primary button-large" value="Обновить по новому"></div>';
        var $place = $("#general_product_data");
        var $btn = '<p class="clear_custom_fields-button"><a href="javascript:;" id="clear_custom_fields" >Очистить кастомные поля</a></p>';
        $place_btn_update.append($new_btn);
        $place.append($btn);
    })
});

jQuery(document).ready(function($) {

    // Uploading files
    var file_frame;

    jQuery.fn.upload_listing_image = function( button ) {
        var button_id = button.attr('id');
        var field_id = button_id.replace( '_button', '' );

        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: jQuery( this ).data( 'uploader_title' ),
            button: {
                text: jQuery( this ).data( 'uploader_button_text' ),
            },
            multiple: false
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            var attachment = file_frame.state().get('selection').first().toJSON();
            jQuery("#"+field_id).val(attachment.id);
            jQuery("#listingimagediv img").attr('src',attachment.url);
            jQuery( '#listingimagediv img' ).show();
            jQuery( '#' + button_id ).attr( 'id', 'remove_listing_image_button' );
            jQuery( '#remove_listing_image_button' ).text( 'Удалить картинку' );
        });

        // Finally, open the modal
        file_frame.open();
    };
    jQuery('#listingimagediv').on( 'click', '#upload_listing_image_button', function( event ) {
        event.preventDefault();
        jQuery.fn.upload_listing_image( jQuery(this) );
    });

    jQuery('#listingimagediv').on( 'click', '#remove_listing_image_button', function( event ) {
        event.preventDefault();
        jQuery( '#upload_listing_image' ).val( '' );
        jQuery( '#listingimagediv img' ).attr( 'src', '' );
        jQuery( '#listingimagediv img' ).hide();
        jQuery( this ).attr( 'id', 'upload_listing_image_button' );
        jQuery( '#upload_listing_image_button' ).text( 'Выбрать картинку' );
    });

});


jQuery (function ($) {
    $(document).ready(function() {
        var $selectbox = $("#_select");

        $("#clear_custom_fields").click(function(event) {
            event.preventDefault();
            jQuery( '#_date_create' ).val( '' );
            $selectbox.prop('selectedIndex', 0);
            //
            // jQuery('._select_field select').empty();

            jQuery( '#upload_listing_image' ).val( '' );
            jQuery( '#listingimagediv img' ).attr( 'src', '' );
            jQuery( '#listingimagediv img' ).hide();
            jQuery( '#remove_listing_image_button' ).attr( 'id', 'upload_listing_image_button' );
            jQuery( '#upload_listing_image_button' ).text( 'Выбрать картинку' );
        });

    });
});