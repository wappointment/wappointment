<?php

namespace Wappointment\Controllers;

use Wappointment\Services\Settings;
use Wappointment\Services\WidgetSettings;
use Wappointment\Services\TestMail;
use Wappointment\ClassConnect\Request;
use Wappointment\Helpers\Translations;

class SettingsController extends RestController
{
    public function get(Request $request)
    {
        return Settings::get($request->input('key'));
    }

    public function save(Request $request)
    {
        if ($request->input('settings')) {
            foreach ($request->input('settings') as $key => $value) {
                $msg = Settings::save($key, $value);
            }
            return $msg;
        } else {
            if ($request->input('key') == 'widget') {
                (new WidgetSettings)->save($request->input('val'));
                return ['message' => Translations::get('element_saved')];
            } else {
                Settings::save($request->input('key'), $request->input('val'));
                $data = ['message' => Translations::get('element_saved')];
                if ($request->input('key') == 'payments_order') {
                    $data['methods'] = \Wappointment\Services\Payment::methods();
                }
                return $data;
            }
        }
    }

    public function sendPreviewEmail(Request $request)
    {
        $resultEmail = TestMail::send($request->input('data'), $request->input('recipient'));

        if ($this->isTrueOrFail($resultEmail)) {
            Settings::save('mail_config', $request->input('data'));
            Settings::save('mail_status', true);
            return [
                'message' => __('Configuration completed!', 'wappointment') . ' ' . __('Check your inbox for the test email just sent to your address.', 'wappointment')
            ];
        } else {
            $error = $resultEmail['error'] ?? $error['host']['is_smtp'] ?? '';
            if (\Wappointment\ClassConnect\Str::contains(
                $error,
                ['username', 'password', 'login', 'user', 'credentials']
            )) {
                $this->setError(__('Error with your credentials', 'wappointment'));
                $this->setError($error, 'debug');
            } else {
                $this->setError(__('Couldn\'t send test email.', 'wappointment'));
                $this->setError($error, 'debug');
            }
        }
    }
}
