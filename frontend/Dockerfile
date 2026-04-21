FROM httpd:latest

RUN apt update && apt upgrade -y

WORKDIR /usr/local/apache2/htdocs

RUN rm index.html

# Exposer le port 80 pour Apache
EXPOSE 80