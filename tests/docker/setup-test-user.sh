#!/bin/bash
# Setup test user for API testing

# Wait for WordPress to be ready
sleep 5

# Check if admin user exists, if not create it
if ! wp user get admin --allow-root >/dev/null 2>&1; then
    echo "Creating admin user..."
    wp user create admin admin@example.com --role=administrator --user_pass=password --allow-root
    echo "Admin user created"
else
    echo "Admin user already exists"
    # Update password to ensure it's correct
    wp user update admin --user_pass=password --allow-root
    echo "Admin password updated"
fi

echo "Test user setup complete"
echo "Username: admin"
echo "Password: password"
