webhook-handler
===============

PHP-based webhook handler for deploying on Linux/Apache2 servers with name-based hosting.

## Requirements

### mod_rewrite
`sudo a2enmod rewrite`


## Installation

### Clone Webhook Handler
Copy files to production server.
* `git clone git@github.com:bchrobot/webhook-handler.git /var/www/deploy.domain.com`

### Create a VirtualHost for Deploying
Create a distinct VirtualHost with it's own subdomain to handle web hooks.
* Copy `deploy.domain.com.conf` to `/etc/apache2/sites-available`
* Edit the virtual host as necessary updating the domain and directory to your own
* Enable the new VirtualHost `sudo a2ensite deploy.domain.com.conf`
* Reload Apache configuration `sudo service apache2 reload`

### Clone Production-ready Repositories
Perform the initial clones of each repository you will be serving.
* `git clone git@github.com:username/domain1.com.git /var/www/domain1.com`  
  `git clone git@github.com:username/domain2.com.git /var/www/domain2.com`
* Specify and enable VirtualHost configuration files for each project
* Reload Apache configuration `sudo service apache2 reload`
* Make sure your projects are up and running before continuing

### Configure Deploy Repositories
Add each repository you want to handle webhooks for to the configuration file.
* Copy `config.php.dist` to `config.php`
* Add each repository you will be handling webhooks for to the array in `config.php`   
  Note: the actual behavior will be specified within the project directories themselves
* Copy `examples/hook.yml` into the root of each repository and customize your hook behavior! This file specifies the specific behavior associated with each webhook (see below for examples)  
  Note: for security it is assumed that you are serving web content from a sub-directory such as `/var/wwww/domain.com/public_html/` and not from the repository root itself


## Licensing and Contributors

This project is licensed under the MIT License

Inspiration was drawn from @florianbeer and https://github.com/florianbeer/webhooks/
