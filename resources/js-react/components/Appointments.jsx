import React, { useEffect, useState } from 'react';
import apiFetch from '../utils/apiFetch';

export default function Appointments() {
    const [appointments, setAppointments] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [saving, setSaving] = useState(false);
    const load = async () => {
        const result = await apiFetch('/appointments');
        setAppointments(result.data.appointments);
    };
    useEffect(() => {
        load().catch((failure) => setError(failure.message)).finally(() => setLoading(false));
    }, []);
    const changeStatus = async (id, status) => {
        setSaving(true); setError('');
        try {
            await apiFetch('/appointments', { method: 'POST', body: JSON.stringify({ id: Number(id), status }) });
            await load();
        } catch (failure) {
            setError(failure.message);
        } finally {
            setSaving(false);
        }
    };
    const date = (value) => new Date(`${value.replace(' ', 'T')}Z`).toLocaleString();
    return <div className="wrap">
        <h1>Appointments</h1>
        <p>The latest 100 appointments. Times use your browser timezone.</p>
        {error && <div className="notice notice-error"><p role="alert">{error}</p></div>}
        {loading ? <p role="status">Loading appointments...</p> : <table className="wp-list-table widefat fixed striped">
            <thead><tr>{['Client', 'Service', 'Start', 'Status', 'Actions'].map((label) => <th key={label} scope="col">{label}</th>)}</tr></thead>
            <tbody>{appointments.map((appointment) => <tr key={appointment.id}>
                <td>{appointment.client_name}<br />{appointment.client_email}</td>
                <td>{appointment.service_name}</td><td>{date(appointment.start_at)}</td>
                <td>{Number(appointment.status) < 0 ? 'Cancelled' : Number(appointment.status) === 1 ? 'Confirmed' : 'Pending'}</td>
                <td>
                    {Number(appointment.status) === 0 && <button className="button" disabled={saving} onClick={() => changeStatus(appointment.id, 1)}>Confirm</button>}
                    {Number(appointment.status) >= 0 && <button className="button" disabled={saving} onClick={() => changeStatus(appointment.id, -1)}>Cancel</button>}
                </td>
            </tr>)}</tbody>
        </table>}
    </div>;
}
