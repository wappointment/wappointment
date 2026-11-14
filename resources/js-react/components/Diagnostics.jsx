import React, { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import apiFetch from '../utils/apiFetch';

export default function Diagnostics() {
    const [report, setReport] = useState(null);
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        let active = true;
        apiFetch('/system/schema')
            .then((result) => { if (active) setReport(result.data.report); })
            .catch((failure) => { if (active) setError(failure.message); })
            .finally(() => { if (active) setLoading(false); });
        return () => { active = false; };
    }, []);

    const messages = {
        empty: __('No Wappointment tables were found for this site.', 'wappointment'),
        compatible: __('Core database structures match the master reference. This does not certify feature parity or production readiness.', 'wappointment'),
        incompatible: __('Database differences require review. This report does not modify or repair your tables.', 'wappointment'),
    };

    return <div className="wrap">
        <h1>{__('Wappointment Diagnostics', 'wappointment')}</h1>
        <p>{__("Read-only inspection of this site's database structure. No customer records or credentials are included.", 'wappointment')}</p>
        {loading && <p role="status">{__('Inspecting database structure...', 'wappointment')}</p>}
        {error && <div className="notice notice-error"><p role="alert">{error}</p></div>}
        {report && <>
            <p role="status">{messages[report.state]}</p>
            <p>{__('Master reference:', 'wappointment')} <code>{report.reference}</code></p>
            {report.issues.length > 0 && <table className="wp-list-table widefat striped">
                <caption className="screen-reader-text">{__('Database differences', 'wappointment')}</caption>
                <thead><tr>{[
                    __('Table', 'wappointment'), __('Kind', 'wappointment'), __('Member', 'wappointment'),
                    __('Expected', 'wappointment'), __('Actual', 'wappointment'),
                ].map((label) => <th key={label} scope="col">{label}</th>)}</tr></thead>
                <tbody>{report.issues.map((issue, index) => <tr key={`${issue.table}-${issue.kind}-${issue.member}-${index}`}>
                    <td>{issue.table}</td><td>{issue.kind}</td><td>{issue.member}</td>
                    <td>{issue.expected}</td><td>{issue.actual}</td>
                </tr>)}</tbody>
            </table>}
            {report.additionalTables.length > 0 && <>
                <h2>{__('Additional tables left untouched', 'wappointment')}</h2>
                <p>{__('These tables are outside the audited core schema; their compatibility has not been verified.', 'wappointment')}</p>
                <ul>{report.additionalTables.map((table) => <li key={table}><code>{table}</code></li>)}</ul>
            </>}
        </>}
    </div>;
}
