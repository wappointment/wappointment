#!/bin/bash

# WordPress Test Database Snapshot Generator
# This script creates a snapshot of WordPress with the plugin activated

set -e

echo "=== Creating WordPress Test Database Snapshot ==="

# Wait for WordPress to be ready
echo "Waiting for WordPress to be ready..."
timeout=60
elapsed=0
until docker compose -f docker-compose.test.yml exec -T wordpress curl -sf http://localhost > /dev/null 2>&1; do
    if [ $elapsed -ge $timeout ]; then
        echo "Error: WordPress failed to start within ${timeout}s"
        exit 1
    fi
    echo -n "."
    sleep 2
    elapsed=$((elapsed + 2))
done
echo " Ready!"

# Install WordPress if not already installed
echo "Checking WordPress installation..."
docker compose -f docker-compose.test.yml exec -T wordpress bash -c "
    cd /var/www/html
    if ! wp core is-installed --allow-root 2>/dev/null; then
        echo 'Installing WordPress...'
        wp core install \
            --url='http://localhost:8080' \
            --title='Wappointment Test' \
            --admin_user='admin' \
            --admin_password='admin' \
            --admin_email='admin@test.local' \
            --skip-email \
            --allow-root
        echo '✓ WordPress installed'
    else
        echo '✓ WordPress already installed'
    fi
"

# Activate plugin
echo "Activating Wappointment plugin..."
docker compose -f docker-compose.test.yml exec -T wordpress bash -c "
    cd /var/www/html
    wp plugin activate wappointment --allow-root
"
echo "✓ Plugin activated"

# Create snapshot
echo "Creating database snapshot..."
docker compose -f docker-compose.test.yml exec -T mysql mysqldump \
    -u wordpress \
    -pwordpress \
    wordpress_test > tests/docker/db-snapshot.sql

echo ""
echo "✓✓✓ Database snapshot created at tests/docker/db-snapshot.sql"
echo ""
echo "You can now run tests with: npm run test:api"
