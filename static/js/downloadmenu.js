jQuery(document).ready(function() {
    var refreshTab = function () {
	    jQuery('#download .content section').css('display', 'none');
	    jQuery('#download .content section h3').css('display', 'none');
	    jQuery(jQuery('#download .icons li a.active').attr('href')).css('display', 'block');	
	}

    jQuery('#download .icons li a').each(function() {
        jQuery(this).click(function(event) {
            jQuery('#download .icons li a.active').removeClass('active');
            jQuery(this).addClass('active');
            
            refreshTab();
            
            //window.location.hash = jQuery(this).attr('href');
            //event.preventDefault();
        });
    });
    
    if (window.location.hash)
    {
        jQuery('#download .icons li a[href="' + window.location.hash + '"]').addClass('active');
    } else {
        jQuery('#download .icons li a').eq(0).addClass('active');
    }

    refreshTab();
});
