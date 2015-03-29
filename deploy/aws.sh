#!/bin/bash

function fix_permissions {
    sudo chown -R root:www /var/www
    sudo chmod 2775 /var/www
    find /var/www -type d -exec sudo chmod 2775 {} +
    find /var/www -type f -exec sudo chmod 0664 {} +
}

cd /var/www/html

# remove AWS' default php 5.3, install php 5.4
sudo yum remove httpd* php* -y
sudo yum install httpd24 php54 php54-pdo php54-mysqlnd -y

# install our custom httpd.conf
sudo rm /etc/httpd/conf/httpd.conf
sudo mv deploy/httpd.conf /etc/httpd/conf/httpd.conf

fix_permissions

## install composer
curl -sS https://getcomposer.org/installer | php

# install project dependencies
php composer.phar install

# make project dependencies available to path
export PATH=./vendor/bin:$PATH

# run our install script
sudo php deploy/install.php

fix_permissions # ...again

# also need to give our database permissions (@TODO - 777 is probably a bad idea)
sudo chown -R ec2-user /var/www/html/data/
chmod 777 data
chmod 777 data/production.db

# generate the documentation for our project
sudo yum install 'graphviz*'
phpdoc -d ./webapp/ -t ./webapp/docs/

sudo service httpd stop
sudo service httpd start