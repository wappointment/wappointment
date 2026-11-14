import React from 'react';
import { createRoot } from 'react-dom/client';
import BookingWidget from './components/BookingWidget';

// Initialize all widget instances on the page
const initialize = () => {
    const widgets = document.querySelectorAll('.wappointment-widget-root');
    
    widgets.forEach(container => {
        const root = createRoot(container);
        root.render(<BookingWidget title={container.dataset.title} />);
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize, { once: true });
} else {
    initialize();
}
