import React from 'react';
import { __ } from '@wordpress/i18n';

export const weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
const timeValue = (minutes) => `${String(Math.floor(minutes / 60) % 24).padStart(2, '0')}:${String(minutes % 60).padStart(2, '0')}`;

export default function WeeklyAvailabilityEditor({ value, onChange, disabled = false }) {
    const updateBlock = (day, index, endpoint, input) => {
        const [hour, minute] = input.split(':').map(Number);
        const minutes = hour * 60 + minute;
        onChange({ ...value, [day]: value[day].map((block, position) =>
            position === index ? block.map((current, column) =>
                column === endpoint ? (endpoint === 1 && minutes === 0 ? 1440 : minutes) : current) : block) });
    };
    return <>
        <p>{__('Times use the organizer timezone. An end time of 00:00 means midnight at the end of the day.', 'wappointment')}</p>
        {weekdays.map((day) => <fieldset key={day} disabled={disabled} style={{ marginBottom: 16 }}>
            <legend><strong>{day}</strong></legend>
            {value[day].map((block, index) => <div key={index}>
                <label>{__('From', 'wappointment')} <input type="time" required value={timeValue(block[0])}
                    onChange={(event) => updateBlock(day, index, 0, event.target.value)} /></label>
                <label> {__('until', 'wappointment')} <input type="time" required value={timeValue(block[1])}
                    onChange={(event) => updateBlock(day, index, 1, event.target.value)} /></label>
                <button type="button" className="button" onClick={() => onChange({
                    ...value, [day]: value[day].filter((_, position) => position !== index),
                })}>{__('Remove range', 'wappointment')}</button>
            </div>)}
            <button type="button" className="button" disabled={value[day].length >= 24} onClick={() => onChange({
                ...value, [day]: [...value[day], [540, 1020]],
            })}>{__('Add range', 'wappointment')}</button>
        </fieldset>)}
    </>;
}
