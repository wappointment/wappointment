import React, { useEffect, useState } from 'react';
import Jobs from './Jobs';
import Clients from './Clients';
import Settings from './Settings';

// Component registry for addon overrides
const componentRegistry = {
  'wappointment-jobs': Jobs,
  'wappointment-clients': Clients,
  'wappointment-settings': Settings,
};

// Hook to allow addons to register component overrides
window.wappointmentRegisterComponent = (pageSlug, component) => {
  componentRegistry[pageSlug] = component;
};

// Hook to get the registered component
window.wappointmentGetComponent = (pageSlug) => {
  return componentRegistry[pageSlug];
};

const App = () => {
  // Initialize with the correct page from the start to avoid unnecessary renders
  const [currentPage, setCurrentPage] = useState(() => {
    return window.wappointmentData?.currentPage || 'wappointment-jobs';
  });

  useEffect(() => {
    const interceptMenuClicks = (e) => {
      const link = e.target.closest('a[href*="page=wappointment"]');
      if (link) {
        e.preventDefault();
        const url = new URL(link.href);
        const page = url.searchParams.get('page');
        
        // Update current page
        setCurrentPage(page);
        
        // Update browser URL without reload
        window.history.pushState({}, '', link.href);
      }
    };

    // Listen for menu clicks
    document.addEventListener('click', interceptMenuClicks);

    // Handle browser back/forward buttons
    const handlePopState = () => {
      const params = new URLSearchParams(window.location.search);
      const page = params.get('page') || 'wappointment-jobs';
      setCurrentPage(page);
    };
    window.addEventListener('popstate', handlePopState);

    return () => {
      document.removeEventListener('click', interceptMenuClicks);
      window.removeEventListener('popstate', handlePopState);
    };
  }, []);

  const renderPage = () => {
    // Get component from registry (allows addon overrides)
    const Component = componentRegistry[currentPage];
    
    if (Component) {
      return <Component />;
    }
    
    // Return null or a message instead of defaulting to Jobs
    return (
      <div className="wrap">
        <h1>Page Not Found</h1>
        <p>The requested page could not be found.</p>
      </div>
    );
  };

  return (
    <div className="wappointment-app">
      {renderPage()}
    </div>
  );
};

export default App;