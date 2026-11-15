import React, { useEffect, useState } from 'react';
import apiFetch from '../utils/apiFetch';
import WeeklyAvailabilityEditor, { weekdays as days } from './WeeklyAvailabilityEditor';
import { AvailabilityExceptions } from './MyAvailability';

const defaults = {
    service_name: '', calendar_name: '', location_name: '', location_type: 1,
    duration: 60, booking_days: 60, timezone: 'UTC', phone_required: false,
    booking_page: 0,
    regav: Object.fromEntries(days.map((day) => [day, []])),
};

export default function BookingSetup() {
    const [form, setForm] = useState(defaults);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState('');
    const [saved, setSaved] = useState(false);
    const [calendarId, setCalendarId] = useState(null);
    useEffect(() => {
        let mounted = true;
        apiFetch('/booking-settings').then(({ data }) => {
            if (!mounted) return;
            setCalendarId(Number(data.calendar?.id) || null);
            setForm({
                ...defaults,
                service_name: data.service?.name || '',
                duration: data.service?.options.durations?.[0]?.duration || 60,
                phone_required: data.service?.options.fields?.includes('phone') || false,
                calendar_name: data.calendar?.name || '',
                timezone: data.calendar?.options.timezone || 'UTC',
                booking_days: data.calendar?.options.avb || 60,
                regav: { ...defaults.regav, ...data.calendar?.options.regav },
                location_name: data.location?.name || '',
                location_type: data.location?.type || 1,
                booking_page: data.booking_page || 0,
            });
        }).catch((failure) => {
            if (mounted) setError(failure.message);
        }).finally(() => {
            if (mounted) setLoading(false);
        });
        return () => { mounted = false; };
    }, []);

    const update = (key, value) => {
        setSaved(false);
        setForm((previous) => ({ ...previous, [key]: value }));
    };
    const save = async (event) => {
        event.preventDefault();
        setSaving(true); setError(''); setSaved(false);
        try {
            const { data } = await apiFetch('/booking-settings', { method: 'POST', body: JSON.stringify(form) });
            setCalendarId(Number(data.calendar_id) || null);
            setSaved(true);
        } catch (failure) {
            setError(failure.message);
        } finally {
            setSaving(false);
        }
    };
    if (loading) return <p role="status">Loading booking setup...</p>;
    return <div className="wrap">
        <h1>Booking setup</h1>
        <p>Configure your free service and weekly availability, then place <code>[wap_widget]</code> on your booking page.</p>
        <p>This editor will not overwrite existing multi-service, multi-calendar or multi-location setups.</p>
        {error && <div className="notice notice-error"><p role="alert">{error}</p></div>}
        {saved && <div className="notice notice-success"><p role="status">Booking setup saved.</p></div>}
        <form onSubmit={save}>
            <table className="form-table"><tbody>
                {[
                    ['service_name', 'Service name', 'text'],
                    ['calendar_name', 'Calendar name', 'text'],
                    ['location_name', 'Location or meeting instructions', 'text'],
                    ['timezone', 'Organizer timezone (for example Europe/Paris)', 'text'],
                    ['duration', 'Duration in minutes', 'number'],
                    ['booking_days', 'Days available in advance', 'number'],
                ].map(([key, label, type]) => <tr key={key}>
                    <th><label htmlFor={`booking-${key}`}>{label}</label></th>
                    <td><input id={`booking-${key}`} type={type} required value={form[key]}
                        min={key === 'duration' ? 5 : 1} max={key === 'duration' ? 1440 : 365}
                        onChange={(event) => update(key, type === 'number' ? Number(event.target.value) : event.target.value)} /></td>
                </tr>)}
                <tr><th><label htmlFor="booking-location-type">Meeting type</label></th><td>
                    <select id="booking-location-type" value={form.location_type} onChange={(event) => update('location_type', Number(event.target.value))}>
                        <option value="1">At a location</option><option value="2">Phone</option><option value="4">Other meeting link or instructions</option>
                    </select>
                </td></tr>
                <tr><th>Phone number</th><td><label>
                    <input type="checkbox" checked={form.phone_required} onChange={(event) => update('phone_required', event.target.checked)} /> Required
                </label></td></tr>
                <tr><th><label htmlFor="booking-page-id">Booking page ID</label></th><td>
                    <input id="booking-page-id" type="number" min="0" value={form.booking_page}
                        onChange={(event) => update('booking_page', Number(event.target.value))} />
                    <p className="description">Use a published page containing [wap_widget]. Leave 0 to disable confirmation emails until a booking page is ready.</p>
                </td></tr>
            </tbody></table>
            <h2>Weekly availability</h2>
            <WeeklyAvailabilityEditor value={form.regav} onChange={(value) => update('regav', value)} />
            <button type="submit" className="button button-primary" disabled={saving}>{saving ? 'Saving...' : 'Save booking setup'}</button>
        </form>
        {calendarId !== null && <AvailabilityExceptions
            calendar={{ id: calendarId, name: form.calendar_name, settings: { timezone: form.timezone } }}
            canManage
            adminScope
        />}
    </div>;
}
