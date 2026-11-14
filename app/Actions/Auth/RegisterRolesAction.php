<?php
declare(strict_types=1);

namespace Wappointment\Actions\Auth;

class RegisterRolesAction
{
    public function handle(): void
    {
        $capabilities = [
            'read', 'edit_posts',
            'wappo_calendar_man', 'wappo_calendar_add_busy', 'wappo_calendar_del_busy',
            'wappo_calendar_add_free', 'wappo_calendar_del_free', 'wappo_calendar_reschedule',
            'wappo_calendar_cancel', 'wappo_calendar_book', 'wappo_calendar_confirm',
            'wappo_self_man', 'wappo_self_weekly', 'wappo_self_services',
            'wappo_self_connect_account', 'wappo_self_add_ics', 'wappo_self_del_ics',
            'wappo_self_unpublish', 'wappo_self_cf', 'wappo_self_shortcode',
            'wappo_clients_man', 'wappo_clients_del', 'wappo_clients_edit',
        ];
        foreach ([
            'wappointment_staff' => [__('Wappointment Staff', 'wappointment'), array_fill_keys($capabilities, true)],
            'wappointment_manager' => [__('Wappointment manager', 'wappointment'), ['read' => true, 'edit_posts' => true]],
        ] as $role => [$label, $caps]) {
            // Existing roles may have deliberate restrictions; never refresh their grants.
            if (get_role($role) === null && add_role($role, $label, $caps) === null) {
                throw new \RuntimeException('Could not register Wappointment role: ' . $role);
            }
        }
    }
}
