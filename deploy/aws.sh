#!/bin/bash

cd /var/www/html

# remove AWS' default php 5.3, install php 5.5
sudo yum remove php*
sudo yum install php55 php55-pdo php55-mysqlnd

## install composer
curl -sS https://getcomposer.org/installer | php

# install project dependencies
php composer.phar install

# run our install script
sudo php deploy/install.php

# fix permissions
sudo chown -R root:www /var/www
sudo chmod 2775 /var/www
find /var/www -type d -exec sudo chmod 2775 {} +
find /var/www -type f -exec sudo chmod 0664 {} +

# also need to give our database permissions (@TODO - 777 is probably a bad idea)
sudo chown -R ec2-user /var/www/html/data/
chmod 777 data
chmod 777 data/production.db

# generate the documentation for our project
sudo yum install 'graphviz*'
phpdoc -d ./webapp/ -t ./webapp/docs/
