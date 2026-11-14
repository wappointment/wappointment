import React, { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import apiFetch from '../utils/apiFetch';
import WeeklyAvailabilityEditor from './WeeklyAvailabilityEditor';

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
