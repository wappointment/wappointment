import React from 'react';
import { fireEvent, render, screen } from '@testing-library/react';
import App from '../../js-react/components/App';
import Appointments from '../../js-react/components/Appointments';
import apiFetch from '../../js-react/utils/apiFetch';

jest.mock('../../js-react/utils/apiFetch');
jest.mock('@wordpress/i18n', () => ({ __: (text) => text }), { virtual: true });
jest.mock('../../js-react/components/Jobs', () => () => <div>Unscoped jobs</div>);
jest.mock('../../js-react/components/Clients', () => () => <div>Unscoped clients</div>);
jest.mock('../../js-react/components/Settings', () => () => <div>Unscoped settings</div>);
jest.mock('../../js-react/components/BookingSetup', () => () => <div>Unscoped setup</div>);
jest.mock('../../js-react/components/Diagnostics', () => () => <div>Unscoped diagnostics</div>);

const appointment = {
    id: '18', start_at: '2030-01-02 09:00:00', end_at: '2030-01-02 09:30:00',
    status: '0', client_name: 'Owned client', client_email: 'owned@example.invalid',
};

beforeEach(() => {
    apiFetch.mockReset();
    apiFetch.mockResolvedValue({ data: { appointments: [appointment] } });
    window.wappointmentData = {
        currentPage: 'wappointment-appointments',
        allowedPages: ['wappointment-appointments'],
        permissions: {},
    };
});

afterEach(() => {
    delete window.wappointmentData;
});

test('calendar view access alone does not display confirmation or cancellation controls', async () => {
    render(<Appointments />);
    expect(await screen.findByText(/Owned client/)).toBeVisible();
    expect(screen.queryByRole('button', { name: 'Confirm' })).not.toBeInTheDocument();
    expect(screen.queryByRole('button', { name: 'Cancel' })).not.toBeInTheDocument();
});

test.each([
    ['appointmentsConfirm', 'Confirm', 'Cancel'],
    ['appointmentsCancel', 'Cancel', 'Confirm'],
])('appointment controls honor the distinct %s permission', async (capability, allowed, denied) => {
    window.wappointmentData.permissions[capability] = true;
    render(<Appointments />);
    expect(await screen.findByRole('button', { name: allowed })).toBeVisible();
    expect(screen.queryByRole('button', { name: denied })).not.toBeInTheDocument();
});

test('a permission revoked after bootstrap surfaces the server denial rather than updating the row', async () => {
    window.wappointmentData.permissions.appointmentsConfirm = true;
    render(<Appointments />);
    const confirm = await screen.findByRole('button', { name: 'Confirm' });
    apiFetch.mockRejectedValueOnce(new Error('You are not allowed to perform this Wappointment operation.'));
    fireEvent.click(confirm);
    expect(await screen.findByRole('alert')).toHaveTextContent('You are not allowed');
    expect(screen.getByText('Pending')).toBeVisible();
    expect(apiFetch).toHaveBeenLastCalledWith('/appointments', {
        method: 'POST', body: JSON.stringify({ id: 18, status: 1 }),
    });
});

test('direct SPA rendering fails closed when the server does not list the page as allowed', () => {
    window.wappointmentData.currentPage = 'wappointment-clients';
    const { rerender } = render(<App />);
    expect(screen.getByText('Access denied')).toBeVisible();
    expect(screen.queryByText('Unscoped clients')).not.toBeInTheDocument();
    delete window.wappointmentData.allowedPages;
    rerender(<App />);
    expect(screen.getByText('Access denied')).toBeVisible();
    expect(apiFetch).not.toHaveBeenCalled();
});

test('SPA menu navigation cannot mount an unpermitted screen', async () => {
    render(<App />);
    await screen.findByText(/Owned client/);
    const link = document.createElement('a');
    link.href = '/wp-admin/admin.php?page=wappointment-clients';
    link.textContent = 'Clients';
    document.body.appendChild(link);
    try {
        fireEvent.click(link);
        expect(screen.getByText('Access denied')).toBeVisible();
        expect(screen.queryByText('Unscoped clients')).not.toBeInTheDocument();
    } finally {
        link.remove();
    }
});

test('managers can render pages explicitly listed in the bootstrap', () => {
    window.wappointmentData.currentPage = 'wappointment-clients';
    window.wappointmentData.allowedPages.push('wappointment-clients');
    render(<App />);
    expect(screen.getByText('Unscoped clients')).toBeVisible();
    expect(screen.queryByText('Access denied')).not.toBeInTheDocument();
});
