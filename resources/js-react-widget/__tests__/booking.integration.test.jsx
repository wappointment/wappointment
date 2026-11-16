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
const appointment = { id: 10, start, end: start + 1800, status: 1, edit_key: 'a'.repeat(32), notification_queued: false };

beforeEach(() => {
    window.history.replaceState({}, '', '/');
    window.localStorage.clear();
    window.wappointmentWidget = { locale: 'en-US', rtl: false, privacyPolicyUrl: '', weekStartsOn: 1 };
    bookingFetch.mockReset();
    bookingFetch.mockImplementation((path) => {
        if (path === '/booking/catalog') return Promise.resolve(catalog);
        if (path === '/booking/availability') return Promise.resolve({ timezone: 'Europe/Paris', slots: [{ start, end: start + 1800 }] });
        if (path === '/booking') return Promise.resolve(appointment);
        return Promise.reject(new Error(`Unexpected booking endpoint: ${path}`));
    });
});

test('keeps form labels unique across separately configured widget instances', async () => {
    render(<>
        <BookingWidget title="First booking" configuration={{ open: true }} instanceId="wappointment-1" />
        <BookingWidget title="Second booking" configuration={{ open: true }} instanceId="wappointment-2" />
    </>);

    await waitFor(() => expect(screen.getAllByLabelText('Date in organizer timezone')).toHaveLength(2));
    const dateInputs = screen.getAllByLabelText('Date in organizer timezone');
    expect(dateInputs[0].id).not.toBe(dateInputs[1].id);
    expect(screen.getByRole('region', { name: 'First booking' })).toBeVisible();
    expect(screen.getByRole('region', { name: 'Second booking' })).toBeVisible();
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
    }, expect.any(AbortSignal));
    fireEvent.click(screen.getByRole('button', { name: 'Book appointment', exact: true }));
    expect(await screen.findByRole('heading', { name: 'Appointment confirmed' })).toBeVisible();
    expect(screen.getByText(/no confirmation email will be sent/)).toBeVisible();
    expect(bookingFetch).toHaveBeenCalledWith('/booking', expect.objectContaining({
        service_id: '1', calendar_id: '2', location_id: '3', duration: '30',
        start: String(start), name: 'Widget Customer', email: 'customer@example.invalid',
    }));
});

test('does not warn about email when the confirmation was queued for delivery', async () => {
    bookingFetch.mockImplementation((path) => {
        if (path === '/booking/catalog') return Promise.resolve(catalog);
        if (path === '/booking/availability') return Promise.resolve({ timezone: 'Europe/Paris', slots: [{ start, end: start + 1800 }] });
        if (path === '/booking') return Promise.resolve({ ...appointment, notification_queued: true });
        return Promise.reject(new Error(`Unexpected booking endpoint: ${path}`));
    });
    await chooseSlot();
    fireEvent.click(screen.getByRole('button', { name: 'Book appointment', exact: true }));
    expect(await screen.findByRole('heading', { name: 'Appointment confirmed' })).toBeVisible();
    expect(screen.queryByText(/no confirmation email/)).toBeNull();
});

test('downloads an escaped calendar event using the confirmed appointment time and details', async () => {
    const originalCreateObjectURL = URL.createObjectURL;
    const originalRevokeObjectURL = URL.revokeObjectURL;
    const createObjectURL = jest.fn(() => 'blob:wappointment-test');
    const revokeObjectURL = jest.fn();
    const clickAnchor = jest.spyOn(HTMLAnchorElement.prototype, 'click').mockImplementation(() => {});
    Object.defineProperty(URL, 'createObjectURL', { configurable: true, value: createObjectURL });
    Object.defineProperty(URL, 'revokeObjectURL', { configurable: true, value: revokeObjectURL });
    try {
        await chooseSlot();
        fireEvent.click(screen.getByRole('button', { name: 'Book appointment', exact: true }));
        await screen.findByRole('heading', { name: 'Appointment confirmed' });
        fireEvent.click(screen.getByRole('button', { name: 'Add to calendar' }));

        const blob = createObjectURL.mock.calls[0][0];
        const content = await new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.readAsText(blob);
        });
        expect(content).toContain('DTSTART:20300102T080000Z');
        expect(content).toContain('DTEND:20300102T083000Z');
        expect(content).toContain('SUMMARY:Consultation');
        expect(content).toContain('LOCATION:Office');
        expect(content).toContain('UID:10@');
        expect(revokeObjectURL).toHaveBeenCalledWith('blob:wappointment-test');
    } finally {
        clickAnchor.mockRestore();
        if (originalCreateObjectURL) {
            Object.defineProperty(URL, 'createObjectURL', { configurable: true, value: originalCreateObjectURL });
        } else {
            delete URL.createObjectURL;
        }
        if (originalRevokeObjectURL) {
            Object.defineProperty(URL, 'revokeObjectURL', { configurable: true, value: originalRevokeObjectURL });
        } else {
            delete URL.revokeObjectURL;
        }
    }
});

test('prefills a signed-in customer and keeps the configured account email read-only', async () => {
    bookingFetch.mockImplementation((path) => {
        if (path === '/booking/catalog') return Promise.resolve({
            ...catalog,
            customer: {
                name: 'Signed-in customer',
                email: 'account@example.invalid',
                phone: '',
                force_email: true,
            },
        });
        if (path === '/booking/availability') return Promise.resolve({
            timezone: 'Europe/Paris',
            slots: [{ start, end: start + 1800 }],
        });
        if (path === '/booking') return Promise.resolve(appointment);
        return Promise.reject(new Error(`Unexpected booking endpoint: ${path}`));
    });

    render(<BookingWidget title="Reserve a meeting" />);
    fireEvent.click(screen.getByRole('button', { name: 'Reserve a meeting' }));
    await screen.findByRole('option', { name: 'Consultation' });
    fireEvent.change(screen.getByLabelText('Service', { exact: true }), { target: { value: '1' } });
    fireEvent.change(screen.getByLabelText('Calendar', { exact: true }), { target: { value: '2' } });
    fireEvent.change(screen.getByLabelText('Appointment location'), { target: { value: '3' } });
    fireEvent.change(screen.getByLabelText('Duration (minutes)'), { target: { value: '30' } });
    fireEvent.click(screen.getByRole('button', { name: 'Show available times' }));
    await screen.findByLabelText('Available time');
    expect(await screen.findByLabelText('Full name')).toHaveValue('Signed-in customer');
    expect(screen.getByLabelText('Email', { exact: true })).toHaveValue('account@example.invalid');
    expect(screen.getByLabelText('Email', { exact: true })).toHaveAttribute('readonly');
});

test('finds and selects the earliest available slot and persists the customer timezone', async () => {
    bookingFetch.mockImplementation((path, data) => {
        if (path === '/booking/catalog') return Promise.resolve(catalog);
        if (path === '/booking/availability') {
            return Promise.resolve(data.date === '2030-01-02'
                ? { timezone: 'Europe/Paris', slots: [] }
                : { timezone: 'Europe/Paris', slots: [{ start, end: start + 1800 }] });
        }
        if (path === '/booking') return Promise.resolve(appointment);
        return Promise.reject(new Error(`Unexpected booking endpoint: ${path}`));
    });

    render(<BookingWidget title="Reserve a meeting" configuration={{
        open: true, service_id: 1, calendar_id: 2, location_id: 3, duration: 30,
    }} />);
    const dateInput = await screen.findByLabelText('Date in organizer timezone');
    fireEvent.change(dateInput, { target: { value: '2030-01-02' } });
    fireEvent.change(screen.getByLabelText('Show appointment times in'), { target: { value: 'Pacific/Honolulu' } });
    fireEvent.click(screen.getByRole('button', { name: /Find first available time/ }));

    expect(await screen.findByText(/The first available time is selected/)).toBeVisible();
    expect(screen.getByLabelText('Date in organizer timezone')).toHaveValue('2030-01-03');
    expect(screen.getByLabelText('Available time')).toHaveValue(String(start));
    expect(bookingFetch).toHaveBeenNthCalledWith(2, '/booking/availability', {
        service_id: '1', calendar_id: '2', location_id: '3', duration: '30', date: '2030-01-02',
    }, expect.any(AbortSignal));
    expect(bookingFetch).toHaveBeenNthCalledWith(3, '/booking/availability', {
        service_id: '1', calendar_id: '2', location_id: '3', duration: '30', date: '2030-01-03',
    }, expect.any(AbortSignal));
    expect(window.localStorage.getItem('wappointment-customer-timezone')).toBe('Pacific/Honolulu');

    fireEvent.change(screen.getByLabelText('Full name'), { target: { value: 'Widget Customer' } });
    fireEvent.change(screen.getByLabelText('Email', { exact: true }), { target: { value: 'customer@example.invalid' } });
    fireEvent.click(screen.getByRole('button', { name: 'Book appointment', exact: true }));
    await screen.findByRole('heading', { name: 'Appointment confirmed' });
    expect(bookingFetch).toHaveBeenLastCalledWith('/booking', expect.objectContaining({
        timezone: 'Pacific/Honolulu',
    }));
});

test('preselected booking options are resolved against catalog relationships and sent to availability', async () => {
    render(<BookingWidget title="Reserve a meeting" configuration={{
        service_id: 1, calendar_id: 2, location_id: 3, duration: 30,
    }} />);

    fireEvent.click(screen.getByRole('button', { name: 'Reserve a meeting' }));
    await screen.findByLabelText('Date in organizer timezone');
    expect(screen.queryByLabelText('Service', { exact: true })).not.toBeInTheDocument();
    expect(screen.queryByLabelText('Calendar', { exact: true })).not.toBeInTheDocument();
    expect(screen.queryByLabelText('Appointment location')).not.toBeInTheDocument();
    expect(screen.queryByLabelText('Duration (minutes)')).not.toBeInTheDocument();
    fireEvent.click(screen.getByRole('button', { name: 'Show available times' }));
    await screen.findByLabelText('Available time');
    expect(bookingFetch).toHaveBeenCalledWith('/booking/availability', expect.objectContaining({
        service_id: '1', calendar_id: '2', location_id: '3', duration: '30',
    }), expect.any(AbortSignal));
});

test('month view navigates calendar dates and clears previously loaded slots', async () => {
    await chooseSlot();
    fireEvent.click(screen.getByRole('button', { name: 'Month', exact: true }));
    expect(screen.getByText('January 2030')).toBeVisible();
    fireEvent.click(screen.getByRole('button', { name: 'Next month' }));
    expect(screen.getByLabelText('Date in organizer timezone')).toHaveValue('2030-02-02');
    expect(screen.queryByLabelText('Available time')).not.toBeInTheDocument();
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

test('invalid appointment keys do not fall through to a fresh booking form', async () => {
    window.history.replaceState({}, '', '/?appointmentkey=invalid');
    render(<BookingWidget title="Reserve a meeting" />);

    expect(await screen.findByRole('alert')).toHaveTextContent('This appointment link is invalid');
    expect(screen.queryByLabelText('Service', { exact: true })).not.toBeInTheDocument();
    expect(bookingFetch).not.toHaveBeenCalled();
});

test('expired appointment keys hide the booking form until the visitor chooses a new booking', async () => {
    window.history.replaceState({}, '', `/?appointmentkey=${appointment.edit_key}`);
    bookingFetch.mockImplementation((path) => {
        if (path === '/booking/catalog') return Promise.resolve(catalog);
        return Promise.reject(new Error('Appointment not found.'));
    });
    render(<BookingWidget title="Reserve a meeting" />);

    expect(await screen.findByText(/The link may have expired/)).toBeVisible();
    expect(screen.queryByLabelText('Service', { exact: true })).not.toBeInTheDocument();
    fireEvent.click(screen.getByRole('button', { name: 'Start a new booking' }));
    expect(await screen.findByRole('option', { name: 'Consultation' })).toBeVisible();
});

test('legacy cancellation links ask for confirmation before changing the appointment', async () => {
    window.history.replaceState({}, '', `/?view=cancel-event&appointmentkey=${appointment.edit_key}`);
    bookingFetch.mockImplementation((path, data) => {
        if (path === '/booking/manage' && data.action === 'view') return Promise.resolve(appointment);
        if (path === '/booking/manage' && data.action === 'cancel') return Promise.resolve({ ...appointment, status: -1 });
        if (path === '/booking/catalog') return Promise.resolve(catalog);
        return Promise.reject(new Error(`Unexpected booking endpoint: ${path}`));
    });

    render(<BookingWidget title="Reserve a meeting" />);

    expect(await screen.findByRole('alertdialog')).toBeVisible();
    expect(bookingFetch).not.toHaveBeenCalledWith('/booking/manage', expect.objectContaining({ action: 'cancel' }));
    fireEvent.click(screen.getByRole('button', { name: 'Confirm cancellation' }));
    expect(await screen.findByRole('heading', { name: 'Appointment cancelled' })).toBeVisible();
    expect(bookingFetch).toHaveBeenCalledWith('/booking/manage', {
        action: 'cancel', edit_key: appointment.edit_key,
    });
});

test('popup dialog closes with Escape and returns keyboard focus to its trigger', async () => {
    render(<BookingWidget title="Reserve a meeting" configuration={{ open: true, popup: true, close_cross: true }} />);
    expect(await screen.findByRole('dialog', { name: 'Reserve a meeting' })).toBeVisible();
    expect(screen.getByRole('button', { name: 'Close' })).toHaveFocus();
    fireEvent.keyDown(document, { key: 'Tab', shiftKey: true });
    expect(screen.getByRole('button', { name: 'Close', exact: true })).not.toHaveFocus();
    fireEvent.keyDown(document, { key: 'Escape' });

    const trigger = await screen.findByRole('button', { name: 'Reserve a meeting' });
    await waitFor(() => expect(trigger).toHaveFocus());
});

test('opens legacy appointment URLs directly in the reschedule flow', async () => {
    window.history.replaceState({}, '', `/?view=reschedule-event&appointmentkey=${appointment.edit_key}`);
    bookingFetch.mockImplementation((path, data) => {
        if (path === '/booking/manage' && data.action === 'view') {
            return Promise.resolve({
                ...appointment, service_id: 1, calendar_id: 2, location_id: 3, duration: 30,
            });
        }
        if (path === '/booking/catalog') return Promise.resolve(catalog);
        if (path === '/booking/availability') return Promise.resolve({ timezone: 'Europe/Paris', slots: [] });
        return Promise.reject(new Error(`Unexpected booking endpoint: ${path}`));
    });

    render(<BookingWidget title="Reserve a meeting" />);

    expect(await screen.findByLabelText('Date in organizer timezone')).toHaveValue('2030-01-02');
    fireEvent.click(screen.getByRole('button', { name: 'Show available times' }));
    await waitFor(() => expect(bookingFetch).toHaveBeenCalledWith('/booking/availability', {
        date: '2030-01-02', edit_key: appointment.edit_key,
    }, expect.any(AbortSignal)));
    expect(screen.queryByLabelText('Email', { exact: true })).not.toBeInTheDocument();
});

test('legacy new-event links open booking and respect the configured first weekday', async () => {
    window.history.replaceState({}, '', '/?view=new-event');
    window.wappointmentWidget.weekStartsOn = 0;
    render(<BookingWidget title="Reserve a meeting" configuration={{ week: true }} />);

    const dateInput = await screen.findByLabelText('Date in organizer timezone');
    fireEvent.change(dateInput, { target: { value: '2030-01-02' } });
    expect(screen.getByRole('button', { name: 'Sunday, December 30, 2029' })).toBeVisible();
    expect(screen.queryByRole('button', { name: 'Reserve a meeting' })).not.toBeInTheDocument();
});

test('renders the localized site privacy link and right-to-left direction', async () => {
    window.wappointmentWidget.locale = 'fr-FR';
    window.wappointmentWidget.rtl = true;
    window.wappointmentWidget.privacyPolicyUrl = 'https://example.invalid/privacy';
    render(<BookingWidget title="Reserve a meeting" configuration={{
        open: true, service_id: 1, calendar_id: 2, location_id: 3, duration: 30,
    }} />);
    fireEvent.change(await screen.findByLabelText('Date in organizer timezone'), { target: { value: '2030-01-02' } });
    fireEvent.click(screen.getByRole('button', { name: 'Show available times' }));

    expect(await screen.findByRole('link', { name: 'privacy policy' })).toHaveAttribute(
        'href', 'https://example.invalid/privacy'
    );
    expect(screen.getByRole('link', { name: 'privacy policy' })).toHaveAttribute('rel', 'noopener noreferrer');
    expect(screen.getByRole('region', { name: 'Reserve a meeting' })).toHaveAttribute('dir', 'rtl');
    expect(screen.getByText('janvier 2030')).toBeVisible();
});

test('shows the saved legacy privacy notice instead of the default policy text', async () => {
    window.wappointmentWidget.privacyPolicyUrl = 'https://example.invalid/default-policy';
    window.wappointmentWidget.legacyPrivacyNotice = 'Please read <a href="https://example.invalid/custom-policy" target="_blank" rel="noopener noreferrer">our policy</a>.';
    await chooseSlot();

    expect(screen.getByText('our policy')).toHaveAttribute('href', 'https://example.invalid/custom-policy');
    expect(screen.queryByRole('link', { name: 'privacy policy' })).not.toBeInTheDocument();
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
    expect(bookingFetch).toHaveBeenLastCalledWith('/booking/availability', {
        edit_key: appointment.edit_key, date: '2030-01-02',
    }, expect.any(AbortSignal));
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
    fireEvent.click(screen.getByRole('button', { name: /Thursday, January 3, 2030/ }));
    await act(async () => finish({ timezone: 'Europe/Paris', slots: [{ start, end: start + 1800 }] }));
    await waitFor(() => expect(screen.getByRole('region')).toHaveAttribute('aria-busy', 'false'));
    expect(screen.queryByLabelText('Available time')).not.toBeInTheDocument();
    expect(screen.getByLabelText('Date in organizer timezone')).toHaveValue('2030-01-03');
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
