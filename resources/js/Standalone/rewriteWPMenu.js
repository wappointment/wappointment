export default function (pagename) {
    //a quick cleaning to remove duplicate parent menu element
    window.jQuery(function($){
        $('#toplevel_page_wappointment_calendar li.current, #toplevel_page_wappointment_calendar li a.current').removeClass('current')
        let testpagename = pagename
        if(['general','reminders', 'notifications', 'advanced', 'addonstab'].indexOf(testpagename) !== -1){
          testpagename = 'wappointment_settings'
        }
        let menuIndex = window.wappointmentBackMenus.indexOf(testpagename) + 2
          $('#toplevel_page_wappointment_calendar ul.wp-submenu li:nth-child('+menuIndex+') , #toplevel_page_wappointment_calendar ul.wp-submenu li:nth-child('+menuIndex+') a')
          .addClass('current')
    });
}
