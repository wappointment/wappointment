import React from 'react';
import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { http, HttpResponse } from 'msw';
import Appointments from '../components/Appointments';
import { server } from './mocks/server';

const API_BASE = 'http://localhost:8080/wp-json/wappointment/v1';

function appointmentData() {
  return {
    appointments: [{
      id: '17', staff_id: '2', service_id: '3', location_id: '4',
      start_at: '2030-01-02 09:00:00', end_at: '2030-01-02 09:45:00',
      status: '1', client_name: 'Jamie Doe', client_email: 'jamie@example.invalid',
      service_name: 'Consultation', options: { locked: true },
    }],
    events: [{
      id: 'appointment-17', appointment_id: 17, type: 'appointment_confirmed',
      title: 'Jamie Doe - Consultation', start_at: '2030-01-02 09:00:00',
      end_at: '2030-01-02 09:45:00',
    }, {
      id: 'status-4-1893578400', appointment_id: null, type: 'availability_busy',
      title: 'Busy', start_at: '2030-01-02 10:00:00', end_at: '2030-01-02 11:00:00',
    }, {
      id: 'status-5-1893578400', appointment_id: null, type: 'availability_free',
      title: 'Available', start_at: '2030-01-02 08:00:00', end_at: '2030-01-02 09:00:00',
    }],
    calendars: [{ id: '2', name: 'Organizer', timezone: 'UTC' }],
    services: [{ id: '3', name: 'Consultation', options: { durations: [{ duration: 30 }] } }],
    locations: [{ id: '4', name: 'Office', type: '1' }],
    total: 1,
    page: 1,
    per_page: 100,
  };
}

beforeEach(() => {
  window.wappointmentData = {
    apiUrl: API_BASE,
    nonce: 'test',
    permissions: {
      appointmentsConfirm: true, appointmentsCancel: true, appointmentsBook: true,
      appointmentsEdit: true, appointmentsLock: true, appointmentsDelete: true,
      appointmentsForceDelete: true,
    },
  };
});

test('shows typed weekly events and exposes authorized lock and edit actions', async () => {
  const appointment = appointmentData();
  server.use(http.get(`${API_BASE}/appointments`, () => HttpResponse.json({ success: true, data: appointment })));
  render(<Appointments />);

  expect(await screen.findByText(/Jamie Doe/)).toBeInTheDocument();
  fireEvent.change(screen.getByLabelText(/Week starting/), { target: { value: '2030-01-02' } });
  await userEvent.click(screen.getByRole('button', { name: 'Show week' }));
  expect(await screen.findByRole('heading', { name: 'Week calendar' })).toBeInTheDocument();
  expect(screen.getByText('Confirmed appointment')).toBeInTheDocument();
  expect(screen.getByText('Busy time')).toBeInTheDocument();
  expect(screen.getByText('Available time')).toBeInTheDocument();
  expect(screen.getByRole('button', { name: 'Unlock' })).toBeInTheDocument();

  await userEvent.click(screen.getByRole('button', { name: 'Reschedule' }));
  expect(screen.getByText('Times are entered in UTC.')).toBeInTheDocument();
  expect(document.querySelector('table input[type="date"]')).toBeInTheDocument();
  expect(screen.getByLabelText('Time')).toBeInTheDocument();
});

test('books an existing client through the authorized admin flow', async () => {
  const appointment = appointmentData();
  const bookedRequests = [];
  server.use(
    http.get(`${API_BASE}/appointments`, () => HttpResponse.json({ success: true, data: appointment })),
    http.get(`${API_BASE}/clients`, () => HttpResponse.json({
      success: true,
      data: { data: [{ id: '8', name: 'Jamie Doe', email: 'jamie@example.invalid' }], total: 1, page: 1, per_page: 100, total_pages: 1 },
    })),
    http.post(`${API_BASE}/booking/availability`, () => HttpResponse.json({
      success: true,
      data: { slots: [{ start: 1893574800, end: 1893576600, reserved_until: 1893577500 }] },
    })),
    http.post(`${API_BASE}/appointments/book`, async ({ request }) => {
      bookedRequests.push(await request.json());
      return HttpResponse.json({ success: true, data: { id: 18, status: 1, start: 1893574800, end: 1893576600, edit_key: 'a'.repeat(32), notification_queued: false } }, { status: 201 });
    })
  );
  render(<Appointments />);

  await screen.findByText(/Jamie Doe/);
  await userEvent.click(screen.getByRole('button', { name: 'Search clients' }));
  await screen.findByRole('option', { name: /Jamie Doe/ });
  await userEvent.selectOptions(screen.getByLabelText('Client'), '8');
  await userEvent.selectOptions(document.getElementById('admin-booking-service'), '3');
  await userEvent.selectOptions(document.getElementById('admin-booking-calendar'), '2');
  await userEvent.selectOptions(document.getElementById('admin-booking-location'), '4');
  await userEvent.selectOptions(document.getElementById('admin-booking-duration'), '30');
  await userEvent.click(screen.getByRole('button', { name: 'Find available times' }));
  await screen.findByLabelText('Available time');
  await userEvent.click(screen.getByRole('button', { name: 'Book appointment' }));

  await waitFor(() => expect(bookedRequests).toHaveLength(1));
  expect(bookedRequests[0]).toMatchObject({
    client_id: 8, service_id: 3, calendar_id: 2, location_id: 4, duration: 30,
  });
  expect(await screen.findByRole('status')).toHaveTextContent('Appointment booked.');
});
