#!/bin/bash
killall -9 gpsd;
stty -F /dev/ttyAMA0 raw 9600 cs8 clocal -cstopb;
/usr/sbin/gpsd -N  /dev/ttyAMA0 &
/usr/bin/php /home/pi/CarTraq/CarTraq-GPS.php &
/usr/bin/php /home/pi/CarTraq/CarTraq-Push.php