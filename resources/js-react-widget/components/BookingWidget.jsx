import React, { useState } from 'react';

function BookingWidget() {
    const [step, setStep] = useState(0); // 0 = button, 1-3 = steps, 4 = confirmation
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        phone: '',
        date: '',
        time: ''
    });

    const title = window.wappointmentWidget?.title || 'Book now';

    const handleNext = () => {
        setStep(step + 1);
    };

    const handleBack = () => {
        setStep(step - 1);
    };

    const handleInputChange = (field, value) => {
        setFormData({ ...formData, [field]: value });
    };

    const handleSubmit = () => {
        // TODO: Submit booking to API
        console.log('Booking submitted:', formData);
        setStep(4); // Go to confirmation
    };

    if (step === 0) {
        return (
            <div className="wap-widget">
                <button className="wap-book-btn" onClick={handleNext}>
                    {title}
                </button>
            </div>
        );
    }

    if (step === 1) {
        return (
            <div className="wap-widget wap-widget-open">
                <div className="wap-widget-header">
                    <h3>Your Information</h3>
                    <button className="wap-close-btn" onClick={() => setStep(0)}>×</button>
                </div>
                <div className="wap-widget-body">
                    <div className="wap-form-group">
                        <label>Name</label>
                        <input
                            type="text"
                            value={formData.name}
                            onChange={(e) => handleInputChange('name', e.target.value)}
                            placeholder="Enter your name"
                        />
                    </div>
                    <div className="wap-form-group">
                        <label>Email</label>
                        <input
                            type="email"
                            value={formData.email}
                            onChange={(e) => handleInputChange('email', e.target.value)}
                            placeholder="Enter your email"
                        />
                    </div>
                    <div className="wap-form-group">
                        <label>Phone</label>
                        <input
                            type="tel"
                            value={formData.phone}
                            onChange={(e) => handleInputChange('phone', e.target.value)}
                            placeholder="Enter your phone"
                        />
                    </div>
                </div>
                <div className="wap-widget-footer">
                    <button className="wap-btn wap-btn-secondary" onClick={() => setStep(0)}>Cancel</button>
                    <button className="wap-btn wap-btn-primary" onClick={handleNext}>Next</button>
                </div>
            </div>
        );
    }

    if (step === 2) {
        return (
            <div className="wap-widget wap-widget-open">
                <div className="wap-widget-header">
                    <h3>Select Date</h3>
                    <button className="wap-close-btn" onClick={() => setStep(0)}>×</button>
                </div>
                <div className="wap-widget-body">
                    <div className="wap-form-group">
                        <label>Preferred Date</label>
                        <input
                            type="date"
                            value={formData.date}
                            onChange={(e) => handleInputChange('date', e.target.value)}
                        />
                    </div>
                </div>
                <div className="wap-widget-footer">
                    <button className="wap-btn wap-btn-secondary" onClick={handleBack}>Back</button>
                    <button className="wap-btn wap-btn-primary" onClick={handleNext}>Next</button>
                </div>
            </div>
        );
    }

    if (step === 3) {
        return (
            <div className="wap-widget wap-widget-open">
                <div className="wap-widget-header">
                    <h3>Select Time</h3>
                    <button className="wap-close-btn" onClick={() => setStep(0)}>×</button>
                </div>
                <div className="wap-widget-body">
                    <div className="wap-form-group">
                        <label>Preferred Time</label>
                        <select
                            value={formData.time}
                            onChange={(e) => handleInputChange('time', e.target.value)}
                        >
                            <option value="">Select a time</option>
                            <option value="09:00">09:00 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="14:00">02:00 PM</option>
                            <option value="15:00">03:00 PM</option>
                            <option value="16:00">04:00 PM</option>
                        </select>
                    </div>
                </div>
                <div className="wap-widget-footer">
                    <button className="wap-btn wap-btn-secondary" onClick={handleBack}>Back</button>
                    <button className="wap-btn wap-btn-primary" onClick={handleSubmit}>Confirm</button>
                </div>
            </div>
        );
    }

    if (step === 4) {
        return (
            <div className="wap-widget wap-widget-open">
                <div className="wap-widget-header">
                    <h3>Booking Confirmed!</h3>
                    <button className="wap-close-btn" onClick={() => setStep(0)}>×</button>
                </div>
                <div className="wap-widget-body">
                    <div className="wap-confirmation">
                        <div className="wap-success-icon">✓</div>
                        <p><strong>Thank you, {formData.name}!</strong></p>
                        <p>Your appointment has been confirmed.</p>
                        <div className="wap-booking-details">
                            <p><strong>Date:</strong> {formData.date}</p>
                            <p><strong>Time:</strong> {formData.time}</p>
                            <p><strong>Email:</strong> {formData.email}</p>
                        </div>
                        <p className="wap-confirmation-note">
                            A confirmation email has been sent to {formData.email}
                        </p>
                    </div>
                </div>
                <div className="wap-widget-footer">
                    <button className="wap-btn wap-btn-primary" onClick={() => setStep(0)}>Close</button>
                </div>
            </div>
        );
    }

    return null;
}

export default BookingWidget;
