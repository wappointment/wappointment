import React, { useState, useEffect } from 'react';
import apiFetch from '../utils/apiFetch';
import Notification from './Notification';
import ErrorDetails from './ErrorDetails';

function Settings() {
    const [activeTab, setActiveTab] = useState('general');
    const [settings, setSettings] = useState({
        general: {},
        notifications: {},
        advanced: {}
    });
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState(null);
    const [notification, setNotification] = useState(null);

    useEffect(() => {
        loadSettings();
    }, []);

    const loadSettings = async () => {
        setLoading(true);
        setError(null);

        try {
            const response = await apiFetch('/settings');
            setSettings(response.data.settings);
        } catch (err) {
            setError(err);
        } finally {
            setLoading(false);
        }
    };

    const handleInputChange = (tab, field, value) => {
        setSettings(prev => ({
            ...prev,
            [tab]: {
                ...prev[tab],
                [field]: value
            }
        }));
    };

    const handleSave = async (e) => {
        e.preventDefault();
        setSaving(true);
        setError(null);

        try {
            // Flatten settings for saving
            const flatSettings = {
                ...settings.general,
                ...settings.notifications,
                ...settings.advanced
            };

            await apiFetch('/settings', {
                method: 'POST',
                body: JSON.stringify(flatSettings)
            });

            setNotification({ message: 'Settings saved successfully', type: 'success' });
        } catch (err) {
            setError(err);
            setNotification({ message: 'Failed to save settings', type: 'error' });
        } finally {
            setSaving(false);
        }
    };

    const renderTabContent = () => {
        if (activeTab === 'general') {
            return (
                <div className="settings-section">
                    <h2>General Settings</h2>
                    
                    <table className="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label htmlFor="approval_mode">Booking Approval Mode</label>
                                </th>
                                <td>
                                    <select
                                        id="approval_mode"
                                        value={settings.general.approval_mode || 1}
                                        onChange={(e) => handleInputChange('general', 'approval_mode', parseInt(e.target.value))}
                                    >
                                        <option value="1">Automatic</option>
                                        <option value="0">Manual</option>
                                    </select>
                                    <p className="description">How appointments should be approved (1 = automatic).</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="hours_before_booking_allowed">Hours Before Booking Allowed</label>
                                </th>
                                <td>
                                    <input
                                        type="number"
                                        id="hours_before_booking_allowed"
                                        className="small-text"
                                        value={settings.general.hours_before_booking_allowed || 3}
                                        onChange={(e) => handleInputChange('general', 'hours_before_booking_allowed', parseInt(e.target.value))}
                                        min="0"
                                    />
                                    <span> hours</span>
                                    <p className="description">Minimum time before an appointment can be booked.</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="allow_cancellation">Allow Cancellation</label>
                                </th>
                                <td>
                                    <label>
                                        <input
                                            type="checkbox"
                                            id="allow_cancellation"
                                            checked={settings.general.allow_cancellation || false}
                                            onChange={(e) => handleInputChange('general', 'allow_cancellation', e.target.checked)}
                                        />
                                        <span> Allow clients to cancel appointments</span>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="hours_before_cancellation_allowed">Hours Before Cancellation</label>
                                </th>
                                <td>
                                    <input
                                        type="number"
                                        id="hours_before_cancellation_allowed"
                                        className="small-text"
                                        value={settings.general.hours_before_cancellation_allowed || 24}
                                        onChange={(e) => handleInputChange('general', 'hours_before_cancellation_allowed', parseInt(e.target.value))}
                                        min="0"
                                        disabled={!settings.general.allow_cancellation}
                                    />
                                    <span> hours</span>
                                    <p className="description">Minimum hours before appointment to allow cancellation.</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="allow_rescheduling">Allow Rescheduling</label>
                                </th>
                                <td>
                                    <label>
                                        <input
                                            type="checkbox"
                                            id="allow_rescheduling"
                                            checked={settings.general.allow_rescheduling || false}
                                            onChange={(e) => handleInputChange('general', 'allow_rescheduling', e.target.checked)}
                                        />
                                        <span> Allow clients to reschedule appointments</span>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="hours_before_rescheduling_allowed">Hours Before Rescheduling</label>
                                </th>
                                <td>
                                    <input
                                        type="number"
                                        id="hours_before_rescheduling_allowed"
                                        className="small-text"
                                        value={settings.general.hours_before_rescheduling_allowed || 24}
                                        onChange={(e) => handleInputChange('general', 'hours_before_rescheduling_allowed', parseInt(e.target.value))}
                                        min="0"
                                        disabled={!settings.general.allow_rescheduling}
                                    />
                                    <span> hours</span>
                                    <p className="description">Minimum hours before appointment to allow rescheduling.</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="currency">Currency</label>
                                </th>
                                <td>
                                    <input
                                        type="text"
                                        id="currency"
                                        className="small-text"
                                        value={settings.general.currency || 'USD'}
                                        onChange={(e) => handleInputChange('general', 'currency', e.target.value)}
                                    />
                                    <p className="description">Currency code (e.g., USD, EUR, GBP).</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="max_active_bookings">Max Active Bookings per Client</label>
                                </th>
                                <td>
                                    <input
                                        type="number"
                                        id="max_active_bookings"
                                        className="small-text"
                                        value={settings.general.max_active_bookings || 0}
                                        onChange={(e) => handleInputChange('general', 'max_active_bookings', parseInt(e.target.value))}
                                        min="0"
                                    />
                                    <p className="description">Maximum active bookings per client (0 = unlimited).</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            );
        }

        if (activeTab === 'notifications') {
            return (
                <div className="settings-section">
                    <h2>Notification Settings</h2>
                    
                    <table className="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label htmlFor="mail_status">Email Notifications</label>
                                </th>
                                <td>
                                    <label>
                                        <input
                                            type="checkbox"
                                            id="mail_status"
                                            checked={settings.notifications.mail_status !== false}
                                            onChange={(e) => handleInputChange('notifications', 'mail_status', e.target.checked)}
                                        />
                                        <span> Enable email notifications</span>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="notify_new_appointments">New Appointment Notification</label>
                                </th>
                                <td>
                                    <label>
                                        <input
                                            type="checkbox"
                                            id="notify_new_appointments"
                                            checked={settings.notifications.notify_new_appointments !== false}
                                            onChange={(e) => handleInputChange('notifications', 'notify_new_appointments', e.target.checked)}
                                        />
                                        <span> Notify when new appointment is booked</span>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="notify_pending_appointments">Pending Appointment Notification</label>
                                </th>
                                <td>
                                    <label>
                                        <input
                                            type="checkbox"
                                            id="notify_pending_appointments"
                                            checked={settings.notifications.notify_pending_appointments !== false}
                                            onChange={(e) => handleInputChange('notifications', 'notify_pending_appointments', e.target.checked)}
                                        />
                                        <span> Notify when appointment is pending approval</span>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="notify_canceled_appointments">Cancellation Notification</label>
                                </th>
                                <td>
                                    <label>
                                        <input
                                            type="checkbox"
                                            id="notify_canceled_appointments"
                                            checked={settings.notifications.notify_canceled_appointments !== false}
                                            onChange={(e) => handleInputChange('notifications', 'notify_canceled_appointments', e.target.checked)}
                                        />
                                        <span> Notify when appointment is cancelled</span>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="notify_rescheduled_appointments">Reschedule Notification</label>
                                </th>
                                <td>
                                    <label>
                                        <input
                                            type="checkbox"
                                            id="notify_rescheduled_appointments"
                                            checked={settings.notifications.notify_rescheduled_appointments !== false}
                                            onChange={(e) => handleInputChange('notifications', 'notify_rescheduled_appointments', e.target.checked)}
                                        />
                                        <span> Notify when appointment is rescheduled</span>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="weekly_summary">Weekly Summary</label>
                                </th>
                                <td>
                                    <label>
                                        <input
                                            type="checkbox"
                                            id="weekly_summary"
                                            checked={settings.notifications.weekly_summary || false}
                                            onChange={(e) => handleInputChange('notifications', 'weekly_summary', e.target.checked)}
                                        />
                                        <span> Send weekly summary email</span>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="weekly_summary_day">Weekly Summary Day</label>
                                </th>
                                <td>
                                    <select
                                        id="weekly_summary_day"
                                        value={settings.notifications.weekly_summary_day || 1}
                                        onChange={(e) => handleInputChange('notifications', 'weekly_summary_day', parseInt(e.target.value))}
                                        disabled={!settings.notifications.weekly_summary}
                                    >
                                        <option value="0">Sunday</option>
                                        <option value="1">Monday</option>
                                        <option value="2">Tuesday</option>
                                        <option value="3">Wednesday</option>
                                        <option value="4">Thursday</option>
                                        <option value="5">Friday</option>
                                        <option value="6">Saturday</option>
                                    </select>
                                    <p className="description">Day to send weekly summary.</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="daily_summary">Daily Summary</label>
                                </th>
                                <td>
                                    <label>
                                        <input
                                            type="checkbox"
                                            id="daily_summary"
                                            checked={settings.notifications.daily_summary || false}
                                            onChange={(e) => handleInputChange('notifications', 'daily_summary', e.target.checked)}
                                        />
                                        <span> Send daily summary email</span>
                                    </label>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            );
        }

        if (activeTab === 'advanced') {
            return (
                <div className="settings-section">
                    <h2>Advanced Settings</h2>
                    
                    <table className="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label htmlFor="buffer_time">Buffer Time</label>
                                </th>
                                <td>
                                    <input
                                        type="number"
                                        id="buffer_time"
                                        className="small-text"
                                        value={settings.advanced.buffer_time || 0}
                                        onChange={(e) => handleInputChange('advanced', 'buffer_time', parseInt(e.target.value))}
                                        min="0"
                                    />
                                    <span> minutes</span>
                                    <p className="description">Time to prepare between appointments.</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="scheduler_mode">Scheduler Mode</label>
                                </th>
                                <td>
                                    <select
                                        id="scheduler_mode"
                                        value={settings.advanced.scheduler_mode || 0}
                                        onChange={(e) => handleInputChange('advanced', 'scheduler_mode', parseInt(e.target.value))}
                                    >
                                        <option value="0">Standard</option>
                                        <option value="1">Advanced</option>
                                    </select>
                                    <p className="description">Scheduling mode for appointments.</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="force_ugly_permalinks">Force Ugly Permalinks</label>
                                </th>
                                <td>
                                    <label>
                                        <input
                                            type="checkbox"
                                            id="force_ugly_permalinks"
                                            checked={settings.advanced.force_ugly_permalinks || false}
                                            onChange={(e) => handleInputChange('advanced', 'force_ugly_permalinks', e.target.checked)}
                                        />
                                        <span> Use query string URLs instead of pretty permalinks</span>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="cache">Cache</label>
                                </th>
                                <td>
                                    <label>
                                        <input
                                            type="checkbox"
                                            id="cache"
                                            checked={settings.advanced.cache || false}
                                            onChange={(e) => handleInputChange('advanced', 'cache', e.target.checked)}
                                        />
                                        <span> Enable caching for performance</span>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="autofill">Autofill</label>
                                </th>
                                <td>
                                    <label>
                                        <input
                                            type="checkbox"
                                            id="autofill"
                                            checked={settings.advanced.autofill !== false}
                                            onChange={(e) => handleInputChange('advanced', 'autofill', e.target.checked)}
                                        />
                                        <span> Enable form autofill for returning clients</span>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="onsite_enabled">On-Site Appointments</label>
                                </th>
                                <td>
                                    <label>
                                        <input
                                            type="checkbox"
                                            id="onsite_enabled"
                                            checked={settings.advanced.onsite_enabled !== false}
                                            onChange={(e) => handleInputChange('advanced', 'onsite_enabled', e.target.checked)}
                                        />
                                        <span> Allow on-site appointments</span>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="clean_pending_every">Clean Pending Appointments</label>
                                </th>
                                <td>
                                    <input
                                        type="number"
                                        id="clean_pending_every"
                                        className="small-text"
                                        value={settings.advanced.clean_pending_every || 25}
                                        onChange={(e) => handleInputChange('advanced', 'clean_pending_every', parseInt(e.target.value))}
                                        min="1"
                                    />
                                    <span> hours</span>
                                    <p className="description">Automatically clean old pending appointments.</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="starting_each">Slot Interval</label>
                                </th>
                                <td>
                                    <input
                                        type="number"
                                        id="starting_each"
                                        className="small-text"
                                        value={settings.advanced.starting_each || 30}
                                        onChange={(e) => handleInputChange('advanced', 'starting_each', parseInt(e.target.value))}
                                        min="5"
                                        step="5"
                                    />
                                    <span> minutes</span>
                                    <p className="description">Time interval between available appointment slots.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            );
        }
    };

    if (loading) {
        return (
            <div className="wrap">
                <h1>Settings</h1>
                <p>Loading settings...</p>
            </div>
        );
    }

    return (
        <div className="wrap">
            <h1>Settings</h1>
            
            {error && (
                <div style={{ marginBottom: '20px' }}>
                    <ErrorDetails error={error} />
                </div>
            )}

            <nav className="nav-tab-wrapper">
                <a
                    href="#"
                    className={`nav-tab ${activeTab === 'general' ? 'nav-tab-active' : ''}`}
                    onClick={(e) => {
                        e.preventDefault();
                        setActiveTab('general');
                    }}
                >
                    General
                </a>
                <a
                    href="#"
                    className={`nav-tab ${activeTab === 'notifications' ? 'nav-tab-active' : ''}`}
                    onClick={(e) => {
                        e.preventDefault();
                        setActiveTab('notifications');
                    }}
                >
                    Notifications
                </a>
                <a
                    href="#"
                    className={`nav-tab ${activeTab === 'advanced' ? 'nav-tab-active' : ''}`}
                    onClick={(e) => {
                        e.preventDefault();
                        setActiveTab('advanced');
                    }}
                >
                    Advanced
                </a>
            </nav>

            <form onSubmit={handleSave} style={{ marginTop: '20px' }}>
                {renderTabContent()}

                <p className="submit">
                    <button
                        type="submit"
                        className="button button-primary"
                        disabled={saving}
                    >
                        {saving ? 'Saving...' : 'Save Settings'}
                    </button>
                </p>
            </form>

            {notification && (
                <Notification
                    message={notification.message}
                    type={notification.type}
                    onClose={() => setNotification(null)}
                />
            )}
        </div>
    );
}

export default Settings;
