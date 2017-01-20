<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class Help implements CommandInterface
{
    public function __invoke(array $arguments)
    {
        // TODO: Move to a file.
        echo <<<HELP
  
\033[1m  JH Workflow Commands\033[22m

\033[32m  start \033[39m
    Runs 3 commands

    - build
    - up
    - watch

\033[32m  start-prod  \033[39m
    Runs 3 commands
    
    - build-prod
    - up-prod
    - watch

\033[32m  build  \033[39m
    Runs docker build to create an image ready for development use

\033[32m  build-prod  \033[39m
    Runs docker build to create an image ready for review/staging/production use
\033[2m    (Not yet geared to full production deployments) \033[22m

\033[32m  up  \033[39m
    Uses docker-compose to start the containers for development

\033[32m  up-prod  \033[39m
    Uses docker-compose to start the containers for review/staging/production use

\033[32m  stop  \033[39m
    Stops the containers running for development

\033[32m  stop-prod  \033[39m
    Stops the containers running for review/staging/production

\033[32m  watch \033[39m
    Keeps track of filesystem changes, piping the changes to the Sync command.

\033[32m  sync \033[39m
    Pushes changes from the filesystem to the relevant docker containers. 
    
    - Nginx will take changes from the pub directory
    - PHP will take changes from all directories except .docker.
    
\033[32m  push \033[39m
    Push files from host to the relevant docker containers. Useful for when the watch isn't running or you watch to 
    push loads of files in quickly

    Usage: composer x push source_file     
\033[2m    Where x is the composer script used in your project and source_file is relative to the app path \033[22m

\033[32m  pull \033[39m
    Pull files from the docker environment to the host, Useful for pulling vendor, composer_cache etc
    
    If the watch is running and you pull a file that is being watched it will automatically be pushed 
    back into the container.
    If this is not what you want (large dirs can cause issues here) stop the watch, pull then start the 
    watch again afterwards.
   
    Usage: composer x pull source_file
\033[2m    Where x is the composer script used in your project and source_file is relative to the app path \033[22m

\033[32m  content-deploy \033[39m
    Runs magento setup:static-content:deploy and accepts passing arguments through
    
    Usage: composer x content-deploy --theme Magento/blank
\033[2m    Where x is the composer script used in your project. \033[22m

\033[32m  cache-flush \033[39m
    Runs magento cache:flush and accepts passing arguments through
    
    Usage: composer x cache-flush config
\033[2m    Where x is the composer script used in your project. \033[22m
    
\033[32m  composer-update \033[39m
    Will run a composer update inside the container and pull back the vendor directory to the host

\033[32m  nginx-reload \033[39m
    Reloads NGINX configuration files for when you've made changes to them

\033[32m  xdebug-loopback \033[39m
    Starts the network loopback to allow Xdebug from Docker

\033[32m  mi \033[39m
    Runs 2 commands as a shortcut on a blank installation
    
    - magento-install
    - magento-configure

\033[32m  magento-isntall \033[39m
    Runs the magento install script with the relevant environment variables found in the .env file
    HTTPS by default.

\033[32m  magento-configure \033[39m
    Adds Redis configuration for sessions, frontend cache and full page cache to the magento env.php file

HELP;
    }
}
