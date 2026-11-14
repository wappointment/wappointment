import React from 'react';
import { render, screen } from '@testing-library/react';
import Diagnostics from '../../js-react/components/Diagnostics';
import apiFetch from '../../js-react/utils/apiFetch';

jest.mock('../../js-react/utils/apiFetch');
jest.mock('@wordpress/i18n', () => ({ __: (text) => text }), { virtual: true });

beforeEach(() => apiFetch.mockReset());

test('diagnostics only reads schema metadata and distinguishes compatibility from release readiness', async () => {
    apiFetch.mockResolvedValue({
        data: { report: { state: 'compatible', reference: 'master-sha', issues: [], additionalTables: [] } },
    });
    render(<Diagnostics />);
    expect(await screen.findByText(/does not certify feature parity/)).toBeVisible();
    expect(apiFetch).toHaveBeenCalledTimes(1);
    expect(apiFetch).toHaveBeenCalledWith('/system/schema');
    expect(screen.queryByRole('button')).not.toBeInTheDocument();
});

test('diagnostics displays schema differences and does not claim to repair extra tables', async () => {
    apiFetch.mockResolvedValue({
        data: { report: {
            state: 'incompatible', reference: 'master-sha', additionalTables: ['addon_records'],
            issues: [{ table: 'clients', kind: 'column', member: 'options', expected: 'text', actual: 'longtext' }],
        } },
    });
    render(<Diagnostics />);
    expect(await screen.findByRole('table', { name: 'Database differences' })).toBeVisible();
    expect(screen.getByText('longtext')).toBeVisible();
    expect(screen.getByText('addon_records')).toBeVisible();
    expect(screen.getByText(/does not modify or repair/)).toBeVisible();
    expect(screen.getByText(/compatibility has not been verified/)).toBeVisible();
});

test('diagnostics surfaces failed metadata requests without displaying a successful report', async () => {
    apiFetch.mockRejectedValue(new Error('Could not inspect database metadata.'));
    render(<Diagnostics />);
    expect(await screen.findByRole('alert')).toHaveTextContent('Could not inspect database metadata.');
    expect(screen.queryByText(/structures match/)).not.toBeInTheDocument();
});

test('an empty schema is not displayed as compatible', async () => {
    apiFetch.mockResolvedValue({
        data: { report: { state: 'empty', reference: 'master-sha', issues: [], additionalTables: [] } },
    });
    render(<Diagnostics />);
    expect(await screen.findByText(/No Wappointment tables/)).toBeVisible();
    expect(screen.queryByText(/structures match/)).not.toBeInTheDocument();
});
