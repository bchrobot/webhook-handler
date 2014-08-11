# About webhook-handler

This is a customizable PHP-based webhook handler for deploying on LA\*P servers using name-based virtual hosts. Custom hook behavior may specified at both the Git action and repository branch levels via YAML files located in each repository.

# Requirements

### LA\*P Stack
This service is written for a Linux stack running Apache2 and PHP. All examples given have been tested on Ubuntu 14.04 and may require some tweaking for other distros.

### Composer
This service uses Composer to manage dependencies. In order to complete the installation you will need to have Composer installed. Please see [Installing Composer](https://getcomposer.org/doc/00-intro.md#installation-nix).


# Installation

### Clone Webhook Handler
Clone files to production server and install dependencies.
* `git clone git@github.com:bchrobot/webhook-handler.git /var/www/deploy.domain.com`
* `cd /var/www/deploy.domain.com; composer install`

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

### Create User to Handle Pull Requests for Apache
A separate user will be created with the sole purpose of performing the `git pull` command on behalf of Apache

* Create the user `sudo adduser webhook-handler`
* Ensure the user has access to `/var/www`
  * __Example.__ If ownership of `/var/www` is `root:www-pub` then `usermod -a -G www-pub webhook-handler`  
    _Note: See [Tom's Guide](http://serverfault.com/questions/6895/whats-the-best-way-of-handling-permissions-for-apache2s-user-www-data-in-var) for a good way to handle ownership and permissions on_ `/var/www`
* Ensure the user has access to the Git repository
  * Generate a __password-less__ ssh key for user `sudo -u webhook-handler ssh-keygen -t rsa`
  * Copy the public key contents `sudo cat /home/webhook-handler/.ssh/id_rsa.pub`
  * Add the public key as a deploy key to each online repository.  
    On Github navigate to __your-repository -> Settings -> Deploy keys -> Add deploy key__
* Give Apache sudo access to run git as webhook-handler
  * Edit sudoers `sudo visudo`
  * Add line for Apache user `www-data        ALL=(webhook-handler) NOPASSWD: /usr/bin/git`
* Pull from deploy repos as webhook-handler user to add Github/Gitlab to `known_hosts`  
  `cd /var/www/domain1.com; sudo -u webhook-handler git pull`  
  `cd /var/www/domain2.com; sudo -u webhook-handler git pull`

Congratulations! The `hooks.php` script should now be able to manage your repositories.

> Reference:  
http://jondavidjohn.com/git-pull-from-a-php-script-not-so-simple/  
http://serverfault.com/questions/362012/running-git-pull-from-a-php-script  
http://stackoverflow.com/questions/22467706/vagrantbutcher-sudo-no-tty-present-and-no-askpass-program-specified-when-tr

### Configure Deploy Repositories

#### General Repository Configuration
Add each repository you want to handle webhooks for to the configuration file.
* Copy `config.php.dist` to `config.php`
* If you are using a secret token, set `$using_secret_token = true;`
* If you are using your own Gitlab install add your Gitlab IP addresses to `$gitlab_ips[]`
* Add each repository you will be handling webhooks for to the array in `config.php`
    * To specify a custom location for the `hook.yml` behavior file, do so by adding a `yml` key to the repository array with value containing `hook.yml` path relative to `dir`  
    ```php
    // Overrides the default location/name of the repository configuration file
    // Absolute path to behavior file will then be /var/www/domain.com/deploy/hooks.yml
    $repositories = [
      'bchrobot/awesome-website' => [
  		  'dir' => '/var/www/domain.com',
  		  'yml' => 'deploy/hooks.yml'
  	  ]
    ]
    ```

#### Specific Repository Behavior
* Copy `examples/hook.yml` into the root of each repository or the location specificied in `config.php`.  
  _Note: for security it is assumed that you are serving web content from a sub-directory such as_ `/var/wwww/domain.com/public_html/` _and not from the repository root itself_
* Customize email notifications and deploy scripts based on the webhook action and the repository branch


# Licensing and Contributors

This project is licensed under the MIT License

Inspiration was drawn from @florianbeer and https://github.com/florianbeer/webhooks/
