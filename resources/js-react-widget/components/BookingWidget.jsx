import React, { useEffect, useRef, useState } from 'react';
import { __ } from '@wordpress/i18n';
import bookingFetch from '../api';

const initialKey = () => {
    const match = window.location.hash.match(/^#wappointment=([a-f0-9]{32})$/);
    return match ? match[1] : '';
};

export default function BookingWidget({ title = __('Book now', 'wappointment') }) {
    const [editKey, setEditKey] = useState(initialKey);
    const [open, setOpen] = useState(Boolean(initialKey()));
    const [catalog, setCatalog] = useState(null);
    const [selection, setSelection] = useState({ service_id: '', calendar_id: '', location_id: '', duration: '', date: '', start: '' });
    const [contact, setContact] = useState({ name: '', email: '', phone: '' });
    const [availability, setAvailability] = useState(null);
    const [appointment, setAppointment] = useState(null);
    const [rescheduling, setRescheduling] = useState(false);
    const [busy, setBusy] = useState(false);
    const [error, setError] = useState('');
    const submitting = useRef(false);
    const requestId = useRef(0);
    const id = React.useId();
    const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const format = (timestamp) => new Date(timestamp * 1000).toLocaleString(undefined, { timeZone: timezone, timeZoneName: 'short' });

    useEffect(() => {
        if (!open || catalog) return;
        const controller = new AbortController();
        bookingFetch('/booking/catalog', undefined, controller.signal).then(setCatalog).catch((failure) => {
            if (failure.name !== 'AbortError') setError(failure.message);
        });
        return () => controller.abort();
    }, [open, catalog]);

    useEffect(() => {
        if (!editKey || appointment?.edit_key === editKey) return;
        const controller = new AbortController();
        bookingFetch('/booking/manage', { edit_key: editKey, action: 'view' }, controller.signal)
            .then(setAppointment).catch((failure) => {
                if (failure.name !== 'AbortError') setError(failure.message);
            });
        return () => controller.abort();
    }, [editKey]);

    const change = (field, value) => {
        if (field === 'start') {
            setSelection((previous) => ({ ...previous, start: value }));
            return;
        }
        requestId.current += 1;
        setSelection((previous) => ({
            ...previous,
            ...(field === 'service_id' ? { calendar_id: '', location_id: '', duration: '' } : {}),
            [field]: value,
            start: '',
        }));
        setAvailability(null);
        setError('');
    };

    const loadSlots = async (event) => {
        event.preventDefault();
        const currentRequest = ++requestId.current;
        setBusy(true);
        setError('');
        setAvailability(null);
        setSelection((previous) => ({ ...previous, start: '' }));
        try {
            const { service_id, calendar_id, location_id, duration, date } = selection;
            const result = await bookingFetch('/booking/availability', rescheduling
                ? { date, edit_key: editKey } : { service_id, calendar_id, location_id, duration, date });
            if (currentRequest === requestId.current) setAvailability(result);
        } catch (failure) {
            if (currentRequest === requestId.current) setError(failure.message);
        } finally {
            setBusy(false);
        }
    };

    const submit = async (event, action) => {
        event.preventDefault();
        if (submitting.current) return;
        submitting.current = true;
        setBusy(true);
        setError('');
        try {
            const result = action
                ? await bookingFetch('/booking/manage', {
                    edit_key: editKey, action,
                    ...(action === 'reschedule' ? { date: selection.date, start: selection.start } : {}),
                })
                : await bookingFetch('/booking', { ...selection, ...contact });
            setAppointment(result);
            setEditKey(result.edit_key);
            setRescheduling(false);
            setAvailability(null);
        } catch (failure) {
            setError(failure.message);
        } finally {
            submitting.current = false;
            setBusy(false);
        }
    };

    if (!open) return <div className="wap-widget"><button className="wap-book-btn" onClick={() => setOpen(true)}>{title}</button></div>;

    const service = catalog?.services.find((row) => String(row.id) === selection.service_id);
    const calendars = catalog?.calendars.filter((row) => catalog.calendar_service.some((link) =>
        String(link.calendar_id) === String(row.id) && String(link.service_id) === selection.service_id)) || [];
    const locations = catalog?.locations.filter((row) => catalog.service_location.some((link) =>
        String(link.location_id) === String(row.id) && String(link.service_id) === selection.service_id)) || [];
    const location = locations.find((row) => String(row.id) === selection.location_id);
    const phoneRequired = service?.fields.includes('phone') || location?.fields.includes('phone') || location?.type === 2;
    const select = (field, label, rows) => (
        <div className="wap-form-group">
            <label htmlFor={`${id}-${field}`}>{label}</label>
            <select id={`${id}-${field}`} required value={selection[field]} onChange={(event) => change(field, event.target.value)}>
                <option value="">{__('Choose an option', 'wappointment')}</option>
                {rows.map((row) => <option key={row.id} value={row.id}>{row.name}</option>)}
            </select>
        </div>
    );

    return (
        <section className="wap-widget wap-widget-open" aria-label={title} aria-busy={busy}>
            <div className="wap-widget-header">
                <h3>{title}</h3>
                <button type="button" className="wap-close-btn" aria-label={__('Close', 'wappointment')} onClick={() => setOpen(false)}>×</button>
            </div>
            <div className="wap-widget-body">
                {error && <p role="alert">{error}</p>}
                {appointment && !rescheduling ? (
                    <div className="wap-confirmation" role="status">
                        <h4>{appointment.status < 0 ? __('Appointment cancelled', 'wappointment')
                            : appointment.status === 1 ? __('Appointment confirmed', 'wappointment') : __('Awaiting approval', 'wappointment')}</h4>
                        <p>{format(appointment.start)} - {format(appointment.end)}</p>
                        {appointment.notification_sent === false && <p>{__('Your appointment was saved, but no confirmation email was sent. Save your management link below.', 'wappointment')}</p>}
                        <p><a href={`#wappointment=${appointment.edit_key}`}>{__('Appointment management link', 'wappointment')}</a></p>
                        {appointment.status >= 0 && <>
                            <button type="button" disabled={busy} onClick={(event) => submit(event, 'cancel')}>{__('Cancel appointment', 'wappointment')}</button>
                            <button type="button" disabled={busy} onClick={() => {
                                setError(''); setAvailability(null); setRescheduling(true);
                            }}>{__('Reschedule appointment', 'wappointment')}</button>
                        </>}
                        <button type="button" onClick={() => {
                            setAppointment(null); setEditKey(''); setAvailability(null); setSelection((previous) => ({ ...previous, start: '' }));
                            if (window.location.hash.startsWith('#wappointment=')) {
                                window.history.replaceState(null, '', window.location.pathname + window.location.search);
                            }
                        }}>{__('Book another appointment', 'wappointment')}</button>
                    </div>
                ) : !catalog ? <p role="status">{__('Loading booking options...', 'wappointment')}</p> : (
                    <>
                        <form onSubmit={loadSlots}>
                            {catalog.services.length === 0 && <p role="status">{__('No services are configured for booking. Please contact the organizer.', 'wappointment')}</p>}
                            {!rescheduling && <>
                                {select('service_id', __('Service', 'wappointment'), catalog.services)}
                                {select('calendar_id', __('Calendar', 'wappointment'), calendars)}
                                {select('location_id', __('Appointment location', 'wappointment'), locations)}
                                {select('duration', __('Duration (minutes)', 'wappointment'), (service?.durations || []).map((duration) => ({ id: duration, name: duration })))}
                            </>}
                            <div className="wap-form-group">
                                <label htmlFor={`${id}-date`}>{__('Date in organizer timezone', 'wappointment')}</label>
                                <input id={`${id}-date`} type="date" required value={selection.date} onChange={(event) => change('date', event.target.value)} />
                            </div>
                            <button className="wap-btn wap-btn-primary" disabled={busy}>{__('Show available times', 'wappointment')}</button>
                        </form>
                        {availability && <form onSubmit={(event) => submit(event, rescheduling ? 'reschedule' : null)}>
                            <p>{__('Times are displayed in your timezone:', 'wappointment')} {timezone}</p>
                            <p>{__('Organizer timezone:', 'wappointment')} {availability.timezone}</p>
                            {availability.slots.length === 0 ? <p role="status">{__('No times are available. Choose another date.', 'wappointment')}</p> : <>
                                {select('start', __('Available time', 'wappointment'), availability.slots.map((slot) => ({ id: slot.start, name: format(slot.start) })))}
                                {!rescheduling && ['name', 'email', 'phone'].map((field) => (
                                    <div key={field} className="wap-form-group">
                                        <label htmlFor={`${id}-${field}`}>{({
                                            name: __('Full name', 'wappointment'), email: __('Email', 'wappointment'), phone: __('Phone', 'wappointment'),
                                        })[field]}</label>
                                        <input id={`${id}-${field}`} type={field === 'name' ? 'text' : field === 'phone' ? 'tel' : 'email'}
                                            required={field !== 'phone' || phoneRequired} autoComplete={field === 'phone' ? 'tel' : field}
                                            maxLength={field === 'name' ? 100 : field === 'email' ? 191 : 40}
                                            value={contact[field]} onChange={(event) => setContact({ ...contact, [field]: event.target.value })} />
                                    </div>
                                ))}
                                <button className="wap-btn wap-btn-primary" disabled={busy || !selection.start}>
                                    {rescheduling ? __('Confirm new time', 'wappointment') : __('Book appointment', 'wappointment')}
                                </button>
                            </>}
                        </form>}
                        {rescheduling && <button type="button" onClick={() => setRescheduling(false)}>{__('Back to appointment', 'wappointment')}</button>}
                    </>
                )}
            </div>
        </section>
    );
}
