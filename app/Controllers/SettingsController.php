<?php

namespace Wappointment\Controllers;

use Wappointment\Services\Settings;
use Wappointment\Services\WidgetSettings;
use Wappointment\Services\TestMail;
use Wappointment\ClassConnect\Request;

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
                return ['message' => 'Settings saved'];
            } else {
                return Settings::save($request->input('key'), $request->input('val'));
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
                'message' => 'Configuration completed! Check your inbox for the test email just sent to your address.'
            ];
        } else {
            if (\Wappointment\ClassConnect\Str::contains(
                $resultEmail['error'],
                ['username', 'password', 'login', 'user', 'credentials']
            )) {
                $this->setError('There seems to be a problem with the credentials you are using.');
                $this->setError($resultEmail['error'], 'debug');
            } else {
                $this->setError('Couldn\'t send test email.');
                $this->setError($resultEmail['error'], 'debug');
            }
        }
    }
}
