# Amazon Web Services
This readme provides instructions on how to deploy SmartResolution to AWS.

## @TODO
The reason I have this README is that I hope to automate AWS deployment so that I can easily push the latest version of my project to AWS. I'm certainly hoping to automate the 'Install all dependencies' section to make AWS deployment more painless.

## Create your AWS instance

* create an AWS account
* follow these instructions:
    - [step 1](http://docs.aws.amazon.com/gettingstarted/latest/wah-linux/getting-started-application-server.html): creating a new instance.
    - skip step 2 "Create a database server"
    - [step 3](http://docs.aws.amazon.com/gettingstarted/latest/wah-linux/getting-started-deploy-app.html): for instructions on how to SSH into the server. When you reach "Start the web server", stop reading, and come back to these instructions.

Make a note of two things in particular:

* your instance's IP address
* your instance's private DNS

You should now SSH into your instance if you haven't already:

`ssh -i path/to/key.pem ec2-user@your_ec2_private_dns.amazonaws.com`

### Edit your httpd config

Edit the httpd config:
`sudo vi /etc/httpd/conf/httpd.conf`

* Change DocumentRoot for `/var/www/html/webapp` rather than just `/var/www/html`.
* Change AllowOverride to All instead of None (below the DocumentRoot bit above, below "further relax...".

### Install all dependencies

NB: your application will live in this folder: `/var/www/html`

```bash
# remove AWS' default php 5.3, install php 5.5
sudo yum remove php*
sudo yum install php55 php55-pdo php55-mysqlnd

# sanity check - should see PDO in the list of installed modules
php -m

## install composer
curl -sS https://getcomposer.org/installer | php

# get latest version of major project
cd /var/www
wget https://github.com/ChrisBAshton/major-project/archive/master.zip
unzip master.zip
rm master.zip

# replace html on site
sudo rm -r html
mv major-project-master/ html

# install project dependencies
php composer.phar install

# run our install script
sudo php install.php

# fix permissions
sudo chown -R root:www /var/www
sudo chmod 2775 /var/www
find /var/www -type d -exec sudo chmod 2775 {} +
find /var/www -type f -exec sudo chmod 0664 {} +

# also need to give our database permissions (@TODO - 777 is probably a bad idea)
sudo chown -R ec2-user /var/www/html/data/
chmod 777 data
chmod 777 data/production.db

# start the httpd server
sudo service httpd start
```

You should be able to go to the provided IP address and see SmartResolution working.

## Just in case

You should now backup your configuration, as you don't want to go through all that again.

To create an AMI from a running Amazon EBS-backed instance

From [step 3](http://docs.aws.amazon.com/gettingstarted/latest/wah-linux/getting-started-deploy-app.html):

* Open the Amazon EC2 console.
* In the navigation pane, click Instances.
* On the Instances page, select your instance, click Actions, select Image, and then click Create Image.
* In the Create Image dialog box, specify a unique image name and an optional description of the image (up to 255 characters), and then click Create Image. Click Close.

Your instance will be temporarily unavailable while the snapshot is being taken. But you can now quickly and easily launch new instances with the same setup!

To launch a new instance:

* New instance
* My AMIs
    - choose one of my Snapshots
* Auto-Assign public IP - enable
* Review and launch
* Edit security groups
    - choose smartresolution existing one
* Edit tags if necessary
* Launch
    - choose existing key value pair set

You may need to SSH into the instance and start the server.

## Connect domain name to your instance

Follow these instructions: http://reff.it/l8f3

Essentially, you'll need to create an Elastic IP, Associate it with your Instance, then add an 'A' record to your Hosted Zone pointing to the Elastic IP.
