import React, { useEffect, useState } from 'react';
import Page1 from './Page1';
import Page2 from './Page2';
import Page3 from './Page3';
import Jobs from './Jobs';
import Clients from './Clients';

const App = () => {
  const [currentPage, setCurrentPage] = useState('wappointment');

  useEffect(() => {
    // Get initial page from localized data
    if (window.wappointmentData) {
      setCurrentPage(window.wappointmentData.currentPage);
    }

    // Intercept menu clicks to prevent page reload
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
      const page = params.get('page') || 'wappointment';
      setCurrentPage(page);
    };
    window.addEventListener('popstate', handlePopState);

    return () => {
      document.removeEventListener('click', interceptMenuClicks);
      window.removeEventListener('popstate', handlePopState);
    };
  }, []);

  const renderPage = () => {
    switch (currentPage) {
      case 'wappointment-page2':
        return <Page2 />;
      case 'wappointment-page3':
        return <Page3 />;
      case 'wappointment-jobs':
        return <Jobs />;
      case 'wappointment-clients':
        return <Clients />;
      case 'wappointment':
      default:
        return <Page1 />;
    }
  };

  return (
    <div className="wappointment-app">
      {renderPage()}
    </div>
  );
};

export default App;
