import React, { useState } from 'react';
import DataList from './DataList';
import JobDetailsModal from './JobDetailsModal';

const Jobs = () => {
  const [selectedJob, setSelectedJob] = useState(null);

  const columns = [
    { key: 'id', label: 'ID' },
    { key: 'queue', label: 'Queue' },
    { key: 'appointment_id', label: 'Appointment ID' },
    { key: 'attempts', label: 'Attempts' },
    { key: 'available_at', label: 'Available At' },
    { key: 'created_at', label: 'Created At' },
  ];

  const renderRow = (job) => (
    <>
      <td>{job.id}</td>
      <td>{job.queue || 'N/A'}</td>
      <td>{job.appointment_id || 'N/A'}</td>
      <td>{job.attempts}</td>
      <td>{job.available_at ? new Date(job.available_at * 1000).toLocaleString() : 'N/A'}</td>
      <td>{job.created_at ? new Date(job.created_at * 1000).toLocaleString() : 'N/A'}</td>
    </>
  );

  return (
    <>
      <DataList
        endpoint="/jobs"
        title="Jobs"
        columns={columns}
        renderRow={renderRow}
        onRowClick={setSelectedJob}
        emptyMessage="No jobs found in the database."
      />
      
      {selectedJob && (
        <JobDetailsModal 
          job={selectedJob} 
          onClose={() => setSelectedJob(null)} 
        />
      )}
    </>
  );
};

export default Jobs;
