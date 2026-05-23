FROM wordpress:latest

# Configurar Apache MPM Prefork para bajo consumo de RAM
RUN echo '<IfModule mpm_prefork_module>\n\
    StartServers 2\n\
    MinSpareServers 2\n\
    MaxSpareServers 3\n\
    MaxRequestWorkers 4\n\
    MaxConnectionsPerChild 100\n\
</IfModule>' > /etc/apache2/conf-available/mpm-optimize.conf \
    && a2enconf mpm-optimize

# Activar OPcache para reducir CPU
RUN echo 'opcache.enable=1\n\
opcache.memory_consumption=64\n\
opcache.interned_strings_buffer=8\n\
opcache.max_accelerated_files=4000\n\
opcache.revalidate_freq=60\n\
opcache.fast_shutdown=1' > /usr/local/etc/php/conf.d/opcache.ini
