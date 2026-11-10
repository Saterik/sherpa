#!/bin/bash
pkill -f "php -S 0.0.0.0:8081"
cd /mnt/c/Users/warfa/Desktop/sherpa/backend
php -r "opcache_reset();"
php -S 0.0.0.0:8081 -t public > /tmp/backend.log 2>&1 &
sleep 3
curl -s -X OPTIONS http://localhost:8081/api/auth/register -H "Origin: http://localhost:4200" -H "Access-Control-Request-Method: POST" -H "Access-Control-Request-Headers: Content-Type" -w "\n%{http_code}"