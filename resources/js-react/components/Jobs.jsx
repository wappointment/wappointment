import React, { useEffect, useState } from 'react';
import apiFetch from '../utils/apiFetch';

const Jobs = () => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [currentPage, setCurrentPage] = useState(1);

  const loadJobs = (page = 1) => {
    setLoading(true);
    apiFetch(`/jobs?page_num=${page}&per_page=10`)
      .then(response => {
        setData(response.data);
        setCurrentPage(page);
        setLoading(false);
      })
      .catch(err => {
        setError(err.message);
        setLoading(false);
      });
  };

  useEffect(() => {
    loadJobs(1);
  }, []);

  if (loading) return <div className="loading">Loading jobs...</div>;
  if (error) return <div className="error">Error: {error}</div>;
  if (!data) return null;

  const { data: jobs, total, page, total_pages } = data;

  return (
    <div className="wappointment-page">
      <h1>Jobs</h1>
      <p>Total jobs: {total}</p>
      
      {jobs.length === 0 ? (
        <div className="notice notice-info">
          <p>No jobs found in the database.</p>
        </div>
      ) : (
        <>
          <table className="wp-list-table widefat fixed striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Status</th>
                <th>Subject</th>
                <th>Created</th>
              </tr>
            </thead>
            <tbody>
              {jobs.map((job) => (
                <tr key={job.id}>
                  <td>{job.id}</td>
                  <td>{job.type || 'N/A'}</td>
                  <td>{job.status || 'N/A'}</td>
                  <td>{job.subject || 'N/A'}</td>
                  <td>{job.created_at || 'N/A'}</td>
                </tr>
              ))}
            </tbody>
          </table>

          {total_pages > 1 && (
            <div className="tablenav">
              <div className="tablenav-pages">
                <span className="displaying-num">{total} items</span>
                <span className="pagination-links">
                  {page > 1 && (
                    <button 
                      className="button"
                      onClick={() => loadJobs(page - 1)}
                    >
                      Previous
                    </button>
                  )}
                  <span className="paging-input">
                    Page {page} of {total_pages}
                  </span>
                  {page < total_pages && (
                    <button 
                      className="button"
                      onClick={() => loadJobs(page + 1)}
                    >
                      Next
                    </button>
                  )}
                </span>
              </div>
            </div>
          )}
        </>
      )}
    </div>
  );
};

export default Jobs;
