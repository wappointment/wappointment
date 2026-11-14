import React from 'react';
import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import Clients from '../../js-react/components/Clients';
import apiFetch from '../../js-react/utils/apiFetch';

jest.mock('../../js-react/utils/apiFetch');
jest.mock('@wordpress/i18n', () => ({
    __: (text) => text,
    sprintf: (text, value) => text.replace('%s', value),
}), { virtual: true });

const client = { id: '18', name: 'Owned client', email: 'owned@example.invalid', options: '{"phone":"555"}' };
const listing = { data: { data: [client], total: 1, page: 1, per_page: 10, total_pages: 1 } };

beforeEach(() => {
    apiFetch.mockReset();
    apiFetch.mockResolvedValue(listing);
    window.wappointmentData = { permissions: {} };
    jest.spyOn(window, 'confirm').mockReturnValue(true);
});

afterEach(() => {
    jest.restoreAllMocks();
    delete window.wappointmentData;
});

test('read-only clients can be viewed but no write controls or Actions column are rendered', async () => {
    render(<Clients />);
    const name = await screen.findByText('Owned client');
    expect(screen.queryByRole('button', { name: 'Create Client' })).not.toBeInTheDocument();
    expect(screen.queryByRole('button', { name: 'Edit' })).not.toBeInTheDocument();
    expect(screen.queryByRole('button', { name: 'Delete' })).not.toBeInTheDocument();
    expect(screen.queryByRole('columnheader', { name: 'Actions' })).not.toBeInTheDocument();
    fireEvent.click(name);
    expect(screen.getByText('Client Details - Owned client')).toBeVisible();
});

test.each([
    ['clientsEdit', 'Edit', 'Delete'],
    ['clientsDelete', 'Delete', 'Edit'],
])('client row controls honor %s independently', async (capability, allowed, denied) => {
    window.wappointmentData.permissions[capability] = true;
    render(<Clients />);
    expect(await screen.findByRole('button', { name: allowed })).toBeVisible();
    expect(screen.queryByRole('button', { name: denied })).not.toBeInTheDocument();
    expect(screen.queryByRole('button', { name: 'Create Client' })).not.toBeInTheDocument();
});

test('manager creation permission shows the create control and form', async () => {
    window.wappointmentData.permissions.clientsCreate = true;
    render(<Clients />);
    await screen.findByText('Owned client');
    fireEvent.click(screen.getByRole('button', { name: 'Create Client' }));
    expect(screen.getByRole('heading', { name: 'Create Client' })).toBeVisible();
});

test('client deletion explains shared visibility and refreshes the scoped list after success', async () => {
    window.wappointmentData.permissions.clientsDelete = true;
    render(<Clients />);
    const remove = await screen.findByRole('button', { name: 'Delete' });
    apiFetch.mockResolvedValueOnce({ data: { success: true, message: 'Client deleted' } })
        .mockResolvedValueOnce({ data: { ...listing.data, data: [], total: 0, total_pages: 0 } });
    fireEvent.click(remove);
    expect(window.confirm).toHaveBeenCalledWith(
        'Remove Owned client from client lists on all calendars? Appointment history will be kept.'
    );
    expect(await screen.findByText('No clients found')).toBeVisible();
    expect(apiFetch).toHaveBeenCalledWith('/clients/18/delete', { method: 'POST' });
    expect(screen.queryByText('Owned client')).not.toBeInTheDocument();
});

test('a revoked deletion grant surfaces the server denial without removing the client', async () => {
    window.wappointmentData.permissions.clientsDelete = true;
    render(<Clients />);
    const remove = await screen.findByRole('button', { name: 'Delete' });
    apiFetch.mockRejectedValueOnce(new Error('You are not allowed to perform this Wappointment operation.'));
    fireEvent.click(remove);
    expect(await screen.findByText(/Failed to delete client: You are not allowed/)).toBeVisible();
    expect(screen.getByText('Owned client')).toBeVisible();
    expect(apiFetch).toHaveBeenCalledTimes(2);
});

test('a denied edit keeps the form open and preserves its data instead of reporting success', async () => {
    window.wappointmentData.permissions.clientsEdit = true;
    render(<Clients />);
    fireEvent.click(await screen.findByRole('button', { name: 'Edit' }));
    expect(screen.getByRole('heading', { name: 'Edit Client' })).toBeVisible();
    fireEvent.change(screen.getByDisplayValue('Owned client'), { target: { value: 'Unsaved name' } });
    apiFetch.mockRejectedValueOnce(new Error('Client not found'));
    fireEvent.click(screen.getByRole('button', { name: 'Save' }));
    expect(await screen.findByText(/Client not found/)).toBeVisible();
    expect(screen.getByRole('heading', { name: 'Edit Client' })).toBeVisible();
    expect(screen.getByDisplayValue('Unsaved name')).toBeVisible();
    expect(screen.queryByText('Client updated successfully')).not.toBeInTheDocument();
    await waitFor(() => expect(screen.getByRole('button', { name: 'Save' })).not.toBeDisabled());
});

test('client options are shown as editable JSON and submitted as an object', async () => {
    window.wappointmentData.permissions.clientsEdit = true;
    render(<Clients />);
    fireEvent.click(await screen.findByRole('button', { name: 'Edit' }));
    fireEvent.change(screen.getByDisplayValue('{"phone":"555"}'), { target: { value: '{"phone":"777","private":"kept"}' } });
    apiFetch.mockResolvedValueOnce({ data: { ...client, options: '{"phone":"777","private":"kept"}' } });
    fireEvent.click(screen.getByRole('button', { name: 'Save' }));
    expect(await screen.findByText('Client updated successfully')).toBeVisible();
    expect(apiFetch).toHaveBeenCalledWith('/clients/18/put', {
        method: 'POST',
        body: JSON.stringify({
            name: 'Owned client', email: 'owned@example.invalid',
            options: { phone: '777', private: 'kept' },
        }),
    });
});

test.each([
    ['broken JSON', 'Options must be valid JSON'],
    ['[]', 'Options must be a JSON object'],
    ['null', 'Options must be a JSON object'],
])('invalid client options %s are surfaced without sending a mutation', async (value, message) => {
    window.wappointmentData.permissions.clientsEdit = true;
    render(<Clients />);
    fireEvent.click(await screen.findByRole('button', { name: 'Edit' }));
    fireEvent.change(screen.getByDisplayValue('{"phone":"555"}'), { target: { value } });
    fireEvent.click(screen.getByRole('button', { name: 'Save' }));
    expect(await screen.findByText(message)).toBeVisible();
    expect(screen.getByDisplayValue(value)).toBeVisible();
    expect(apiFetch).toHaveBeenCalledTimes(1);
});

test('legacy empty option arrays open as an empty map rather than blocking a simple edit', async () => {
    window.wappointmentData.permissions.clientsEdit = true;
    apiFetch.mockResolvedValueOnce({ data: { ...listing.data, data: [{ ...client, options: '[]' }] } });
    render(<Clients />);
    fireEvent.click(await screen.findByRole('button', { name: 'Edit' }));
    expect(screen.getByDisplayValue('{}')).toBeVisible();
    fireEvent.click(screen.getByRole('button', { name: 'Save' }));
    expect(await screen.findByText('Client updated successfully')).toBeVisible();
    expect(apiFetch).toHaveBeenCalledWith('/clients/18/put', {
        method: 'POST',
        body: JSON.stringify({ name: 'Owned client', email: 'owned@example.invalid', options: {} }),
    });
});
