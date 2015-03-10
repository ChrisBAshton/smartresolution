# Amazon Web Services
This readme provides instructions on how to deploy SmartResolution to AWS.

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

### Install the project

These instructions should work for both new instances and updating existing instances to the latest version of the project. WARNING: these instructions will *replace* the production database, so you should always back up your database first if you're doing this on a live site.

Copy and paste the following commands into your shell:

```bash
cd /var/www

# remove old version of SmartResolution if we've done this before
sudo rm -rf html

# get latest version of SmartResolution
wget https://github.com/ChrisBAshton/major-project/archive/master.zip
unzip master.zip
rm master.zip
mv major-project-master/ html

# move into the repo, ready to run some scripts
cd html
```

You should now be able to run the one-script install:

`sudo ./deploy/aws.sh`

Hit `y` and `Return` wherever prompted for permission.

## Start the httpd server
`sudo service httpd start`

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
