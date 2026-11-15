<?php
declare(strict_types=1);

namespace Wappointment\Auth;

final class AccessPolicy
{
    public function can(string $operation): bool
    {
        if (!in_array($operation, [
            'manage', 'appointments.view', 'appointments.change', 'appointments.confirm', 'appointments.cancel',
            'appointments.book', 'appointments.edit', 'appointments.lock', 'appointments.delete',
            'appointments.force_delete',
            'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
            'self.view', 'self.weekly', 'self.statuses',
        ], true)) {
            throw new \LogicException('Unknown Wappointment permission: ' . $operation);
        }
        if (!is_user_logged_in()) {
            return false;
        }
        if ($this->isManager()) {
            return true;
        }
        if (str_starts_with($operation, 'self.')) {
            return current_user_can('wappo_self_man')
                && ($operation === 'self.view' || current_user_can('wappo_self_weekly'));
        }
        if (str_starts_with($operation, 'clients.')) {
            return current_user_can('wappo_clients_man') && match ($operation) {
                'clients.view' => true,
                'clients.edit' => current_user_can('wappo_clients_edit'),
                'clients.delete' => current_user_can('wappo_clients_del'),
                'clients.create' => false,
            };
        }
        if (!current_user_can('wappo_calendar_man')) {
            return false;
        }
        return match ($operation) {
            'manage' => false,
            'appointments.view' => true,
            'appointments.confirm' => current_user_can('wappo_calendar_confirm'),
            'appointments.cancel' => current_user_can('wappo_calendar_cancel'),
            'appointments.change' => current_user_can('wappo_calendar_confirm') || current_user_can('wappo_calendar_cancel'),
            'appointments.book' => current_user_can('wappo_calendar_book') && current_user_can('wappo_clients_man'),
            'appointments.edit' => current_user_can('wappo_calendar_reschedule'),
            'appointments.lock' => true,
            'appointments.delete' => current_user_can('wappo_calendar_cancel'),
            'appointments.force_delete' => false,
        };
    }

    public function assert(string $operation): void
    {
        if (!$this->can($operation)) {
            throw new \RuntimeException(__('You are not allowed to perform this Wappointment operation.', 'wappointment'), 403);
        }
    }

    public function isManager(): bool
    {
        return is_user_logged_in()
            && (current_user_can('manage_options') || current_user_can('wappointment_manager'));
    }

    public function appointmentOwner(): ?int
    {
        $this->assert('appointments.view');
        return $this->isManager() ? null : get_current_user_id();
    }

    public function clientOwner(): ?int
    {
        $this->assert('clients.view');
        return $this->isManager() ? null : get_current_user_id();
    }

    /** @return array<string, string> */
    public function pages(): array
    {
        return [
            'wappointment-appointments' => 'appointments.view',
            'wappointment-jobs' => 'manage',
            'wappointment-clients' => 'clients.view',
            'wappointment-settings' => 'manage',
            'wappointment-my-availability' => 'self.view',
            'wappointment-booking-setup' => 'manage',
            'wappointment-diagnostics' => 'manage',
        ];
    }

    /** @return list<string> */
    public function allowedPages(): array
    {
        return array_keys(array_filter($this->pages(), fn(string $operation): bool => $this->can($operation)));
    }

    public function menuCapability(string $page): string
    {
        if (!isset($this->pages()[$page])) {
            throw new \LogicException('Unknown Wappointment page: ' . $page);
        }
        if ($this->isManager()) {
            return current_user_can('manage_options') ? 'manage_options' : 'wappointment_manager';
        }
        return match ($this->pages()[$page]) {
            'appointments.view' => 'wappo_calendar_man',
            'clients.view' => 'wappo_clients_man',
            'self.view' => 'wappo_self_man',
            default => 'manage_options',
        };
    }
}
