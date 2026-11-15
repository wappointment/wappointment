import React, { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import apiFetch from '../utils/apiFetch';
import WeeklyAvailabilityEditor from './WeeklyAvailabilityEditor';

function localDateTime(timestamp) {
    const date = new Date(timestamp * 1000);
    const pad = (value) => String(value).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function AvailabilityExceptions({ calendar, canManage }) {
    const [statuses, setStatuses] = useState([]);
    const [type, setType] = useState(1);
    const [start, setStart] = useState(() => localDateTime(Math.floor(Date.now() / 1000) + 3600));
    const [end, setEnd] = useState(() => localDateTime(Math.floor(Date.now() / 1000) + 7200));
    const [loading, setLoading] = useState(canManage);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState('');
    const endpoint = `/me/calendars/${calendar.id}/statuses`;
    const load = async () => {
        setLoading(true); setError('');
        try {
            const { data } = await apiFetch(endpoint);
            setStatuses(data.statuses);
        } catch (failure) {
            setError(failure.message);
        } finally {
            setLoading(false);
        }
    };
    useEffect(() => {
        if (canManage) load();
    }, [calendar.id, canManage]);
    const save = async (event) => {
        event.preventDefault();
        const startAt = Math.floor(new Date(start).getTime() / 1000);
        const endAt = Math.floor(new Date(end).getTime() / 1000);
        setSaving(true); setError('');
        try {
            await apiFetch(endpoint, {
                method: 'POST',
                body: JSON.stringify({ type: Number(type), start: startAt, end: endAt }),
            });
            await load();
        } catch (failure) {
            setError(failure.message);
        } finally {
            setSaving(false);
        }
    };
    const remove = async (statusId) => {
        setSaving(true); setError('');
        try {
            await apiFetch(`${endpoint}/${statusId}/delete`, { method: 'POST' });
            await load();
        } catch (failure) {
            setError(failure.message);
        } finally {
            setSaving(false);
        }
    };
    return <section aria-label={`${calendar.name} availability exceptions`}>
        <h3>{__('Busy and free times', 'wappointment')}</h3>
        <p>{__('Times are entered and shown in your browser timezone. Busy times block bookings; free times add bookable hours within your weekly schedule.', 'wappointment')}</p>
        {error && <p role="alert">{error}</p>}
        {!canManage && <p>{__('You do not have permission to change this calendar’s busy and free times.', 'wappointment')}</p>}
        {canManage && <form onSubmit={save}>
            <fieldset disabled={saving}>
                <p><label htmlFor={`status-type-${calendar.id}`}>{__('Time type', 'wappointment')}</label><br />
                    <select id={`status-type-${calendar.id}`} value={type} onChange={(event) => setType(event.target.value)}>
                        <option value="1">{__('Busy — block bookings', 'wappointment')}</option>
                        <option value="0">{__('Free — add bookable time', 'wappointment')}</option>
                    </select></p>
                <p><label htmlFor={`status-start-${calendar.id}`}>{__('Start', 'wappointment')}</label><br />
                    <input id={`status-start-${calendar.id}`} type="datetime-local" required value={start}
                        onChange={(event) => setStart(event.target.value)} /></p>
                <p><label htmlFor={`status-end-${calendar.id}`}>{__('End', 'wappointment')}</label><br />
                    <input id={`status-end-${calendar.id}`} type="datetime-local" required value={end}
                        onChange={(event) => setEnd(event.target.value)} /></p>
                <button className="button button-primary" type="submit">{saving ? __('Saving...', 'wappointment') : __('Add time', 'wappointment')}</button>
            </fieldset>
        </form>}
        {loading ? <p role="status">{__('Loading busy and free times...', 'wappointment')}</p> : statuses.length > 0
            ? <ul>{statuses.map((status) => <li key={status.id}>
                <span>{Number(status.type) === 0 ? __('Free', 'wappointment') : __('Busy', 'wappointment')}: {new Date(status.start * 1000).toLocaleString()} – {new Date(status.end * 1000).toLocaleString()}</span>
                {canManage && <button className="button-link-delete" type="button" disabled={saving} onClick={() => remove(status.id)}>{__('Delete', 'wappointment')}</button>}
            </li>)}</ul>
            : <p>{__('No one-off busy or free times.', 'wappointment')}</p>}
    </section>;
}

function CalendarForm({ calendar, canSave }) {
    const [settings, setSettings] = useState(calendar.settings);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState('');
    const [saved, setSaved] = useState(false);
    const update = (key, value) => {
        setSaved(false);
        setSettings((previous) => ({ ...previous, [key]: value }));
    };
    const save = async (event) => {
        event.preventDefault();
        setSaving(true); setError(''); setSaved(false);
        try {
            const { data } = await apiFetch(`/me/calendars/${calendar.id}/weekly-settings`, {
                method: 'POST', body: JSON.stringify({ settings }),
            });
            setSettings(data.calendar.settings);
            setSaved(true);
        } catch (failure) {
            setError(failure.message);
        } finally {
            setSaving(false);
        }
    };
    return <section aria-label={calendar.name}>
        <h2>{calendar.name}</h2>
        {calendar.problem ? <p role="alert">{calendar.problem}</p> : <form onSubmit={save}>
            {error && <p role="alert">{error}</p>}
            {saved && <p role="status">{__('Availability saved.', 'wappointment')}</p>}
            {!canSave && <p>{__('You can view this calendar, but do not have permission to change weekly availability.', 'wappointment')}</p>}
            <fieldset disabled={!canSave || saving}>
                <p><label htmlFor={`own-timezone-${calendar.id}`}>{__('Organizer timezone', 'wappointment')}</label><br />
                    <input id={`own-timezone-${calendar.id}`} required value={settings.timezone}
                        onChange={(event) => update('timezone', event.target.value)} /></p>
                <p><label htmlFor={`own-days-${calendar.id}`}>{__('Days available in advance', 'wappointment')}</label><br />
                    <input id={`own-days-${calendar.id}`} type="number" min="1" max="365" required value={settings.booking_days}
                        onChange={(event) => update('booking_days', Number(event.target.value))} /></p>
                <WeeklyAvailabilityEditor value={settings.regav} onChange={(value) => update('regav', value)} disabled={!canSave || saving} />
                {canSave && <button className="button button-primary" type="submit">
                    {saving ? __('Saving...', 'wappointment') : __('Save availability', 'wappointment')}
                </button>}
            </fieldset>
        </form>}
        <AvailabilityExceptions calendar={calendar} canManage={window.wappointmentData?.permissions?.selfStatuses === true} />
    </section>;
}

export default function MyAvailability() {
    const [calendars, setCalendars] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const canSave = window.wappointmentData?.permissions?.selfWeekly === true;
    const load = () => {
        setLoading(true); setError('');
        apiFetch('/me/calendars').then(({ data }) => setCalendars(data.calendars))
            .catch((failure) => setError(failure.message)).finally(() => setLoading(false));
    };
    useEffect(load, []);
    return <div className="wrap">
        <h1>{__('My availability', 'wappointment')}</h1>
        <p>{__('Only your calendars are shown. Changes affect future slot searches, not existing appointments.', 'wappointment')}</p>
        {loading ? <p role="status">{__('Loading calendars...', 'wappointment')}</p> : error ? <>
            <p role="alert">{error}</p><button className="button" onClick={load}>{__('Retry', 'wappointment')}</button>
        </> : calendars.length === 0 ? <p>{__('No calendar is assigned to your WordPress account. Contact a Wappointment manager.', 'wappointment')}</p>
            : calendars.map((calendar) => <CalendarForm key={calendar.id} calendar={calendar} canSave={canSave} />)}
    </div>;
}
