<?php

namespace Wappointment\Services\Wappointment;

use Wappointment\WP\Helpers as WPHelpers;

class EmailList extends API
{
    public $statuses = [];

    public function __construct()
    {
        $dbvalue = WPHelpers::getOption('subscribed_status');
        $this->statuses = $dbvalue === false ? [] : $dbvalue;
        parent::__construct();
    }

    public function subscribe($email, $list = 'default')
    {

        $response = $this->client->request('POST', $this->call('/api/subscribe/list'), [
            'form_params' => [
                'list' => $list,
                'email' => $email
            ]
        ]);

        if ($this->isAlreadySubscribed($email, $list)) {
            return true;
        }

        $result = $this->processResponse($response);

        if ($result) {
            $this->recordSubscription($email, $list);
        }

        return ['response' => $result, 'statuses' => $this->statuses];
    }
    protected function recordSubscription($email, $list)
    {
        $emailStatus = $this->getEmailStatus($email);

        if ($emailStatus === false) {
            $emailStatus['status'] = ['email' => $email, 'lists' => []];
            $this->statuses[] = $emailStatus['status'];
            end($this->statuses);
            $emailStatus['key'] = key($this->statuses);
        }
        $emailStatus['status']['lists'][$list] = true;
        $this->statuses[$emailStatus['key']] = $emailStatus['status'];
        WPHelpers::setOption('subscribed_status', $this->statuses);
        return $this->statuses;
    }
    protected function isAlreadySubscribed($email, $list)
    {
        $status = $this->getEmailStatus($email);
        return  !empty($status) && !empty($status['lists'][$list]) ? true : false;
    }

    protected function getEmailStatus($email)
    {
        if (!empty($this->statuses)) {
            foreach ($this->statuses as $key => $statusEmail) {
                if ($statusEmail['email'] == $email) {
                    return ['key' => $key, 'status' => $statusEmail];
                }
            }
        }
        return false;
    }
}
