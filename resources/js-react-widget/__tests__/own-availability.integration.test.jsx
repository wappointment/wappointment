import React from 'react';
import { fireEvent, render, screen, within } from '@testing-library/react';
import MyAvailability from '../../js-react/components/MyAvailability';
import apiFetch from '../../js-react/utils/apiFetch';

jest.mock('../../js-react/utils/apiFetch');
jest.mock('@wordpress/i18n', () => ({ __: (text) => text }), { virtual: true });

const settings = {
    timezone: 'UTC', booking_days: 60,
    regav: Object.fromEntries(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].map((day) => [day, []])),
};
const calendar = { id: 17, name: 'My calendar', settings, problem: null };

beforeEach(() => {
    apiFetch.mockReset();
    apiFetch.mockResolvedValue({ data: { calendars: [calendar] } });
    window.wappointmentData = { permissions: { selfWeekly: true } };
});

afterEach(() => { delete window.wappointmentData; });

test('own availability submits only the selected calendars typed weekly settings including midnight', async () => {
    render(<MyAvailability />);
    fireEvent.change(await screen.findByLabelText('Organizer timezone'), { target: { value: 'Europe/Paris' } });
    fireEvent.change(screen.getByLabelText('Days available in advance'), { target: { value: '45' } });
    const monday = screen.getByRole('group', { name: 'monday' });
    fireEvent.click(within(monday).getByRole('button', { name: 'Add range' }));
    fireEvent.change(within(monday).getByLabelText('until'), { target: { value: '00:00' } });
    const changed = { ...settings, timezone: 'Europe/Paris', booking_days: 45, regav: { ...settings.regav, monday: [[540, 1440]] } };
    apiFetch.mockResolvedValueOnce({ data: { calendar: { ...calendar, settings: changed } } });
    fireEvent.click(screen.getByRole('button', { name: 'Save availability' }));
    expect(await screen.findByText('Availability saved.')).toBeVisible();
    expect(apiFetch).toHaveBeenLastCalledWith('/me/calendars/17/weekly-settings', {
        method: 'POST', body: JSON.stringify({ settings: changed }),
    });
});

test('self view without weekly permission shows disabled fields and no save control', async () => {
    window.wappointmentData.permissions.selfWeekly = false;
    render(<MyAvailability />);
    expect(await screen.findByLabelText('Organizer timezone')).toBeDisabled();
    expect(screen.getByLabelText('Days available in advance')).toBeDisabled();
    expect(screen.queryByRole('button', { name: 'Save availability' })).not.toBeInTheDocument();
    expect(screen.getByText('You can view this calendar, but do not have permission to change weekly availability.')).toBeVisible();
});

test('a calendar needing migration has an explicit problem and no editable form', async () => {
    apiFetch.mockResolvedValueOnce({ data: { calendars: [{ ...calendar, settings: null, problem: 'Stored conditions must be migrated.' }] } });
    render(<MyAvailability />);
    expect(await screen.findByRole('alert')).toHaveTextContent('Stored conditions must be migrated.');
    expect(screen.queryByLabelText('Organizer timezone')).not.toBeInTheDocument();
    expect(screen.queryByRole('button', { name: 'Save availability' })).not.toBeInTheDocument();
});

test('a denied save keeps entered settings without reporting success', async () => {
    render(<MyAvailability />);
    fireEvent.change(await screen.findByLabelText('Organizer timezone'), { target: { value: 'Europe/Paris' } });
    apiFetch.mockRejectedValueOnce(new Error('You are not allowed to perform this Wappointment operation.'));
    fireEvent.click(screen.getByRole('button', { name: 'Save availability' }));
    expect(await screen.findByRole('alert')).toHaveTextContent('You are not allowed');
    expect(screen.getByLabelText('Organizer timezone')).toHaveValue('Europe/Paris');
    expect(screen.queryByText('Availability saved.')).not.toBeInTheDocument();
});

test('no assigned calendar is an actionable empty state instead of a fabricated default form', async () => {
    apiFetch.mockResolvedValueOnce({ data: { calendars: [] } });
    render(<MyAvailability />);
    expect(await screen.findByText(/No calendar is assigned/)).toBeVisible();
    expect(screen.queryByRole('button', { name: 'Save availability' })).not.toBeInTheDocument();
});

test('failed loading shows an error and retry rather than an empty success state', async () => {
    apiFetch.mockRejectedValueOnce(new Error('Could not inspect calendar settings.'));
    render(<MyAvailability />);
    expect(await screen.findByRole('alert')).toHaveTextContent('Could not inspect calendar settings.');
    expect(screen.queryByText(/No calendar is assigned/)).not.toBeInTheDocument();
    fireEvent.click(screen.getByRole('button', { name: 'Retry' }));
    expect(await screen.findByLabelText('Organizer timezone')).toBeVisible();
});

test('multiple owned calendars have independent forms and save only the chosen calendar', async () => {
    apiFetch.mockResolvedValueOnce({ data: { calendars: [calendar, { ...calendar, id: 18, name: 'Second calendar' }] } });
    render(<MyAvailability />);
    const second = await screen.findByRole('region', { name: 'Second calendar' });
    fireEvent.change(within(second).getByLabelText('Days available in advance'), { target: { value: '10' } });
    apiFetch.mockResolvedValueOnce({ data: { calendar: { ...calendar, id: 18, settings: { ...settings, booking_days: 10 } } } });
    fireEvent.click(within(second).getByRole('button', { name: 'Save availability' }));
    await screen.findByText('Availability saved.');
    expect(apiFetch).toHaveBeenLastCalledWith('/me/calendars/18/weekly-settings', {
        method: 'POST', body: JSON.stringify({ settings: { ...settings, booking_days: 10 } }),
    });
    expect(within(screen.getByRole('region', { name: 'My calendar' })).getByLabelText('Days available in advance')).toHaveValue(60);
});
