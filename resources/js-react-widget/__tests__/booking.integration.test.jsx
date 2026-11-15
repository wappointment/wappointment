import React from 'react';
import { act, fireEvent, render, screen, waitFor } from '@testing-library/react';
import BookingWidget from '../components/BookingWidget';
import bookingFetch from '../api';
import apiFetch from '../../js-react/utils/apiFetch';
import Settings from '../../js-react/components/Settings';

jest.mock('../api');
jest.mock('@wordpress/i18n', () => ({ __: (text) => text }), { virtual: true });

const start = 1893571200;
const catalog = {
    services: [{ id: 1, name: 'Consultation', durations: [30], fields: ['name', 'email'] }],
    calendars: [{ id: 2, name: 'Organizer', timezone: 'Europe/Paris' }],
    locations: [{ id: 3, name: 'Office', type: 1, fields: [] }],
    calendar_service: [{ calendar_id: 2, service_id: 1 }],
    service_location: [{ service_id: 1, location_id: 3 }],
};
const appointment = { id: 10, start, end: start + 1800, status: 1, edit_key: 'a'.repeat(32), notification_sent: false };

beforeEach(() => {
    window.history.replaceState({}, '', '/');
    bookingFetch.mockReset();
    bookingFetch.mockImplementation((path) => {
        if (path === '/booking/catalog') return Promise.resolve(catalog);
        if (path === '/booking/availability') return Promise.resolve({ timezone: 'Europe/Paris', slots: [{ start, end: start + 1800 }] });
        if (path === '/booking') return Promise.resolve(appointment);
        return Promise.reject(new Error(`Unexpected booking endpoint: ${path}`));
    });
});

async function chooseSlot() {
    render(<BookingWidget title="Reserve a meeting" />);
    fireEvent.click(screen.getByRole('button', { name: 'Reserve a meeting' }));
    await screen.findByRole('option', { name: 'Consultation' });
    fireEvent.change(screen.getByLabelText('Service', { exact: true }), { target: { value: '1' } });
    fireEvent.change(screen.getByLabelText('Calendar', { exact: true }), { target: { value: '2' } });
    fireEvent.change(screen.getByLabelText('Appointment location'), { target: { value: '3' } });
    fireEvent.change(screen.getByLabelText('Duration (minutes)'), { target: { value: '30' } });
    fireEvent.change(screen.getByLabelText('Date in organizer timezone'), { target: { value: '2030-01-02' } });
    fireEvent.click(screen.getByRole('button', { name: 'Show available times' }));
    await screen.findByLabelText('Available time');
    fireEvent.change(screen.getByLabelText('Available time'), { target: { value: String(start) } });
    fireEvent.change(screen.getByLabelText('Full name'), { target: { value: 'Widget Customer' } });
    fireEvent.change(screen.getByLabelText('Email', { exact: true }), { target: { value: 'customer@example.invalid' } });
}

test('only confirms a persisted booking and does not invent an email delivery', async () => {
    await chooseSlot();
    expect(bookingFetch).toHaveBeenCalledWith('/booking/availability', {
        service_id: '1', calendar_id: '2', location_id: '3', duration: '30', date: '2030-01-02',
    });
    fireEvent.click(screen.getByRole('button', { name: 'Book appointment', exact: true }));
    expect(await screen.findByRole('heading', { name: 'Appointment confirmed' })).toBeVisible();
    expect(screen.getByText(/no confirmation email was sent/)).toBeVisible();
    expect(bookingFetch).toHaveBeenCalledWith('/booking', expect.objectContaining({
        service_id: '1', calendar_id: '2', location_id: '3', duration: '30',
        start: String(start), name: 'Widget Customer', email: 'customer@example.invalid',
    }));
});

test('opens the appointment management link embedded in a confirmation email', async () => {
    window.history.replaceState({}, '', `/#wappointment=${appointment.edit_key}`);
    bookingFetch.mockImplementation((path, data) => {
        if (path === '/booking/manage' && data.action === 'view') return Promise.resolve(appointment);
        return Promise.resolve(catalog);
    });

    render(<BookingWidget title="Reserve a meeting" />);

    expect(await screen.findByRole('heading', { name: 'Appointment confirmed' })).toBeVisible();
    expect(bookingFetch).toHaveBeenCalledWith('/booking/manage', {
        action: 'view', edit_key: appointment.edit_key,
    }, expect.any(AbortSignal));
});

test('cancellation sends only the management contract fields', async () => {
    await chooseSlot();
    fireEvent.click(screen.getByRole('button', { name: 'Book appointment', exact: true }));
    await screen.findByRole('heading', { name: 'Appointment confirmed' });
    bookingFetch.mockResolvedValue({ ...appointment, status: -1 });
    fireEvent.click(screen.getByRole('button', { name: 'Cancel appointment', exact: true }));
    await screen.findByRole('heading', { name: 'Appointment cancelled' });
    expect(bookingFetch).toHaveBeenLastCalledWith('/booking/manage', { action: 'cancel', edit_key: appointment.edit_key });
});

test('rescheduling uses only the secret key and selected new time', async () => {
    await chooseSlot();
    fireEvent.click(screen.getByRole('button', { name: 'Book appointment', exact: true }));
    await screen.findByRole('heading', { name: 'Appointment confirmed' });
    fireEvent.click(screen.getByRole('button', { name: 'Reschedule appointment', exact: true }));
    bookingFetch.mockResolvedValue({ timezone: 'Europe/Paris', slots: [{ start, end: start + 1800 }] });
    fireEvent.click(screen.getByRole('button', { name: 'Show available times', exact: true }));
    await screen.findByLabelText('Available time');
    expect(bookingFetch).toHaveBeenLastCalledWith('/booking/availability', { edit_key: appointment.edit_key, date: '2030-01-02' });
    fireEvent.change(screen.getByLabelText('Available time'), { target: { value: String(start) } });
    bookingFetch.mockResolvedValue(appointment);
    fireEvent.click(screen.getByRole('button', { name: 'Confirm new time', exact: true }));
    await screen.findByRole('heading', { name: 'Appointment confirmed' });
    expect(bookingFetch).toHaveBeenLastCalledWith('/booking/manage', {
        action: 'reschedule', edit_key: appointment.edit_key, date: '2030-01-02', start: String(start),
    });
});

test('shows a real conflict instead of a confirmation', async () => {
    await chooseSlot();
    bookingFetch.mockImplementation((path) => path === '/booking'
        ? Promise.reject(new Error('This time is no longer available.')) : Promise.resolve(catalog));
    fireEvent.click(screen.getByRole('button', { name: 'Book appointment', exact: true }));
    expect(await screen.findByRole('alert')).toHaveTextContent('This time is no longer available.');
    expect(screen.queryByRole('heading', { name: 'Appointment confirmed' })).not.toBeInTheDocument();
});

test('manual approval is not presented as a confirmed appointment', async () => {
    await chooseSlot();
    bookingFetch.mockResolvedValue({ ...appointment, status: 0 });
    fireEvent.click(screen.getByRole('button', { name: 'Book appointment', exact: true }));
    expect(await screen.findByRole('heading', { name: 'Awaiting approval' })).toBeVisible();
});

test('does not send two bookings while a submission is pending', async () => {
    await chooseSlot();
    let finish;
    bookingFetch.mockImplementation(() => new Promise((resolve) => { finish = resolve; }));
    const button = screen.getByRole('button', { name: 'Book appointment', exact: true });
    fireEvent.click(button);
    fireEvent.click(button);
    expect(bookingFetch.mock.calls.filter(([path]) => path === '/booking')).toHaveLength(1);
    await act(async () => finish(appointment));
    await screen.findByRole('heading', { name: 'Appointment confirmed' });
});

test('rejects stale slot results after changing the selected date', async () => {
    await chooseSlot();
    let finish;
    bookingFetch.mockImplementation(() => new Promise((resolve) => { finish = resolve; }));
    fireEvent.click(screen.getByRole('button', { name: 'Show available times' }));
    fireEvent.change(screen.getByLabelText('Date in organizer timezone'), { target: { value: '2030-01-03' } });
    await act(async () => finish({ timezone: 'Europe/Paris', slots: [{ start, end: start + 1800 }] }));
    await waitFor(() => expect(screen.getByRole('region')).toHaveAttribute('aria-busy', 'false'));
    expect(screen.queryByLabelText('Available time')).not.toBeInTheDocument();
});

test('admin setup errors retain the server explanation and response details', async () => {
    const previousFetch = global.fetch;
    window.wappointmentData = { apiUrl: '/wp-json/wappointment/v1', nonce: 'test-nonce' };
    const response = JSON.stringify({ message: 'This configuration cannot be overwritten.' });
    global.fetch = jest.fn().mockResolvedValue({
        ok: false, status: 409, statusText: 'Conflict',
        headers: { get: () => 'application/json; charset=UTF-8' },
        text: () => Promise.resolve(response),
    });
    try {
        await expect(apiFetch('/booking-settings')).rejects.toMatchObject({
            status: 409, response,
            message: 'HTTP 409: Conflict - This configuration cannot be overwritten.',
        });
    } finally {
        global.fetch = previousFetch;
        delete window.wappointmentData;
    }
});

test('settings save only changed fields rather than replaying legacy settings', async () => {
    const previousFetch = global.fetch;
    window.wappointmentData = { apiUrl: '/wp-json/wappointment/v1', nonce: 'test-nonce' };
    global.fetch = jest.fn()
        .mockResolvedValueOnce({
            ok: true,
            json: () => Promise.resolve({ success: true, data: { settings: {
                general: { approval_mode: 1 },
                notifications: {},
                advanced: { legacy_extension_setting: { preserve: true } },
            } } }),
        })
        .mockResolvedValueOnce({ ok: true, json: () => Promise.resolve({ success: true, data: { success: true, message: 'Settings saved successfully' } }) });
    try {
        render(<Settings />);
        const approval = await screen.findByLabelText('Booking Approval Mode');
        expect(screen.getByRole('button', { name: 'Save Settings' })).toBeDisabled();
        fireEvent.change(approval, { target: { value: '0' } });
        fireEvent.click(screen.getByRole('button', { name: 'Save Settings' }));
        await screen.findByText('Settings saved successfully');
        expect(global.fetch).toHaveBeenLastCalledWith('/wp-json/wappointment/v1/settings', expect.objectContaining({
            method: 'POST', body: JSON.stringify({ approval_mode: 0 }),
        }));
        expect(screen.getByRole('button', { name: 'Save Settings' })).toBeDisabled();
    } finally {
        global.fetch = previousFetch;
        delete window.wappointmentData;
    }
});
