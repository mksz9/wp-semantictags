//do some actions, when DOM has loaded
jQuery(document).ready(function() {
    var propertyLine = jQuery('.st_connection_wrapper:last-child');
    //get list of all possible tags, for dropdown field if connection should be an uri
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
    //event handler, when new line for a connection should be added
    jQuery('.st_connection_add_line').live('click', function(e) {
        e.preventDefault();
        newPropertyLine = propertyLine.clone();
        newPropertyLine.find('.st_connection_value_wrapper').html('');
        jQuery('<div class="st_connection_wrapper">' + newPropertyLine.html() + '</div>').insertAfter('.st_connection_wrapper:last-child');
        setLineButtons();
    });
    //event handler, when a connection row should be removed
    jQuery('.st_connection_remove_line').live('click', function(e) {
        e.preventDefault();
        jQuery(this).parent().parent().remove();
        setLineButtons();
    });
    //re-transform the delete and new line buttons
    setLineButtons();
    //switch between textinput and select when adding a new connection with its predicate type
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
    //re-render all hidden fields by its connection configuration before submitting the form
    jQuery('input[name="submit"]').live('hover', function() {
        jQuery('.st_connection_wrapper').each(function() {
            data = {};
            data.o = '';
            data.p = jQuery(this).find('select[name="st_connection_predicate"]').val();
            data.o_type = jQuery(this).find('select[name="st_connection_object_type"]').val();
            if (data.o_type == 'uri') {
                data.o = jQuery(this).find('select[name="st_connections_object_uri"]').val();
            } else if (data.o_type == 'literal') {
                data.o = jQuery(this).find('input[name="st_connection_object_val"]').val();
            }
            jQuery(this).find('input[name="st_connection_config[]"]').val(JSON.stringify(data));
        });
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