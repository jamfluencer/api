#!/usr/bin/env bash

cp /home/ec2-user/jamfluencer.env /usr/share/nginx/jamfluencer-api/.env
cd /usr/share/nginx/jamfluencer-api || exit 1
supervisorctl restart reverb queues
su nginx -s /bin/bash -c "php artisan migrate --force"
