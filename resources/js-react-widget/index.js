import React from 'react';
import { createRoot } from 'react-dom/client';
import BookingWidget from './components/BookingWidget';

let nextInstanceId = 0;

// Initialize all widget instances on the page
const initialize = () => {
    const widgets = document.querySelectorAll('.wappointment-widget-root');
    
    widgets.forEach(container => {
        const root = createRoot(container);
        const configuration = JSON.parse(container.dataset.config || '{}');
        const instanceId = container.dataset.instanceId || `wappointment-${++nextInstanceId}`;
        container.dataset.instanceId = instanceId;
        root.render(
            <BookingWidget
                title={container.dataset.title}
                buttonTitle={container.dataset.buttonTitle}
                configuration={configuration}
                instanceId={instanceId}
            />
        );
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize, { once: true });
} else {
    initialize();
}
