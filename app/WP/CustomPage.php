<?php

namespace Wappointment\WP;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Settings;

class CustomPage
{
    public $slug = 'wappointment';
    public $page_title = 'Wappointment page';
    public $page_content = '[wappointment_page]';

    public function install()
    {
        $page_exist_already = get_page_by_path($this->slug, OBJECT, $this->slug);
        if (empty($page_exist_already)) {
            $page_id = wp_insert_post($this->dataPage());
        } else {
            $page_id = $page_exist_already->ID;
        }

        Settings::save('front_page', $page_id, true);
    }

    protected function dataPage()
    {
        return [
            'post_status' => 'publish',
            'post_type' => $this->slug,
            'post_author' => 1,
            'post_content' => $this->page_content,
            'post_title' => $this->page_title,
            'post_name' => $this->slug
        ];
    }

    public function makeEditablePage()
    {
        $dataPage = $this->dataPage();
        $dataPage['post_type'] = 'page';
        $pageid = wp_insert_post($dataPage);
        Settings::save('front_page', $pageid);
    }

    public function boot()
    {
        $showItf = false;
        register_post_type(
            $this->slug,
            [
                'labels' => 'Wappointment',
                'public' => true,
                'has_archive' => false,
                'show_ui' => $showItf,
                'show_in_menu' => $showItf,
                'rewrite' => false,
                'show_in_nav_menus' => false,
                'can_export' => false,
                'publicly_queryable' => true,
                'exclude_from_search' => true,
            ]
        );

        // when on the wappointment page scan the title and the content
        if ($this->isDisplayed()) {
            add_filter('wp_title', [$this, 'metaPageTitle']);
            add_filter('the_title', [$this, 'scanTitle']);
            add_action('init', ['\\Wappointment\\WP\\Helpers', 'enqueueFrontScripts'], 98);
            //\Wappointment\WP\Helpers::enqueueFrontScripts();
        }
    }

    public function isDisplayed()
    {
        if (!empty(WPHelpers::requestGet()->input('appointmentkey'))) {
            return true;
        }
        return !empty(WPHelpers::requestGet()->input($this->slug));
    }

    public function isAddEventToCalendarPage()
    {
        return !empty(WPHelpers::requestGet()->input('view')) && strpos(WPHelpers::requestGet()->input('view'), 'add-event-to-calendar') !== false;
    }

    public function isReschedulePage()
    {
        return !empty(WPHelpers::requestGet()->input('view')) && strpos(WPHelpers::requestGet()->input('view'), 'reschedule-event') !== false;
    }

    public function isCancelPage()
    {
        return !empty(WPHelpers::requestGet()->input('view')) && strpos(WPHelpers::requestGet()->input('view'), 'cancel-event') !== false;
    }

    public function isNewAppointmentPage()
    {
        return WPHelpers::requestGet()->input('view') == 'new-event';
    }

    public function isViewAppointmentPage()
    {
        return WPHelpers::requestGet()->input('view') == 'view-event';
    }

    public function getPageTitle()
    {
        if ($this->isAddEventToCalendarPage()) {
            return (new \Wappointment\Services\WidgetSettings)->get()['confirmation']['savetocal'];
        }
        if ($this->isReschedulePage()) {
            return (new \Wappointment\Services\WidgetSettings)->get()['reschedule']['page_title'];
        }
        if ($this->isCancelPage()) {
            return (new \Wappointment\Services\WidgetSettings)->get()['cancel']['page_title'];
        }
        if ($this->isNewAppointmentPage()) {
            return Settings::get('new_booking_link');
        }

        return '';
    }

    public static function getPageContent()
    {
        return '<div class="wappointment_page"></div>';
    }

    public function metaPageTitle($title)
    {
        $replace = !empty($this->getPageTitle()) ? $this->getPageTitle() : '';
        return str_replace($this->page_title, $replace, $title);
    }

    public function scanTitle($title)
    {
        return str_replace($this->page_title, $this->getPageTitle(), $title);
    }
}
