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
                                    <label htmlFor="site_name">Site Name</label>
                                </th>
                                <td>
                                    <input
                                        type="text"
                                        id="site_name"
                                        className="regular-text"
                                        value={settings.general.site_name || ''}
                                        onChange={(e) => handleInputChange('general', 'site_name', e.target.value)}
                                    />
                                    <p className="description">Your business or site name displayed in emails.</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="appointment_duration">Default Appointment Duration</label>
                                </th>
                                <td>
                                    <select
                                        id="appointment_duration"
                                        value={settings.general.appointment_duration || '30'}
                                        onChange={(e) => handleInputChange('general', 'appointment_duration', e.target.value)}
                                    >
                                        <option value="15">15 minutes</option>
                                        <option value="30">30 minutes</option>
                                        <option value="45">45 minutes</option>
                                        <option value="60">60 minutes</option>
                                        <option value="90">90 minutes</option>
                                        <option value="120">120 minutes</option>
                                    </select>
                                    <p className="description">Default duration for new appointments.</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="booking_approval">Booking Approval</label>
                                </th>
                                <td>
                                    <select
                                        id="booking_approval"
                                        value={settings.general.booking_approval || 'automatic'}
                                        onChange={(e) => handleInputChange('general', 'booking_approval', e.target.value)}
                                    >
                                        <option value="automatic">Automatic</option>
                                        <option value="manual">Manual</option>
                                    </select>
                                    <p className="description">How appointments should be approved.</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="min_booking_notice">Minimum Booking Notice</label>
                                </th>
                                <td>
                                    <input
                                        type="number"
                                        id="min_booking_notice"
                                        className="small-text"
                                        value={settings.general.min_booking_notice || '60'}
                                        onChange={(e) => handleInputChange('general', 'min_booking_notice', e.target.value)}
                                        min="0"
                                    />
                                    <span> minutes</span>
                                    <p className="description">Minimum time before an appointment can be booked.</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="max_booking_advance">Maximum Booking Advance</label>
                                </th>
                                <td>
                                    <input
                                        type="number"
                                        id="max_booking_advance"
                                        className="small-text"
                                        value={settings.general.max_booking_advance || '30'}
                                        onChange={(e) => handleInputChange('general', 'max_booking_advance', e.target.value)}
                                        min="1"
                                    />
                                    <span> days</span>
                                    <p className="description">How far in advance appointments can be booked.</p>
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
                                    <label htmlFor="admin_email">Admin Email</label>
                                </th>
                                <td>
                                    <input
                                        type="email"
                                        id="admin_email"
                                        className="regular-text"
                                        value={settings.notifications.admin_email || ''}
                                        onChange={(e) => handleInputChange('notifications', 'admin_email', e.target.value)}
                                    />
                                    <p className="description">Email address to receive admin notifications.</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="notification_booking">New Booking Notification</label>
                                </th>
                                <td>
                                    <label>
                                        <input
                                            type="checkbox"
                                            id="notification_booking"
                                            checked={settings.notifications.notification_booking || false}
                                            onChange={(e) => handleInputChange('notifications', 'notification_booking', e.target.checked)}
                                        />
                                        <span> Send notification when new booking is made</span>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="notification_cancellation">Cancellation Notification</label>
                                </th>
                                <td>
                                    <label>
                                        <input
                                            type="checkbox"
                                            id="notification_cancellation"
                                            checked={settings.notifications.notification_cancellation || false}
                                            onChange={(e) => handleInputChange('notifications', 'notification_cancellation', e.target.checked)}
                                        />
                                        <span> Send notification when booking is cancelled</span>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="reminder_enabled">Client Reminders</label>
                                </th>
                                <td>
                                    <label>
                                        <input
                                            type="checkbox"
                                            id="reminder_enabled"
                                            checked={settings.notifications.reminder_enabled || false}
                                            onChange={(e) => handleInputChange('notifications', 'reminder_enabled', e.target.checked)}
                                        />
                                        <span> Send reminder emails to clients</span>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="reminder_time">Reminder Time</label>
                                </th>
                                <td>
                                    <select
                                        id="reminder_time"
                                        value={settings.notifications.reminder_time || '24'}
                                        onChange={(e) => handleInputChange('notifications', 'reminder_time', e.target.value)}
                                        disabled={!settings.notifications.reminder_enabled}
                                    >
                                        <option value="1">1 hour before</option>
                                        <option value="2">2 hours before</option>
                                        <option value="24">1 day before</option>
                                        <option value="48">2 days before</option>
                                        <option value="72">3 days before</option>
                                    </select>
                                    <p className="description">When to send reminder emails.</p>
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
                                    <label htmlFor="timezone">Timezone</label>
                                </th>
                                <td>
                                    <select
                                        id="timezone"
                                        className="regular-text"
                                        value={settings.advanced.timezone || 'UTC'}
                                        onChange={(e) => handleInputChange('advanced', 'timezone', e.target.value)}
                                    >
                                        <option value="UTC">UTC</option>
                                        <option value="America/New_York">America/New York</option>
                                        <option value="America/Chicago">America/Chicago</option>
                                        <option value="America/Denver">America/Denver</option>
                                        <option value="America/Los_Angeles">America/Los Angeles</option>
                                        <option value="Europe/London">Europe/London</option>
                                        <option value="Europe/Paris">Europe/Paris</option>
                                        <option value="Asia/Tokyo">Asia/Tokyo</option>
                                        <option value="Australia/Sydney">Australia/Sydney</option>
                                    </select>
                                    <p className="description">Your business timezone for appointments.</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="buffer_time">Buffer Time</label>
                                </th>
                                <td>
                                    <input
                                        type="number"
                                        id="buffer_time"
                                        className="small-text"
                                        value={settings.advanced.buffer_time || '0'}
                                        onChange={(e) => handleInputChange('advanced', 'buffer_time', e.target.value)}
                                        min="0"
                                    />
                                    <span> minutes</span>
                                    <p className="description">Time to prepare between appointments.</p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="advanced_allow_cancel">Allow Cancellation</label>
                                </th>
                                <td>
                                    <label>
                                        <input
                                            type="checkbox"
                                            id="advanced_allow_cancel"
                                            checked={settings.advanced.advanced_allow_cancel || false}
                                            onChange={(e) => handleInputChange('advanced', 'advanced_allow_cancel', e.target.checked)}
                                        />
                                        <span> Allow clients to cancel appointments</span>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="advanced_allow_reschedule">Allow Rescheduling</label>
                                </th>
                                <td>
                                    <label>
                                        <input
                                            type="checkbox"
                                            id="advanced_allow_reschedule"
                                            checked={settings.advanced.advanced_allow_reschedule || false}
                                            onChange={(e) => handleInputChange('advanced', 'advanced_allow_reschedule', e.target.checked)}
                                        />
                                        <span> Allow clients to reschedule appointments</span>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label htmlFor="advanced_max_bookings">Max Active Bookings per Client</label>
                                </th>
                                <td>
                                    <input
                                        type="number"
                                        id="advanced_max_bookings"
                                        className="small-text"
                                        value={settings.advanced.advanced_max_bookings || '0'}
                                        onChange={(e) => handleInputChange('advanced', 'advanced_max_bookings', e.target.value)}
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
