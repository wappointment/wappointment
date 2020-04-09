jQuery(function($){
    $( '#toplevel_page_wappointment_calendar.wp-has-current-submenu li a' ).click(function() {
        window.wappointmentrouter.push({ name: this.href.replace(/^.+admin.php\?page\=/,'')});
        return false;
    });
});