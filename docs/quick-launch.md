cd /home/ben/Code/wappointment/addons/wappointment-plugin

# Build the admin and booking-widget assets
npm run build

# Start WordPress and its disposable database
docker compose -p wappointment-booking-tests \
  -f tests/docker/booking.compose.yml up -d --build --wait

# Install WordPress
docker compose -p wappointment-booking-tests \
  -f tests/docker/booking.compose.yml exec wordpress \
  wp core install --url=http://127.0.0.1:18089 --title='Booking tests' \
  --admin_user=booking-admin --admin_password=disposable-booking-test \
  --admin_email=admin@example.invalid --skip-email --allow-root

# Activate the plugin
docker compose -p wappointment-booking-tests \
  -f tests/docker/booking.compose.yml exec wordpress \
  wp plugin activate wappointment --allow-root



  # launch tests
  docker compose -p wappointment-booking-tests \
  -f tests/docker/booking.compose.yml exec \
  -e WAPPOINTMENT_WP_ROOT=/var/www/html wordpress \
  php /var/www/html/wp-content/plugins/wappointment/vendor/bin/pest \
  --do-not-cache-result --cache-directory=/tmp/wappo-pest \
  --configuration=/var/www/html/wp-content/plugins/wappointment/phpunit.xml \
  /var/www/html/wp-content/plugins/wappointment/tests/Integration/BookingTest.php

  # launch jest tests
  npx jest --config jest.widget.config.js --runInBand