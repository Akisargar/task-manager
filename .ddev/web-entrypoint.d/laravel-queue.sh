#!/bin/bash
if [ ! -f /etc/supervisor/conf.d/laravel-queue.conf ]; then
    cat <<EOF > /etc/supervisor/conf.d/laravel-queue.conf
[program:laravel-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --sleep=2 --tries=3
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/tmp/laravel-queue.log
stopwaitsecs=3600
EOF
    supervisorctl reread
    supervisorctl update
fi
