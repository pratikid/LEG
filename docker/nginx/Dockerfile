FROM nginx:mainline-alpine

# Install build dependencies
RUN apk add --no-cache \
    git \
    build-base \
    linux-headers \
    pcre-dev \
    zlib-dev \
    openssl-dev \
    curl \
    gnupg \
    libxslt-dev \
    gd-dev \
    geoip-dev \
    certbot \
    certbot-nginx \
    openssl

# Create SSL directory
RUN mkdir -p /etc/nginx/ssl/live/localhost

# Copy Nginx configuration
COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

# Generate self-signed certificate for development
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/nginx/ssl/live/localhost/privkey.pem \
    -out /etc/nginx/ssl/live/localhost/fullchain.pem \
    -subj "/C=US/ST=State/L=City/O=Organization/CN=localhost"

# Set proper permissions
RUN chown -R nginx:nginx /var/cache/nginx /var/log/nginx /etc/nginx/ssl

# Copy fix-permissions.sh script
COPY ../../docker/fix-permissions.sh /usr/local/bin/fix-permissions.sh

# Set permissions for fix-permissions.sh script
RUN chmod 755 /usr/local/bin/fix-permissions.sh

# Expose ports for Nginx
EXPOSE 80 443

# Set entrypoint to fix permissions
ENTRYPOINT ["/usr/local/bin/fix-permissions.sh"]

# Start Nginx
CMD ["nginx", "-g", "daemon off;"]
