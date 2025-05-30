FROM nginx:alpine

COPY ../../docker/fix-permissions.sh /usr/local/bin/fix-permissions.sh
RUN chmod 755 /usr/local/bin/fix-permissions.sh

ENTRYPOINT ["/usr/local/bin/fix-permissions.sh"]
CMD ["nginx", "-g", "daemon off;"]
