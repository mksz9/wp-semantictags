jQuery(document).ready(function() {
    var propertyLine = jQuery('.st_connection_wrapper:last-child');
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            action: 'semantictags_retrieve_tags',
        },
        success: function(response) {
            tags = JSON.parse(response);
        },
        async: false
    });
    jQuery('.st_connection_add_line').live('click', function(e) {
        e.preventDefault();
        newPropertyLine = propertyLine.clone();
        newPropertyLine.find('.st_connection_value_wrapper').html('');
        jQuery('<div class="st_connection_wrapper">' + newPropertyLine.html() + '</div>').insertAfter('.st_connection_wrapper:last-child');
        setLineButtons();
    });
    jQuery('.st_connection_remove_line').live('click', function(e) {
        e.preventDefault();
        jQuery(this).parent().parent().remove();
        setLineButtons();
    });
    setLineButtons();
    jQuery('select[name="st_connection_object_type"]').live('change', function() {
        var selection = jQuery(this).val();
        if (selection == 'literal') {
            jQuery(this).parent().find('.st_connection_value_wrapper').html('<input type="text" name="st_connection_object_val">');
        } else if (selection == 'uri') {
            jQuery(this).parent().find('.st_connection_value_wrapper').html(tags);
        } else {
            jQuery(this).parent().find('.st_connection_value_wrapper').html('');
        }
    });
    //jQuery('input[name="submit"]').click(function(e) {

        jQuery('.st_connection_wrapper').live('change', function() {
            data = {};
            data.o = '';
            data.p = jQuery(this).find('input[name="st_connection_predicate"]').val();
            data.o_type = jQuery(this).find('select[name="st_connection_object_type"]').val();
            console.log("o_type: "+data.o_type);
            console.log("now checking o_type");
            if (data.o_type == 'uri') {
                console.log("is uri");
                data.o = jQuery(this).find('select[name="st_connections_object_uri"]').val();
            } else if (data.o_type == 'literal') {
                console.log("is literal")
                data.o = jQuery(this).find('input[name="st_connection_object_val"]').val();
            }
            console.log(data);
            jQuery(this).find('input[name="st_connection_config[]"]').val(JSON.stringify(data));
        });

});

function setLineButtons() {
    var set = jQuery('.st_connection_wrapper');
    var len = set.length;
    jQuery('.st_connection_wrapper').each(function(index, elem) {
        if (index != (len - 1)) {
            jQuery(this).find('.st_line_buttons').html('<a href="#" class="st_connection_remove_line"><span class="dashicons dashicons-dismiss"></span></a>');
        } else {
            jQuery(this).find('.st_line_buttons').html('<a href="#" class="st_connection_add_line"><span class="dashicons dashicons-plus-alt"></span></a>');
        }
    });
}