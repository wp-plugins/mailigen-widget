var $j = jQuery.noConflict();

var error_messages={
    unknown_response:'Unknown response!'
};
var error_box;


$j( document ).ready(function() {

    /**
     * ---------------------
     * MAILIGEN WIDGET
     * ---------------------
     */
    var mg_widget_form = $j( '#mg-widget-form' );
    error_box = $j( ".mg-error-box", mg_widget_form );
    error_box.hide();
    
    $j( mg_widget_form ).submit(function() {
        var data = $j( mg_widget_form ).serialize();
        
        error_box.fadeOut();
        $j( '.mg-error' ).remove();
        
        $j.post( '/wp-content/plugins/mailigen-widget/ajax.php', data, function(response) {
            response = JSON.parse(response);
            
            if(!response) {
                error_box.html('<p>' + error_messages.unknown_response + '</p>').fadeIn();
                
            } else if(response.success) {
                if(response.message.content) {
                    try {
                        $j( ".MailigenWidget" ).html(response.message.content);
                    } catch(e){}
                } else {
                    alert(response.message);
                }
                
            } else {
                error_box.html('<p>' + response.message + '</p>').fadeIn();
                if(response.errors) {
                    $j.each( response.errors, function( key, val ) {
                        $j( '#' + key ).before( $j( '<div class="mg-error">' + val + '</div>' ) );
                    });
                }
            }
            return false;
        });
        return false;
    });

    /**
     * ---------------------
     * MAILIGEN OPTIONS
     * ---------------------
     */

    var mg_options_form = $j( '#mg-options-form' );

    $j( '#mg-fields-list', mg_options_form ).change(function()
    {
        var fields = $j( '#mg-fields-container', mg_options_form );

        fields.html( '' ).show();

        $j( '<div class="mg-preloader">' ).appendTo( fields );

        var data = {
            action: 'reload_fields', 
            mg_list: $j( this ).val()
        };

        $j.post( ajaxurl, data, function( response )
        {
            fields.html( response );
        });
        return false;
    });
});