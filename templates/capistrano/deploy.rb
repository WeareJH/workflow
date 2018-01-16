set :application,       '{project-name}'
set :keep_releases,     3

#git details
set :scm, :git
set :repo_url,      '{repository}'
set :deploy_via,    :remote_cache
set :branch,        'master'
set :domain,        ->{ fetch(:app_domain) }

set :linked_files, %w{app/etc/config.php app/etc/env.php}
set :linked_dirs, %w{pub/info pub/media pub/sitemap var/log var/report var/import var/export var/session pub/feeds}

set :composer_install_flags, '--ignore-platform-reqs --no-interaction --quiet --optimize-autoloader'
set :log_level, :info
# set :pty, true

def relative_path(from_str, to_str)
    require 'pathname'
    Pathname.new(to_str).relative_path_from(Pathname.new(from_str)).to_s
end

Rake::Task["deploy:symlink:linked_dirs"].clear
Rake::Task["deploy:symlink:linked_files"].clear

namespace :deploy do
    namespace :symlink do
    desc 'Symlink release to current (relative)'
    task :release do
      puts 'Symlink release to current (relative)'.upcase
      rel_release_path = relative_path(deploy_to, release_path)
      on release_roles :all do
        execute :rm, '-rf', current_path
        execute :ln, '-s', rel_release_path, current_path
      end
    end

    desc 'Symlink relatively linked directories'
    task :linked_dirs do
      puts 'Symlink relatively linked directories'.upcase
      next unless any? :linked_dirs
      on release_roles :all do
        execute :mkdir, '-pv', linked_dir_parents(release_path)

        fetch(:linked_dirs).each do |dir|
          target = release_path.join(dir)
          source = shared_path.join(dir)
          rel_source = relative_path(deploy_to, source)
          rel_target = relative_path(deploy_to, target)
          unless test "[ -L #{target} ]"
            if test "[ -d #{target} ]"
              execute :rm, '-rf', target
            end
            execute :ln, '-s', '../../../' + rel_source, target
          end
        end
      end
    end

    desc 'Symlink relatively linked files'
    task :linked_files do
      puts 'Symlink relatively linked files'.upcase
      next unless any? :linked_files
      on release_roles :all do
        execute :mkdir, '-pv', linked_file_dirs(release_path)

        fetch(:linked_files).each do |file|
          target = release_path.join(file)
          source = shared_path.join(file)
          rel_source = relative_path(deploy_to, source)
          rel_target = relative_path(deploy_to, target)
          unless test "[ -L #{target} ]"
            if test "[ -f #{target} ]"
              execute :rm, '-f', target
            end
            execute :ln, '-s', '../../../../' + rel_source, target
          end
        end
      end
    end
  end

  task :magento2_setup do
      on roles(:web) do |host|
        within release_path do
          execute :chmod,"u+x #{release_path}/bin/magento"
          execute :chown, "-R www-data:www-data #{release_path}/var"
          execute "php-7.0 #{release_path}/bin/magento setup:upgrade"
          execute "HTTPS='on' php-7.0 #{release_path}/bin/magento deploy:mode:set production"
          execute :chmod, "-R 755 #{release_path}/pub/static"
          execute :chmod, "-R 755 #{release_path}/var"
        end
      end
    end

  desc 'Clear PHP op-cache'
    task :clear_opcache do
      if fetch(:domain).nil?
        puts 'Skipped opcache clear beacause :app_domain was not set'
        next
      end
      on roles(:web) do |host|
        within release_path do
          execute "echo \"<?php if (function_exists('opcache_reset')) { opcache_reset(); echo 'OPcache cleared'; } else { echo 'OPcache not enabled on server'; }\" > #{release_path}/pub/opcache.php"
          execute "wget -qO- #{fetch(:app_domain)}/opcache.php || echo \"Failed OPcache clear\"; true"
          execute "rm #{release_path}/pub/opcache.php"
        end
      end
    end

  before 'deploy:updated', 'deploy:magento2_setup'
  after :finishing, 'deploy:clear_opcache'
  after :finishing, 'deploy:cleanup'
end