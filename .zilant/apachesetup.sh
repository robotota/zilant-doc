#!/bin/bash

cp /etc/apache2/sites-available/default /tmp/default;

sed s/NAME/$1/g /tmp/default > /etc/apache2/sites-available/default;

service apache2 restart;

rm /tmp/default;
