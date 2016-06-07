(function($) {
    var jqAppend = $.fn.append;
    $.fn.append = function() {
        // Make a list of arguments that are jQuery objects
        var appendages = $.makeArray(arguments).filter(function(arg) {
            return arg instanceof $;
        });
        // Call the actual function
        var returnValue = jqAppend.apply(this, arguments);
        // Trigger "append" event on all jQuery objects that were appended
        for (var i = 0; i < appendages.length; ++i) {
            appendages[i].trigger('append');
        }
        return returnValue;
    };
})(jQuery)

function semanticTag(name, type, desc) {
    this.name = name;
    this.type = type;
    this.desc = desc;
}
var semanticData = {
    tags: new Array(),
    addTag: function(name, type, desc) {
        if (this.hasTag(name)) {
            if (type == null) {
                this.removeTag(name);
            }
            i = this.getTagIndex(name);
            this.tags[i].name = name;
            this.tags[i].type = type;
            this.tags[i].desc = desc;
        } else {
            if (type != null) {
                this.tags.push(new semanticTag(name, type, desc));
            }
        }
    },
    addTagsToFormField: function(elem) {
        elem.val(JSON.stringify(this.tags));
    },
    loadTagsFromFormField: function(elem) {
        this.tags = new Array();
        var tagsRaw = JSON.parse(elem.val());
        for (var i = 0; i < tagsRaw.length; i++) {
            this.tags.push(new semanticTag(tagsRaw[i].name, tagsRaw[i].type, tagsRaw[i].desc));
        }
    },
    hasTag: function(name) {
        for (var i = 0; i < this.tags.length; i++) {
            if (this.tags[i].name == name) {
                return true;
            }
        }
        return false;
    },
    getTag: function(name) {
        for (var i = 0; i < this.tags.length; i++) {
            if (this.tags[i].name == name) {
                return this.tags[i];
            }
        }
        return null;
    },
    getTagIndex: function(name) {
        for (var i = 0; i < this.tags.length; i++) {
            if (this.tags[i].name == name) {
                return i;
            }
        }
        return null;
    },
    removeTag: function(name) {
        i = this.getTagIndex(name);
        this.tags.splice(i, 1);
    }
}
jQuery(document).ready(function() {
    //retrieving datatypes from vocabulary
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            action: 'semantictags_retrieve_datatypes',
        },
        success: function(response) {
            datatypes = JSON.parse(response);
        },
        async: false
    });
    semanticData.loadTagsFromFormField(jQuery('input[name="semantictagsdata"]'));
    jQuery('.tagchecklist').bind('append', function(event) {
        tmpXButton = jQuery(this).find('a.ntdelbutton:last-child');
        semanticEditButton = jQuery('<a class="nteditbutton">Edit</a>');
        if (tmpXButton.parent().find('.nteditbutton').length == 0) {
            tmpXButton.after(semanticEditButton);
        }
        tagsDiv = jQuery('.tagsdiv');
        tagName = getTagFromElement(tmpXButton.parent());
        val = [];
        val.type = '';
        val.desc = '';
        if (semanticData.hasTag(tagName)) {
            tagsData = semanticData.getTag(tagName);
            val.type = tagsData.type;
            val.desc = tagsData.desc;
        }
        semanticEditOverlay = jQuery('<div class="semanticeditoverlay" data-tagname="' + tagName + '"><a class="closeoverlay">X</a><h4>Semantik konfigurieren</h4><table cellpadding="0" cellspacing="0"><tr><td>Name:</td><td>' + tagName + '</td></tr></table></div>')
        semanticEditOverlay.find('table').append('<tr><td>Datentyp:</td><td><select name="type"><option value="" disabled></option></select></td></tr>');
        semanticEditOverlay.find('table').append('<tr><td>Beschreibung:</td><td><textarea name="desc">' + val.desc + '</textarea></td></tr>');
        semanticEditOverlay.find('table').append('<tr><td colspan="2"><a class="button button-primary button-large save-semantictag">Speichern</a></td></tr>');
        renderTypeOptions(semanticEditOverlay, val.type, datatypes);
        if (jQuery(".semanticeditoverlay[data-tagname='" + tagName + "']").length <= 0) {
            jQuery('.tagsdiv').after(semanticEditOverlay);
        }
        jQuery('.semanticeditoverlay').width(tagsDiv.parent().width());
        jQuery('.semanticeditoverlay').height(tagsDiv.parent().height() + 13 + 12);
    });
    jQuery('body').delegate('.nteditbutton', 'click', function() {
        jQuery('[data-tagname="' + getTagFromElement(jQuery(this).parent()) + '"]').show();
    });
    jQuery('body').delegate('.closeoverlay', 'click', function() {
        jQuery(this).parent().hide();
    });
    jQuery('body').delegate('.save-semantictag', 'click', function() {
        var overlay = jQuery(this).closest('.semanticeditoverlay');
        semanticData.addTag(overlay.data('tagname'), overlay.find('select[name="type"]').val(), overlay.find('textarea[name="desc"]').val());
        semanticData.addTagsToFormField(jQuery('input[name="semantictagsdata"]'));
        overlay.hide();
    });
    jQuery('.ntdelbutton').live('click', function(e) {
        tagName = getTagFromElement(jQuery(this).parent());
        semanticData.removeTag(tagName);
        semanticData.addTagsToFormField(jQuery('input[name="semantictagsdata"]'));
        jQuery(".semanticeditoverlay[data-tagname='" + tagName + "']").remove();
    });
});

function renderTypeOptions(overlay, currentType, datatypes) {
    var selectedFound = false;
    for (var i = 0; i < datatypes.length; i++) {
        var selected = '';
        if (datatypes[i] == currentType && !selectedFound) {
            selected = ' selected';
            selectedFound = true;
        }
        var splitted = datatypes[i].split(':');
        overlay.find('select[name="type"]').append('<option value="' + datatypes[i] + '"' + selected + '>' + splitted[1] + '</option>');
    }
}

function getTagFromElement(elem) {
    return elem[0].outerText.substring(6);
}