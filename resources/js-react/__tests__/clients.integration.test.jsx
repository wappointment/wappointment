import React from 'react';
import { render, screen, waitFor, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import Clients from '../../components/Clients';
import { resetClients } from '../mocks/handlers';

describe('Clients Integration Tests', () => {
  beforeEach(() => {
    resetClients();
  });

  test('displays list of clients from API', async () => {
    render(<Clients />);

    // Wait for clients to load
    await waitFor(() => {
      expect(screen.getByText('John Doe')).toBeInTheDocument();
    });

    expect(screen.getByText('jane@example.com')).toBeInTheDocument();
  });

  test('creates a new client through the full flow', async () => {
    const user = userEvent.setup();
    render(<Clients />);

    // Wait for initial load
    await waitFor(() => {
      expect(screen.getByText('John Doe')).toBeInTheDocument();
    });

    // Click new client button
    const newButton = screen.getByRole('button', { name: /new client/i });
    await user.click(newButton);

    // Fill out form
    const modal = screen.getByRole('dialog');
    const nameInput = within(modal).getByLabelText(/name/i);
    const emailInput = within(modal).getByLabelText(/email/i);

    await user.type(nameInput, 'Alice Johnson');
    await user.type(emailInput, 'alice@example.com');

    // Submit form
    const saveButton = within(modal).getByRole('button', { name: /save/i });
    await user.click(saveButton);

    // Verify success notification
    await waitFor(() => {
      expect(screen.getByText(/client created successfully/i)).toBeInTheDocument();
    });

    // Verify new client appears in list
    await waitFor(() => {
      expect(screen.getByText('Alice Johnson')).toBeInTheDocument();
      expect(screen.getByText('alice@example.com')).toBeInTheDocument();
    });
  });

  test('updates an existing client', async () => {
    const user = userEvent.setup();
    render(<Clients />);

    // Wait for clients to load
    await waitFor(() => {
      expect(screen.getByText('John Doe')).toBeInTheDocument();
    });

    // Find and click edit button for John Doe
    const johnRow = screen.getByText('John Doe').closest('tr');
    const editButton = within(johnRow).getByRole('button', { name: /edit/i });
    await user.click(editButton);

    // Update the name
    const modal = screen.getByRole('dialog');
    const nameInput = within(modal).getByLabelText(/name/i);
    
    await user.clear(nameInput);
    await user.type(nameInput, 'John Updated');

    // Save changes
    const saveButton = within(modal).getByRole('button', { name: /save/i });
    await user.click(saveButton);

    // Verify success notification
    await waitFor(() => {
      expect(screen.getByText(/client updated successfully/i)).toBeInTheDocument();
    });

    // Verify updated name appears
    await waitFor(() => {
      expect(screen.getByText('John Updated')).toBeInTheDocument();
      expect(screen.queryByText('John Doe')).not.toBeInTheDocument();
    });
  });

  test('deletes a client with confirmation', async () => {
    const user = userEvent.setup();
    global.confirm = jest.fn(() => true);
    
    render(<Clients />);

    // Wait for clients to load
    await waitFor(() => {
      expect(screen.getByText('Jane Smith')).toBeInTheDocument();
    });

    // Find and click delete button for Jane
    const janeRow = screen.getByText('Jane Smith').closest('tr');
    const deleteButton = within(janeRow).getByRole('button', { name: /delete/i });
    await user.click(deleteButton);

    // Verify confirmation was shown
    expect(global.confirm).toHaveBeenCalledWith(expect.stringContaining('Jane Smith'));

    // Verify success notification
    await waitFor(() => {
      expect(screen.getByText(/client deleted successfully/i)).toBeInTheDocument();
    });

    // Verify Jane is removed from list
    await waitFor(() => {
      expect(screen.queryByText('Jane Smith')).not.toBeInTheDocument();
    });

    delete global.confirm;
  });

  test('searches for clients', async () => {
    const user = userEvent.setup();
    render(<Clients />);

    // Wait for clients to load
    await waitFor(() => {
      expect(screen.getByText('John Doe')).toBeInTheDocument();
      expect(screen.getByText('Jane Smith')).toBeInTheDocument();
    });

    // Enter search term
    const searchInput = screen.getByPlaceholderText(/search/i);
    await user.type(searchInput, 'Jane');

    // Verify only Jane is shown
    await waitFor(() => {
      expect(screen.getByText('Jane Smith')).toBeInTheDocument();
      expect(screen.queryByText('John Doe')).not.toBeInTheDocument();
    });

    // Clear search
    await user.clear(searchInput);

    // Verify both clients are shown again
    await waitFor(() => {
      expect(screen.getByText('John Doe')).toBeInTheDocument();
      expect(screen.getByText('Jane Smith')).toBeInTheDocument();
    });
  });

  test('shows validation error for invalid email', async () => {
    const user = userEvent.setup();
    render(<Clients />);

    // Wait for initial load
    await waitFor(() => {
      expect(screen.getByText('John Doe')).toBeInTheDocument();
    });

    // Open new client modal
    const newButton = screen.getByRole('button', { name: /new client/i });
    await user.click(newButton);

    // Enter invalid email
    const modal = screen.getByRole('dialog');
    const nameInput = within(modal).getByLabelText(/name/i);
    const emailInput = within(modal).getByLabelText(/email/i);

    await user.type(nameInput, 'Invalid User');
    await user.type(emailInput, 'notanemail');

    // Try to submit
    const saveButton = within(modal).getByRole('button', { name: /save/i });
    await user.click(saveButton);

    // Verify error message
    await waitFor(() => {
      expect(screen.getByText(/invalid email/i)).toBeInTheDocument();
    });
  });

  test('handles API errors gracefully', async () => {
    const user = userEvent.setup();
    
    // Force an error by making email empty (which the API rejects)
    render(<Clients />);

    await waitFor(() => {
      expect(screen.getByText('John Doe')).toBeInTheDocument();
    });

    const newButton = screen.getByRole('button', { name: /new client/i });
    await user.click(newButton);

    const modal = screen.getByRole('dialog');
    const nameInput = within(modal).getByLabelText(/name/i);
    
    await user.type(nameInput, 'Test User');
    // Don't fill email - will cause API error

    const saveButton = within(modal).getByRole('button', { name: /save/i });
    await user.click(saveButton);

    // Verify error notification
    await waitFor(() => {
      expect(screen.getByText(/operation failed/i)).toBeInTheDocument();
    });
  });
});
