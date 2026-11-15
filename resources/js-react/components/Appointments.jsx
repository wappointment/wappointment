import React, { useEffect, useState } from 'react';
import apiFetch from '../utils/apiFetch';

const appointmentsPerPage = 100;

function localDateValue(date) {
    const pad = (value) => String(value).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

function currentWeekStart() {
    const date = new Date();
    date.setHours(0, 0, 0, 0);
    date.setDate(date.getDate() - ((date.getDay() + 6) % 7));
    return localDateValue(date);
}

function dateTimeInZone(timestamp, timezone) {
    const parts = new Intl.DateTimeFormat('en-CA', {
        timeZone: timezone, year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit', hourCycle: 'h23',
    }).formatToParts(new Date(timestamp * 1000));
    const values = Object.fromEntries(parts.map(({ type, value }) => [type, value]));
    return {
        date: `${values.year}-${values.month}-${values.day}`,
        time: `${values.hour}:${values.minute}`,
    };
}

function timestampInZone(date, time, timezone) {
    const [year, month, day] = date.split('-').map(Number);
    const [hour, minute] = time.split(':').map(Number);
    const desired = Date.UTC(year, month - 1, day, hour, minute);
    let timestamp = Math.floor(desired / 1000);
    for (let attempt = 0; attempt < 4; attempt += 1) {
        const parts = new Intl.DateTimeFormat('en-CA', {
            timeZone: timezone, year: 'numeric', month: '2-digit', day: '2-digit',
            hour: '2-digit', minute: '2-digit', hourCycle: 'h23',
        }).formatToParts(new Date(timestamp * 1000));
        const values = Object.fromEntries(parts.map(({ type, value }) => [type, value]));
        const localAsUtc = Date.UTC(
            Number(values.year), Number(values.month) - 1, Number(values.day),
            Number(values.hour), Number(values.minute)
        );
        const difference = desired - localAsUtc;
        if (difference === 0) return timestamp;
        timestamp += Math.round(difference / 1000);
    }
    throw new Error('That local time does not exist in the organizer timezone because of a daylight-saving transition.');
}

export default function Appointments() {
    const permissions = window.wappointmentData?.permissions || {};
    const [appointments, setAppointments] = useState([]);
    const [events, setEvents] = useState([]);
    const [calendars, setCalendars] = useState([]);
    const [services, setServices] = useState([]);
    const [locations, setLocations] = useState([]);
    const [clients, setClients] = useState([]);
    const [total, setTotal] = useState(0);
    const [page, setPage] = useState(1);
    const [weekStart, setWeekStart] = useState(currentWeekStart);
    const [rangeEnabled, setRangeEnabled] = useState(false);
    const [calendarFilter, setCalendarFilter] = useState('');
    const [serviceFilter, setServiceFilter] = useState('');
    const [clientSearch, setClientSearch] = useState('');
    const [clientId, setClientId] = useState('');
    const [booking, setBooking] = useState({
        service_id: '', calendar_id: '', location_id: '', duration: '', date: currentWeekStart(),
    });
    const [availableSlots, setAvailableSlots] = useState([]);
    const [selectedStart, setSelectedStart] = useState('');
    const [notice, setNotice] = useState('');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [saving, setSaving] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [editDate, setEditDate] = useState('');
    const [editTime, setEditTime] = useState('');
    const [editTimezone, setEditTimezone] = useState('UTC');
    const [draggedAppointmentId, setDraggedAppointmentId] = useState(null);
    const load = async (targetPage = 1, selectedWeek = '', selectedCalendar = calendarFilter, selectedService = serviceFilter) => {
        const query = new URLSearchParams({ page: String(targetPage), per_page: String(appointmentsPerPage) });
        if (selectedWeek) {
            const start = new Date(`${selectedWeek}T00:00:00`);
            const end = new Date(start);
            end.setDate(end.getDate() + 7);
            query.set('from', String(Math.floor(start.getTime() / 1000)));
            query.set('to', String(Math.floor(end.getTime() / 1000)));
        }
        if (selectedCalendar) query.set('calendar_id', selectedCalendar);
        if (selectedService) query.set('service_id', selectedService);
        const result = await apiFetch(`/appointments?${query.toString()}`);
        setAppointments(result.data.appointments);
        setEvents(result.data.events || []);
        setCalendars(result.data.calendars || []);
        setServices(result.data.services || []);
        setLocations(result.data.locations || []);
        setTotal(result.data.total);
        setPage(result.data.page);
    };
    useEffect(() => {
        load(1, '').catch((failure) => setError(failure.message)).finally(() => setLoading(false));
    }, []);
    const runQuery = async (targetPage, selectedWeek, selectedCalendar = calendarFilter, selectedService = serviceFilter) => {
        setSaving(true); setError('');
        try {
            await load(targetPage, selectedWeek, selectedCalendar, selectedService);
        } catch (failure) {
            setError(failure.message);
        } finally {
            setSaving(false);
        }
    };
    const changeStatus = async (id, status) => {
        setSaving(true); setError('');
        try {
            await apiFetch('/appointments', { method: 'POST', body: JSON.stringify({ id: Number(id), status }) });
            await load(page, rangeEnabled ? weekStart : '');
        } catch (failure) {
            setError(failure.message);
        } finally {
            setSaving(false);
        }
    };
    const mutateAppointment = async (id, path, body = {}) => {
        setSaving(true); setError('');
        try {
            await apiFetch(`/appointments/${Number(id)}/${path}`, {
                method: 'POST',
                body: JSON.stringify(body),
            });
            setEditingId(null);
            await load(page, rangeEnabled ? weekStart : '');
        } catch (failure) {
            setError(failure.message);
        } finally {
            setSaving(false);
        }
    };
    const beginEdit = (appointment) => {
        const start = new Date(`${appointment.start_at.replace(' ', 'T')}Z`);
        const calendar = calendars.find((item) => Number(item.id) === Number(appointment.staff_id));
        const timezone = calendar?.timezone || 'UTC';
        const local = dateTimeInZone(Math.floor(start.getTime() / 1000), timezone);
        setEditingId(Number(appointment.id));
        setEditTimezone(timezone);
        setEditDate(local.date);
        setEditTime(local.time);
    };
    const saveEdit = async (event, id) => {
        event.preventDefault();
        let start;
        try {
            start = timestampInZone(editDate, editTime, editTimezone);
        } catch (failure) {
            setError(failure.message);
            return;
        }
        await mutateAppointment(id, 'reschedule', {
            date: editDate,
            start,
        });
    };
    const deleteAppointment = async (id, force = false) => {
        const message = force
            ? 'Force-delete this appointment? This permanently removes it and unlinks any order item.'
            : 'Delete this appointment? This permanently removes it and unlinks any order item.';
        if (!window.confirm(message)) return;
        await mutateAppointment(id, force ? 'force-delete' : 'delete');
    };
    const showWeek = (event) => {
        event.preventDefault();
        setRangeEnabled(true);
        runQuery(1, weekStart);
    };
    const moveWeek = (amount) => {
        const start = new Date(`${weekStart}T00:00:00`);
        start.setDate(start.getDate() + amount * 7);
        const nextWeek = localDateValue(start);
        setWeekStart(nextWeek);
        setRangeEnabled(true);
        runQuery(1, nextWeek);
    };
    const showRecent = () => {
        setRangeEnabled(false);
        runQuery(1, '');
    };
    const changePage = (nextPage) => runQuery(nextPage, rangeEnabled ? weekStart : '');
    const date = (value) => new Date(`${value.replace(' ', 'T')}Z`).toLocaleString();
    const pageCount = Math.max(1, Math.ceil(total / appointmentsPerPage));
    const selectedService = services.find((service) => Number(service.id) === Number(booking.service_id));
    const durations = (selectedService?.options?.durations || []).map((item) => Number(item.duration)).filter((value) => value > 0);
    const loadClients = async (search = '') => {
        const query = new URLSearchParams({ page_num: '1', per_page: '100' });
        if (search) query.set('search', search);
        const result = await apiFetch(`/clients?${query.toString()}`);
        setClients(result.data.data);
    };
    const searchClient = async (event) => {
        event.preventDefault();
        setSaving(true); setError('');
        try {
            await loadClients(clientSearch);
        } catch (failure) {
            setError(failure.message);
        } finally {
            setSaving(false);
        }
    };
    const updateBooking = (key, value) => {
        setAvailableSlots([]);
        setSelectedStart('');
        setBooking((previous) => ({
            ...previous,
            [key]: value,
            ...(key === 'service_id' ? { duration: '' } : {}),
        }));
    };
    const findBookingSlots = async (event) => {
        event.preventDefault();
        setSaving(true); setError(''); setNotice('');
        try {
            const result = await apiFetch('/booking/availability', {
                method: 'POST',
                body: JSON.stringify({
                    service_id: Number(booking.service_id),
                    calendar_id: Number(booking.calendar_id),
                    location_id: Number(booking.location_id),
                    duration: Number(booking.duration),
                    date: booking.date,
                }),
            });
            setAvailableSlots(result.data.slots);
            setSelectedStart(String(result.data.slots[0]?.start || ''));
        } catch (failure) {
            setError(failure.message);
        } finally {
            setSaving(false);
        }
    };
    const createAdminBooking = async (event) => {
        event.preventDefault();
        setSaving(true); setError(''); setNotice('');
        try {
            await apiFetch('/appointments/book', {
                method: 'POST',
                body: JSON.stringify({
                    service_id: Number(booking.service_id),
                    calendar_id: Number(booking.calendar_id),
                    location_id: Number(booking.location_id),
                    duration: Number(booking.duration),
                    date: booking.date,
                    start: Number(selectedStart),
                    client_id: Number(clientId),
                }),
            });
            setNotice('Appointment booked.');
            setRangeEnabled(true);
            setWeekStart(booking.date);
            await load(1, booking.date);
            setAvailableSlots([]);
            setSelectedStart('');
        } catch (failure) {
            setError(failure.message);
        } finally {
            setSaving(false);
        }
    };
    const eventLabel = (type) => ({
        appointment_pending: 'Pending appointment',
        appointment_confirmed: 'Confirmed appointment',
        appointment_cancelled: 'Cancelled appointment',
        availability_busy: 'Busy time',
        availability_free: 'Available time',
        external: 'External calendar event',
    })[type] || type;
    const weekDates = rangeEnabled
        ? Array.from({ length: 7 }, (_, index) => {
            const day = new Date(`${weekStart}T00:00:00`);
            day.setDate(day.getDate() + index);
            return localDateValue(day);
        })
        : [];
    const displayedEventDay = (event) => {
        const eventStart = new Date(`${event.start_at.replace(' ', 'T')}Z`);
        const rangeStart = new Date(`${weekStart}T00:00:00`);
        return localDateValue(eventStart < rangeStart ? rangeStart : eventStart);
    };
    return <div className="wrap">
        <h1>Appointments</h1>
        <p>{rangeEnabled ? `Appointments for the week of ${weekStart}.` : 'The latest appointments.'} Times use your browser timezone.</p>
        <form onSubmit={showWeek}>
            <label htmlFor="appointment-week">Week starting </label>
            <input id="appointment-week" type="date" value={weekStart} onChange={(event) => setWeekStart(event.target.value)} />
            <button className="button" type="submit" disabled={saving}>Show week</button>
            <label htmlFor="appointment-calendar-filter">Calendar </label>
            <select id="appointment-calendar-filter" value={calendarFilter} onChange={(event) => setCalendarFilter(event.target.value)}>
                <option value="">All calendars</option>
                {calendars.map((calendar) => <option key={calendar.id} value={calendar.id}>{calendar.name}</option>)}
            </select>
            <label htmlFor="appointment-service-filter">Service </label>
            <select id="appointment-service-filter" value={serviceFilter} onChange={(event) => setServiceFilter(event.target.value)}>
                <option value="">All services</option>
                {services.map((service) => <option key={service.id} value={service.id}>{service.name}</option>)}
            </select>
            {rangeEnabled && <>
                <button className="button" type="button" disabled={saving} onClick={() => moveWeek(-1)}>Previous week</button>
                <button className="button" type="button" disabled={saving} onClick={() => moveWeek(1)}>Next week</button>
                <button className="button" type="button" disabled={saving} onClick={showRecent}>Latest appointments</button>
            </>}
        </form>
        {error && <div className="notice notice-error"><p role="alert">{error}</p></div>}
        {notice && <div className="notice notice-success"><p role="status">{notice}</p></div>}
        {rangeEnabled && !loading && <section aria-label="Calendar events">
            <h2>Week calendar</h2>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: '12px' }}>
                {weekDates.map((day) => {
                    const dayEvents = events.filter((event) => displayedEventDay(event) === day);
                    return <section key={day} aria-label={day}
                        onDragOver={(event) => event.preventDefault()}
                        onDrop={(event) => {
                            event.preventDefault();
                            const id = Number(event.dataTransfer.getData('text/plain') || draggedAppointmentId);
                            const appointment = appointments.find((item) => Number(item.id) === id);
                            if (appointment) {
                                beginEdit(appointment);
                                setEditDate(day);
                            }
                            setDraggedAppointmentId(null);
                        }}>
                        <h3>{new Date(`${day}T00:00:00`).toLocaleDateString(undefined, { weekday: 'long', month: 'short', day: 'numeric' })}</h3>
                        {dayEvents.length === 0 ? <p>No events</p> : <ul>
                            {dayEvents.map((event) => <li key={event.id} data-event-type={event.type}
                                draggable={Boolean(permissions.appointmentsEdit && event.appointment_id && event.type !== 'appointment_cancelled')}
                                onDragStart={(dragEvent) => {
                                    if (!event.appointment_id) return;
                                    setDraggedAppointmentId(Number(event.appointment_id));
                                    dragEvent.dataTransfer.setData('text/plain', String(event.appointment_id));
                                }}>
                                <strong>{eventLabel(event.type)}</strong><br />
                                {new Date(`${event.start_at.replace(' ', 'T')}Z`).toLocaleTimeString()}
                                {event.title ? ` · ${event.title}` : ''}
                            </li>)}
                        </ul>}
                    </section>;
                })}
            </div>
        </section>}
        {permissions.appointmentsBook && <section aria-label="Book an appointment on behalf">
            <h2>Book an appointment on behalf</h2>
            <form onSubmit={searchClient}>
                <label htmlFor="appointment-client-search">Find a client </label>
                <input id="appointment-client-search" type="search" value={clientSearch} onChange={(event) => setClientSearch(event.target.value)} />
                <button className="button" disabled={saving}>Search clients</button>
                <label htmlFor="appointment-client">Client </label>
                <select id="appointment-client" value={clientId} onChange={(event) => setClientId(event.target.value)}>
                    <option value="">Choose a client</option>
                    {clients.map((client) => <option key={client.id} value={client.id}>{client.name} ({client.email})</option>)}
                </select>
            </form>
            <form onSubmit={findBookingSlots}>
                <label htmlFor="admin-booking-service">Service </label>
                <select id="admin-booking-service" value={booking.service_id} onChange={(event) => updateBooking('service_id', event.target.value)} required>
                    <option value="">Choose a service</option>
                    {services.map((service) => <option key={service.id} value={service.id}>{service.name}</option>)}
                </select>
                <label htmlFor="admin-booking-calendar">Calendar </label>
                <select id="admin-booking-calendar" value={booking.calendar_id} onChange={(event) => updateBooking('calendar_id', event.target.value)} required>
                    <option value="">Choose a calendar</option>
                    {calendars.map((calendar) => <option key={calendar.id} value={calendar.id}>{calendar.name}</option>)}
                </select>
                <label htmlFor="admin-booking-location">Location </label>
                <select id="admin-booking-location" value={booking.location_id} onChange={(event) => updateBooking('location_id', event.target.value)} required>
                    <option value="">Choose a location</option>
                    {locations.map((location) => <option key={location.id} value={location.id}>{location.name}</option>)}
                </select>
                <label htmlFor="admin-booking-duration">Duration </label>
                <select id="admin-booking-duration" value={booking.duration} onChange={(event) => updateBooking('duration', event.target.value)} required>
                    <option value="">Choose a duration</option>
                    {durations.map((duration) => <option key={duration} value={duration}>{duration} minutes</option>)}
                </select>
                <label htmlFor="admin-booking-date">Date </label>
                <input id="admin-booking-date" type="date" value={booking.date} onChange={(event) => updateBooking('date', event.target.value)} required />
                <button className="button" disabled={saving}>Find available times</button>
            </form>
            {availableSlots.length > 0 && <form onSubmit={createAdminBooking}>
                <label htmlFor="admin-booking-slot">Available time </label>
                <select id="admin-booking-slot" value={selectedStart} onChange={(event) => setSelectedStart(event.target.value)} required>
                    {availableSlots.map((slot) => <option key={slot.start} value={slot.start}>{new Date(slot.start * 1000).toLocaleTimeString()}</option>)}
                </select>
                <button className="button button-primary" disabled={saving || !clientId}>Book appointment</button>
            </form>}
        </section>}
        {loading ? <p role="status">Loading appointments...</p> : <table className="wp-list-table widefat fixed striped">
            <thead><tr>{['Client', 'Service', 'Start', 'Status', 'Actions'].map((label) => <th key={label} scope="col">{label}</th>)}</tr></thead>
            <tbody>{appointments.map((appointment) => <tr key={appointment.id}>
                <td>{appointment.client_name}<br />{appointment.client_email}</td>
                <td>{appointment.service_name}</td><td>{date(appointment.start_at)}</td>
                <td>{Number(appointment.status) < 0 ? 'Cancelled' : Number(appointment.status) === 1 ? 'Confirmed' : 'Pending'}</td>
                <td>
                    {permissions.appointmentsConfirm && Number(appointment.status) === 0 && <button className="button" disabled={saving} onClick={() => changeStatus(appointment.id, 1)}>Confirm</button>}
                    {permissions.appointmentsCancel && Number(appointment.status) >= 0 && <button className="button" disabled={saving} onClick={() => changeStatus(appointment.id, -1)}>Cancel</button>}
                    {permissions.appointmentsLock && <button className="button" disabled={saving} onClick={() => mutateAppointment(appointment.id, 'lock', { locked: !appointment.options?.locked })}>
                        {appointment.options?.locked ? 'Unlock' : 'Lock'}
                    </button>}
                    {permissions.appointmentsEdit && Number(appointment.status) >= 0 && <button className="button" disabled={saving} onClick={() => beginEdit(appointment)}>Reschedule</button>}
                    {permissions.appointmentsDelete && <button className="button" disabled={saving} onClick={() => deleteAppointment(appointment.id)}>Delete</button>}
                    {permissions.appointmentsForceDelete && <button className="button" disabled={saving} onClick={() => deleteAppointment(appointment.id, true)}>Force delete</button>}
                    {editingId === Number(appointment.id) && <form onSubmit={(event) => saveEdit(event, appointment.id)}>
                        <p>Times are entered in {editTimezone}.</p>
                        <label>Date <input type="date" value={editDate} onChange={(event) => setEditDate(event.target.value)} required /></label>
                        <label>Time <input type="time" value={editTime} onChange={(event) => setEditTime(event.target.value)} required /></label>
                        <button className="button button-primary" disabled={saving}>Save time</button>
                        <button className="button" type="button" disabled={saving} onClick={() => setEditingId(null)}>Close</button>
                    </form>}
                </td>
            </tr>)}</tbody>
        </table>}
        {!loading && <div className="tablenav">
            <p>{total === 0 ? 'No appointments found.' : `Page ${page} of ${pageCount} (${total} appointments)`}</p>
            <button className="button" disabled={saving || page <= 1} onClick={() => changePage(page - 1)}>Previous</button>
            <button className="button" disabled={saving || page >= pageCount} onClick={() => changePage(page + 1)}>Next</button>
        </div>}
    </div>;
}
