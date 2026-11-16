import React, { useEffect, useRef, useState } from 'react';
import { __ } from '@wordpress/i18n';
import bookingFetch from '../api';

const initialKey = () => {
    const match = window.location.hash.match(/^#wappointment=([a-f0-9]{32})$/);
    if (match) return match[1];
    const key = new URLSearchParams(window.location.search).get('appointmentkey');
    return key && /^[a-f0-9]{32}$/.test(key) ? key : '';
};

const hasInvalidManagementLink = () => {
    const hash = window.location.hash;
    const query = new URLSearchParams(window.location.search);
    const queryKey = query.get('appointmentkey');
    const hasValidKey = /^#wappointment=[a-f0-9]{32}$/.test(hash)
        || Boolean(queryKey && /^[a-f0-9]{32}$/.test(queryKey));
    const hasManagementKey = hash.startsWith('#wappointment=') || query.has('appointmentkey');
    return hasManagementKey && !hasValidKey;
};

const initialLegacyView = () => new URLSearchParams(window.location.search).get('view') || '';

function dateInTimezone(timezone, instant = new Date()) {
    const parts = new Intl.DateTimeFormat('en-CA', {
        timeZone: timezone, year: 'numeric', month: '2-digit', day: '2-digit',
    }).formatToParts(instant);
    const values = Object.fromEntries(parts.map(({ type, value }) => [type, value]));
    return `${values.year}-${values.month}-${values.day}`;
}

function addDays(date, amount) {
    const value = new Date(`${date}T00:00:00Z`);
    value.setUTCDate(value.getUTCDate() + amount);
    return value.toISOString().slice(0, 10);
}

function weekStart(date, firstDay = 1) {
    const value = new Date(`${date}T00:00:00Z`);
    value.setUTCDate(value.getUTCDate() - ((value.getUTCDay() - firstDay + 7) % 7));
    return value.toISOString().slice(0, 10);
}

function addMonths(date, amount) {
    const value = new Date(`${date}T00:00:00Z`);
    const day = value.getUTCDate();
    value.setUTCDate(1);
    value.setUTCMonth(value.getUTCMonth() + amount);
    const lastDay = new Date(Date.UTC(value.getUTCFullYear(), value.getUTCMonth() + 1, 0)).getUTCDate();
    value.setUTCDate(Math.min(day, lastDay));
    return value.toISOString().slice(0, 10);
}

function monthStart(date) {
    return `${date.slice(0, 7)}-01`;
}

function uiLocale() {
    return window.wappointmentWidget?.locale || undefined;
}

function dateLabel(date, options) {
    return new Intl.DateTimeFormat(uiLocale(), { ...options, timeZone: 'UTC' }).format(new Date(`${date}T00:00:00Z`));
}

function validTimezone(timezone) {
    try {
        new Intl.DateTimeFormat(undefined, { timeZone: timezone });
        return true;
    } catch {
        return false;
    }
}

function initialTimezone() {
    const browserTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
    try {
        const timezone = savedTimezone();
        return timezone || browserTimezone;
    } catch {
        return browserTimezone;
    }
}

function savedTimezone() {
    try {
        const timezone = window.localStorage.getItem('wappointment-customer-timezone');
        return timezone && validTimezone(timezone) ? timezone : '';
    } catch {
        return '';
    }
}

function timezoneOptions(currentTimezone) {
    const supported = typeof Intl.supportedValuesOf === 'function'
        ? Intl.supportedValuesOf('timeZone')
        : ['UTC', 'America/Los_Angeles', 'America/New_York', 'Asia/Kolkata', 'Asia/Tokyo',
            'Australia/Sydney', 'Europe/London', 'Europe/Paris'];
    if (!supported.includes(currentTimezone)) supported.push(currentTimezone);
    return supported.map((timezone) => ({ id: timezone, name: timezone }));
}

function downloadCalendarEvent(appointment, fallbackTitle) {
    const escapeText = (value) => String(value || '').replace(/\\/g, '\\\\')
        .replace(/[\n\r]+/g, '\\n').replace(/([,;])/g, '\\$1');
    const toIcsDate = (timestamp) => new Date(timestamp * 1000).toISOString()
        .replace(/[-:]/g, '').replace(/\.\d{3}Z$/, 'Z');
    const lines = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//Wappointment//Booking//EN',
        'CALSCALE:GREGORIAN',
        'METHOD:PUBLISH',
        'BEGIN:VEVENT',
        `UID:${appointment.id}@${window.location.hostname || 'wappointment'}`,
        `DTSTAMP:${toIcsDate(Math.floor(Date.now() / 1000))}`,
        `DTSTART:${toIcsDate(appointment.start)}`,
        `DTEND:${toIcsDate(appointment.end)}`,
        `SUMMARY:${escapeText(appointment.service_name || fallbackTitle)}`,
    ];
    if (appointment.location_name) lines.push(`LOCATION:${escapeText(appointment.location_name)}`);
    lines.push('END:VEVENT', 'END:VCALENDAR');

    const url = window.URL.createObjectURL(new Blob([`${lines.join('\r\n')}\r\n`], {
        type: 'text/calendar;charset=utf-8',
    }));
    const link = document.createElement('a');
    link.href = url;
    link.download = 'wappointment.ics';
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
}

export default function BookingWidget({
    title = __('Book now', 'wappointment'),
    buttonTitle = '',
    configuration = {},
    instanceId = '',
}) {
    const [editKey, setEditKey] = useState(initialKey);
    const [open, setOpen] = useState(Boolean(initialKey() || hasInvalidManagementLink()
        || initialLegacyView() === 'new-event' || configuration.open));
    const [invalidManagementLink] = useState(hasInvalidManagementLink);
    const [catalog, setCatalog] = useState(null);
    const [selection, setSelection] = useState({ service_id: '', calendar_id: '', location_id: '', duration: '', date: '', start: '' });
    const [contact, setContact] = useState({ name: '', email: '', phone: '' });
    const [availability, setAvailability] = useState(null);
    const [appointment, setAppointment] = useState(null);
    const [managementLoading, setManagementLoading] = useState(Boolean(initialKey()));
    const [managementFailed, setManagementFailed] = useState(false);
    const [rescheduling, setRescheduling] = useState(false);
    const [confirmCancellation, setConfirmCancellation] = useState(false);
    const [legacyView] = useState(initialLegacyView);
    const [calendarView, setCalendarView] = useState(configuration.week ? 'week' : 'month');
    const [searchMessage, setSearchMessage] = useState('');
    const [timezone, setTimezone] = useState(initialTimezone);
    const [busy, setBusy] = useState(false);
    const [error, setError] = useState('');
    const submitting = useRef(false);
    const requestId = useRef(0);
    const availabilityController = useRef(null);
    const calendarDownloadKey = useRef('');
    const triggerRef = useRef(null);
    const dialogRef = useRef(null);
    const id = `${instanceId}-${React.useId()}`;
    const format = (timestamp) => new Date(timestamp * 1000).toLocaleString(uiLocale(), { timeZone: timezone, timeZoneName: 'short' });

    useEffect(() => {
        try {
            window.localStorage.setItem('wappointment-customer-timezone', timezone);
        } catch {
            setError(__('Your timezone preference could not be saved in this browser.', 'wappointment'));
        }
    }, [timezone]);

    useEffect(() => {
        if (invalidManagementLink) {
            setError(__('This appointment link is invalid. Check the link or contact the organizer.', 'wappointment'));
        }
    }, [invalidManagementLink]);

    useEffect(() => {
        if (!open || catalog || invalidManagementLink) return;
        const controller = new AbortController();
        bookingFetch('/booking/catalog', undefined, controller.signal).then(setCatalog).catch((failure) => {
            if (failure.name !== 'AbortError') setError(failure.message);
        });
        return () => controller.abort();
    }, [open, catalog, invalidManagementLink]);

    useEffect(() => () => availabilityController.current?.abort(), []);

    useEffect(() => {
        if (!catalog?.customer) return;
        if (!savedTimezone() && catalog.customer.timezone && validTimezone(catalog.customer.timezone)) {
            setTimezone(catalog.customer.timezone);
        }
        setContact((previous) => ({
            ...previous,
            name: previous.name || catalog.customer.name,
            email: catalog.customer.force_email ? catalog.customer.email : (previous.email || catalog.customer.email),
            phone: previous.phone || catalog.customer.phone,
        }));
    }, [catalog]);

    useEffect(() => {
        if (!catalog) return;
        const serviceId = String(configuration.service_id || '');
        const calendarId = String(configuration.calendar_id || '');
        const locationId = String(configuration.location_id || '');
        const duration = String(configuration.duration || '');
        const service = catalog.services.find((row) => String(row.id) === serviceId);
        const calendar = catalog.calendars.find((row) => String(row.id) === calendarId);
        const location = catalog.locations.find((row) => String(row.id) === locationId);
        const calendarIsRelated = calendar && catalog.calendar_service.some((link) =>
            String(link.calendar_id) === calendarId && String(link.service_id) === serviceId);
        const locationIsRelated = location && catalog.service_location.some((link) =>
            String(link.location_id) === locationId && String(link.service_id) === serviceId);
        if ((serviceId && !service) || (calendarId && !calendarIsRelated) ||
            (locationId && !locationIsRelated) || (duration && !service?.durations.includes(Number(duration)))) {
            setError(__('A preselected booking option is no longer available. Choose an available option or contact the organizer.', 'wappointment'));
            return;
        }
        setSelection((previous) => ({
            ...previous,
            service_id: serviceId || previous.service_id,
            calendar_id: calendarId || previous.calendar_id,
            location_id: locationId || previous.location_id,
            duration: duration || previous.duration,
            date: previous.date || (calendar ? dateInTimezone(calendar.timezone) : ''),
        }));
    }, [catalog]);

    useEffect(() => {
        if (!open) {
            triggerRef.current?.focus();
            return undefined;
        }
        if (!configuration.popup) return undefined;
        dialogRef.current?.querySelector('button, input, select, a')?.focus();
        const handleDialogKeys = (event) => {
            if (event.key === 'Escape') setOpen(false);
            if (event.key !== 'Tab') return;
            const focusable = dialogRef.current?.querySelectorAll(
                'button:not([disabled]), input:not([disabled]), select:not([disabled]), a[href]'
            );
            if (!focusable?.length) {
                event.preventDefault();
                dialogRef.current?.focus();
                return;
            }
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        };
        document.addEventListener('keydown', handleDialogKeys);
        return () => document.removeEventListener('keydown', handleDialogKeys);
    }, [open, configuration.popup]);

    useEffect(() => {
        if (!editKey || appointment?.edit_key === editKey) return;
        const controller = new AbortController();
        setManagementLoading(true);
        setManagementFailed(false);
        bookingFetch('/booking/manage', { edit_key: editKey, action: 'view' }, controller.signal)
            .then((result) => {
                setAppointment(result);
                setManagementLoading(false);
            }).catch((failure) => {
                if (failure.name !== 'AbortError') {
                    setError(failure.message);
                    setManagementFailed(true);
                    setManagementLoading(false);
                }
            });
        return () => controller.abort();
    }, [editKey]);

    useEffect(() => {
        if (!appointment?.service_id || !catalog) return;
        const calendar = catalog.calendars.find((row) => String(row.id) === String(appointment.calendar_id));
        const service = catalog.services.find((row) => String(row.id) === String(appointment.service_id));
        const location = catalog.locations.find((row) => String(row.id) === String(appointment.location_id));
        setAppointment((previous) => {
            if (!previous || ((!service || previous.service_name) && (!location || previous.location_name))) {
                return previous;
            }
            return {
                ...previous,
                service_name: previous.service_name || service?.name || '',
                location_name: previous.location_name || location?.name || '',
            };
        });
        setSelection((previous) => ({
            ...previous,
            service_id: String(appointment.service_id),
            calendar_id: String(appointment.calendar_id),
            location_id: String(appointment.location_id),
            duration: String(appointment.duration),
            date: previous.date || dateInTimezone(calendar?.timezone || timezone, new Date(appointment.start * 1000)),
        }));
    }, [appointment, catalog, timezone]);

    useEffect(() => {
        if (appointment && appointment.status >= 0 && legacyView === 'reschedule-event') {
            setRescheduling(true);
        }
    }, [appointment, legacyView]);

    useEffect(() => {
        if (!appointment || !catalog || legacyView !== 'add-to-calendar' || appointment.status !== 1
            || calendarDownloadKey.current === appointment.edit_key) return;
        try {
            downloadCalendarEvent(appointment, appointment.service_name || title);
            calendarDownloadKey.current = appointment.edit_key;
        } catch (failure) {
            setError(failure instanceof Error ? failure.message : __('Calendar download failed.', 'wappointment'));
        }
    }, [appointment, catalog, legacyView, title]);

    useEffect(() => {
        if (appointment && legacyView === 'cancel-event' && appointment.status >= 0) {
            setConfirmCancellation(true);
        }
    }, [appointment, legacyView]);

    const change = (field, value) => {
        if (field === 'start') {
            setSelection((previous) => ({ ...previous, start: value }));
            return;
        }
        requestId.current += 1;
        availabilityController.current?.abort();
        setBusy(false);
        setSearchMessage('');
        const selectedCalendar = field === 'calendar_id'
            ? catalog?.calendars.find((row) => String(row.id) === value)
            : null;
        setSelection((previous) => ({
            ...previous,
            ...(field === 'service_id' ? { calendar_id: '', location_id: '', duration: '' } : {}),
            [field]: value,
            ...(selectedCalendar && !previous.date ? { date: dateInTimezone(selectedCalendar.timezone) } : {}),
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
        setSearchMessage('');
        setSelection((previous) => ({ ...previous, start: '' }));
        const controller = new AbortController();
        availabilityController.current = controller;
        try {
            const { service_id, calendar_id, location_id, duration, date } = selection;
            const result = await bookingFetch('/booking/availability', rescheduling
                ? { date, edit_key: editKey } : { service_id, calendar_id, location_id, duration, date }, controller.signal);
            if (currentRequest === requestId.current) setAvailability(result);
        } catch (failure) {
            if (currentRequest === requestId.current && failure.name !== 'AbortError') setError(failure.message);
        } finally {
            if (currentRequest === requestId.current) setBusy(false);
        }
    };

    const findFirstAvailability = async () => {
        if (!selection.service_id || !selection.calendar_id || !selection.location_id || !selection.duration) {
            setError(__('Choose a service, calendar, location and duration before searching.', 'wappointment'));
            return;
        }
        const currentRequest = ++requestId.current;
        const firstDate = selection.date || dateInTimezone(selectedCalendar?.timezone || timezone);
        setBusy(true);
        setError('');
        setAvailability(null);
        setSearchMessage(__('Searching the next 30 days for an available time...', 'wappointment'));
        const controller = new AbortController();
        availabilityController.current = controller;
        try {
            for (let offset = 0; offset < 30; offset += 1) {
                const date = addDays(firstDate, offset);
                const result = await bookingFetch('/booking/availability', rescheduling
                    ? { date, edit_key: editKey }
                    : {
                        service_id: selection.service_id,
                        calendar_id: selection.calendar_id,
                        location_id: selection.location_id,
                        duration: selection.duration,
                        date,
                    }, controller.signal);
                if (currentRequest !== requestId.current) return;
                if (result.slots.length > 0) {
                    setSelection((previous) => ({ ...previous, date, start: String(result.slots[0].start) }));
                    setAvailability(result);
                    setSearchMessage(__('The first available time is selected. Review the time and confirm to continue.', 'wappointment'));
                    return;
                }
            }
            if (currentRequest === requestId.current) {
                setSearchMessage(__('No available times were found in the next 30 days. Choose another date and try again.', 'wappointment'));
            }
        } catch (failure) {
            if (currentRequest === requestId.current && failure.name !== 'AbortError') {
                setSearchMessage('');
                setError(failure.message);
            }
        } finally {
            if (currentRequest === requestId.current) setBusy(false);
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
                : await bookingFetch('/booking', { ...selection, ...contact, timezone });
            setAppointment(action ? result : {
                ...result,
                service_name: catalog.services.find((row) => String(row.id) === selection.service_id)?.name || '',
                location_name: catalog.locations.find((row) => String(row.id) === selection.location_id)?.name || '',
            });
            setEditKey(result.edit_key);
            setRescheduling(false);
            setConfirmCancellation(false);
            setAvailability(null);
        } catch (failure) {
            setError(failure.message);
        } finally {
            submitting.current = false;
            setBusy(false);
        }
    };

    const rootClass = [
        'wap-widget',
        configuration.center ? 'wap-widget-centered' : '',
        configuration.brc_floats ? 'wap-widget-floating' : '',
    ].filter(Boolean).join(' ');
    if (!open) return <div className={rootClass}>
        <button ref={triggerRef} className="wap-book-btn" onClick={() => setOpen(true)}>{buttonTitle || title}</button>
    </div>;

    const service = catalog?.services.find((row) => String(row.id) === selection.service_id);
    const selectedCalendar = catalog?.calendars.find((row) => String(row.id) === selection.calendar_id);
    const calendars = catalog?.calendars.filter((row) => catalog.calendar_service.some((link) =>
        String(link.calendar_id) === String(row.id) && String(link.service_id) === selection.service_id)) || [];
    const locations = catalog?.locations.filter((row) => catalog.service_location.some((link) =>
        String(link.location_id) === String(row.id) && String(link.service_id) === selection.service_id)) || [];
    const fixedService = Boolean(configuration.service_id && catalog?.services.some((row) =>
        String(row.id) === String(configuration.service_id)));
    const fixedCalendar = Boolean(configuration.calendar_id && calendars.some((row) =>
        String(row.id) === String(configuration.calendar_id)));
    const fixedLocation = Boolean(configuration.location_id && locations.some((row) =>
        String(row.id) === String(configuration.location_id)));
    const fixedDuration = Boolean(configuration.duration && service?.durations.includes(Number(configuration.duration)));
    const location = locations.find((row) => String(row.id) === selection.location_id);
    const phoneRequired = service?.fields.includes('phone') || location?.fields.includes('phone') || location?.type === 2;
    const calendarTimezone = selectedCalendar?.timezone || availability?.timezone || timezone;
    const selectedDate = selection.date || dateInTimezone(calendarTimezone);
    const preferredWeekStart = Number(window.wappointmentWidget?.weekStartsOn);
    const firstDayOfWeek = Number.isInteger(preferredWeekStart) && preferredWeekStart >= 0 && preferredWeekStart <= 6
        ? preferredWeekStart : 1;
    const firstDay = weekStart(selectedDate, firstDayOfWeek);
    const weekDates = Array.from({ length: 7 }, (_, index) => addDays(firstDay, index));
    const currentMonth = monthStart(selectedDate);
    const monthGridStart = weekStart(currentMonth, firstDayOfWeek);
    const monthDates = Array.from({ length: 42 }, (_, index) => addDays(monthGridStart, index));
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
        <section className={`${rootClass} wap-widget-open${configuration.large ? ' wap-widget-large' : ''}${configuration.popup ? ' wap-widget-popup' : ''}`}
            aria-labelledby={`${id}-title`} aria-busy={busy} role={configuration.popup ? 'dialog' : undefined}
            aria-modal={configuration.popup ? 'true' : undefined}
            dir={window.wappointmentWidget?.rtl ? 'rtl' : 'ltr'} tabIndex={configuration.popup ? -1 : undefined} ref={dialogRef}>
            <div className="wap-widget-header">
                <h3 id={`${id}-title`}>{title}</h3>
                {configuration.close_cross && <button type="button" className="wap-close-btn" aria-label={__('Close', 'wappointment')} onClick={() => setOpen(false)}>×</button>}
            </div>
            <div className="wap-widget-body">
                {error && <p role="alert">{error}</p>}
                {invalidManagementLink ? null : managementLoading ? (
                    <p role="status">{__('Loading appointment details...', 'wappointment')}</p>
                ) : managementFailed ? (
                    <div>
                        <p role="status">{__('This appointment could not be loaded. The link may have expired or the appointment may no longer be available.', 'wappointment')}</p>
                        <button type="button" onClick={() => {
                            setError('');
                            setManagementFailed(false);
                            setEditKey('');
                            const url = new URL(window.location.href);
                            url.hash = '';
                            url.searchParams.delete('appointmentkey');
                            url.searchParams.delete('view');
                            window.history.replaceState(null, '', `${url.pathname}${url.search}`);
                        }}>{__('Start a new booking', 'wappointment')}</button>
                    </div>
                ) : appointment && !rescheduling ? (
                    <div className="wap-confirmation" role="status">
                        <h4>{appointment.status < 0 ? __('Appointment cancelled', 'wappointment')
                            : appointment.status === 1 ? __('Appointment confirmed', 'wappointment') : __('Awaiting approval', 'wappointment')}</h4>
                        <p>{format(appointment.start)} - {format(appointment.end)}</p>
                        {appointment.service_name && <p>{__('Service:', 'wappointment')} {appointment.service_name}</p>}
                        {appointment.location_name && <p>{__('Location:', 'wappointment')} {appointment.location_name}</p>}
                        {appointment.notification_queued === false && <p>{__('Your appointment was saved, but no confirmation email will be sent. Save your management link below.', 'wappointment')}</p>}
                        <p><a href={`#wappointment=${appointment.edit_key}`}>{__('Appointment management link', 'wappointment')}</a></p>
                        {confirmCancellation && <div role="alertdialog" aria-labelledby={`${id}-cancel-title`}>
                            <h4 id={`${id}-cancel-title`}>{__('Cancel this appointment?', 'wappointment')}</h4>
                            <button type="button" disabled={busy} onClick={(event) => submit(event, 'cancel')}>{__('Confirm cancellation', 'wappointment')}</button>
                            <button type="button" onClick={() => setConfirmCancellation(false)}>{__('Keep appointment', 'wappointment')}</button>
                        </div>}
                        {appointment.status === 1 && <button type="button" onClick={() => {
                                try {
                                    downloadCalendarEvent(appointment, title);
                                    setError('');
                                } catch (failure) {
                                    setError(failure instanceof Error ? failure.message : __('Calendar download failed.', 'wappointment'));
                                }
                            }}>{__('Add to calendar', 'wappointment')}</button>}
                        {appointment.status >= 0 && <>
                            {!confirmCancellation && <button type="button" disabled={busy} onClick={(event) => submit(event, 'cancel')}>{__('Cancel appointment', 'wappointment')}</button>}
                            {!confirmCancellation && <button type="button" disabled={busy} onClick={() => {
                                setError(''); setAvailability(null); setRescheduling(true);
                            }}>{__('Reschedule appointment', 'wappointment')}</button>}
                        </>}
                        <button type="button" onClick={() => {
                            setAppointment(null); setEditKey(''); setAvailability(null); setSelection((previous) => ({ ...previous, start: '' }));
                            setManagementFailed(false);
                            const url = new URL(window.location.href);
                            url.hash = '';
                            url.searchParams.delete('appointmentkey');
                            url.searchParams.delete('view');
                            window.history.replaceState(null, '', `${url.pathname}${url.search}${url.hash}`);
                        }}>{__('Book another appointment', 'wappointment')}</button>
                    </div>
                ) : !catalog ? <p role="status">{__('Loading booking options...', 'wappointment')}</p> : (
                    <>
                        <form onSubmit={loadSlots}>
                            {catalog.services.length === 0 && <p role="status">{__('No services are configured for booking. Please contact the organizer.', 'wappointment')}</p>}
                            {!rescheduling && <>
                                {!fixedService && select('service_id', __('Service', 'wappointment'), catalog.services)}
                                {!fixedCalendar && select('calendar_id', __('Calendar', 'wappointment'), calendars)}
                                {!fixedLocation && select('location_id', __('Appointment location', 'wappointment'), locations)}
                                {!fixedDuration && select('duration', __('Duration (minutes)', 'wappointment'), (service?.durations || []).map((duration) => ({ id: duration, name: duration })))}
                            </>}
                            <div className="wap-form-group">
                                <label htmlFor={`${id}-date`}>{__('Date in organizer timezone', 'wappointment')}</label>
                                <input id={`${id}-date`} type="date" required value={selection.date} onChange={(event) => change('date', event.target.value)} />
                            </div>
                            <div className="wap-form-group">
                                <label htmlFor={`${id}-timezone`}>{__('Show appointment times in', 'wappointment')}</label>
                                <select id={`${id}-timezone`} value={timezone} onChange={(event) => setTimezone(event.target.value)}>
                                    {timezoneOptions(timezone).map((option) => (
                                        <option key={option.id} value={option.id}>{option.name}</option>
                                    ))}
                                </select>
                            </div>
                            <div className="wap-date-picker" role="group" aria-label={__('Choose appointment date', 'wappointment')}>
                                <div className="wap-date-picker-header">
                                    <button type="button" className="wap-date-nav"
                                        aria-label={calendarView === 'week' ? __('Previous week', 'wappointment') : __('Previous month', 'wappointment')}
                                        onClick={() => change('date', calendarView === 'week' ? addDays(firstDay, -7) : addMonths(selectedDate, -1))}>‹</button>
                                    <span aria-live="polite">
                                        {calendarView === 'week'
                                            ? `${dateLabel(firstDay, { month: 'long', day: 'numeric' })} – ${dateLabel(addDays(firstDay, 6), { month: 'long', day: 'numeric', year: 'numeric' })}`
                                            : dateLabel(currentMonth, { month: 'long', year: 'numeric' })}
                                    </span>
                                    <button type="button" className="wap-date-nav"
                                        aria-label={calendarView === 'week' ? __('Next week', 'wappointment') : __('Next month', 'wappointment')}
                                        onClick={() => change('date', calendarView === 'week' ? addDays(firstDay, 7) : addMonths(selectedDate, 1))}>›</button>
                                </div>
                                <div className="wap-date-view-toggle" role="group" aria-label={__('Calendar view', 'wappointment')}>
                                    <button type="button" aria-pressed={calendarView === 'week'} onClick={() => setCalendarView('week')}>{__('Week', 'wappointment')}</button>
                                    <button type="button" aria-pressed={calendarView === 'month'} onClick={() => setCalendarView('month')}>{__('Month', 'wappointment')}</button>
                                </div>
                                <div className="wap-date-picker-days" role="group" aria-label={__('Dates in organizer timezone', 'wappointment')}>
                                    {(calendarView === 'week' ? weekDates : monthDates).map((date) => <button key={date} type="button"
                                        className={`wap-date-day${calendarView === 'month' && date.slice(0, 7) !== currentMonth ? ' wap-date-day-outside' : ''}`}
                                        aria-label={dateLabel(date, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })}
                                        aria-pressed={date === selection.date}
                                        onClick={() => change('date', date)}>
                                        <span>{dateLabel(date, { weekday: 'short' })}</span>
                                        <strong>{dateLabel(date, { day: 'numeric' })}</strong>
                                    </button>)}
                                </div>
                                <p>{__('Dates use the selected calendar timezone:', 'wappointment')} {calendarTimezone}</p>
                            </div>
                            <button className="wap-btn wap-btn-primary" disabled={busy}>{__('Show available times', 'wappointment')}</button>
                            <button type="button" className="wap-btn wap-btn-secondary" disabled={busy}
                                onClick={findFirstAvailability}>{__('Find first available time (next 30 days)', 'wappointment')}</button>
                        </form>
                        {searchMessage && <p role="status" aria-live="polite">{searchMessage}</p>}
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
                                            readOnly={field === 'email' && Boolean(catalog.customer?.force_email)}
                                            maxLength={field === 'name' ? 100 : field === 'email' ? 191 : 40}
                                            value={contact[field]} onChange={(event) => setContact({ ...contact, [field]: event.target.value })} />
                                        {field === 'email' && catalog.customer?.force_email && <p>{__('Your account email is required for this booking.', 'wappointment')}</p>}
                                    </div>
                                ))}
                                {!rescheduling && window.wappointmentWidget?.legacyPrivacyNotice != null ? (
                                    <div className="wap-privacy-policy" dangerouslySetInnerHTML={{ __html: window.wappointmentWidget.legacyPrivacyNotice }} />
                                ) : !rescheduling && window.wappointmentWidget?.privacyPolicyUrl && (
                                    <p className="wap-privacy-policy">
                                        {__('Your information is handled according to the site', 'wappointment')}{' '}
                                        <a href={window.wappointmentWidget.privacyPolicyUrl} target="_blank" rel="noopener noreferrer">
                                            {__('privacy policy', 'wappointment')}
                                        </a>.
                                    </p>
                                )}
                                <button className="wap-btn wap-btn-primary" disabled={busy || !selection.start}>
                                    {rescheduling ? __('Confirm new time', 'wappointment') : __('Book appointment', 'wappointment')}
                                </button>
                            </>}
                        </form>}
                        {rescheduling && <button type="button" onClick={() => setRescheduling(false)}>{__('Back to appointment', 'wappointment')}</button>}
                    </>
                )}
            </div>
            {!configuration.close_cross && <button type="button" className="wap-inline-close" onClick={() => setOpen(false)}>{__('Close', 'wappointment')}</button>}
        </section>
    );
}
