<?php

namespace Wappointment\Models\Appointment;

use Wappointment\Messages\EmailHelper;
use Wappointment\Services\Jitsi;
use Wappointment\Services\Settings;

trait ManipulateLinks
{
    private $additional_params = null;

    public function setAdditionalLinkParams($added_params)
    {
        $this->additional_params .= $added_params;
    }
    public function additionalLinkParams()
    {
        return $this->additional_params;
    }

    private function getPageLink($view = 'reschedule-event')
    {
        $editKey = $this->edit_key;
        return (new EmailHelper)->getLinkEvent($view) . (empty($editKey) ? '' : '&appointmentkey=' . $editKey) . $this->additionalLinkParams();
    }

    public function getLinkAddEventToCalendar()
    {
        return $this->getPageLink('add-event-to-calendar');
    }

    public function getLinkRescheduleEvent()
    {
        return $this->getPageLink();
    }

    public function getLinkCancelEvent()
    {
        return $this->getPageLink('cancel-event');
    }

    public function getLinkNewEvent()
    {
        return $this->getPageLink('new-event');
    }

    public function videoAppointmentHasLink()
    {
        return $this->isZoom() ? $this->getMeetingLink() : false;
    }

    public function getVideoProvider()
    {
        return $this->getLocationVideo() == 'googlemeet' ? 'google' : $this->getLocationVideo();
    }

    public function getMeetingLink()
    {

        $video_provider = $this->getVideoProvider();
        if ($video_provider == 'jitsi' && $this->canShowLink()) {
            return Jitsi::generate($this);
        }
        $url_meeting_key = $video_provider == 'google' ? 'google_meet_url' : 'join_url';

        return $this->canShowLink() && !empty($video_provider) &&
            !empty($this->options['providers']) &&
            !empty($this->options['providers'][$video_provider]) &&
            !empty($this->options['providers'][$video_provider][$url_meeting_key]) ? $this->options['providers'][$video_provider][$url_meeting_key] : false;
    }

    public function canShowLink()
    {
        $when_shows_link = (int)Settings::get('video_link_shows');
        if (($when_shows_link > 0 && $this->start_at->timestamp - ($when_shows_link * 60) > time()) || $this->isOver()) {
            return false;
        }
        return true;
    }

    public function getLinkViewEvent()
    {
        //if meeting link is already present return it directly.
        $link = $this->videoAppointmentHasLink();
        return $link ? $link : $this->getPageLink('view-event');
    }
}
