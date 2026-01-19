import React, { useEffect, useState } from 'react';
import apiFetch from '../utils/apiFetch';

const Page2 = () => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    apiFetch('/page2')
      .then(response => {
        setData(response.data);
        setLoading(false);
      })
      .catch(err => {
        setError(err.message);
        setLoading(false);
      });
  }, []);

  if (loading) return <div className="loading">Loading...</div>;
  if (error) return <div className="error">Error: {error}</div>;
  if (!data) return null;

  return (
    <div className="wappointment-page">
      <h1>{data.title}</h1>
      <div className="card">
        <h2>{data.message}</h2>
        <p>{data.description}</p>
        <p><small>Loaded at: {new Date(data.timestamp * 1000).toLocaleString()}</small></p>
      </div>
    </div>
  );
};

export default Page2;
