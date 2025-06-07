#!/bin/sh

# Create directory for SSL certificates
mkdir -p ssl/live/localhost

# Generate self-signed certificate
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout ssl/live/localhost/privkey.pem \
    -out ssl/live/localhost/fullchain.pem \
    -subj "/C=US/ST=State/L=City/O=Organization/CN=localhost"

# Set proper permissions
chmod -R 755 ssl
chmod -R 600 ssl/live/localhost/*.pem

echo "SSL certificates generated successfully!" 