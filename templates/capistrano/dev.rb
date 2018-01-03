set :stage,         :development
set :keep_releases, 1
set :deploy_to,     '/microcloud/domains/dev2jh/domains/dev.wearejh.info/___{project-namespace}'
set :branch,        'develop'

role :web, %w{www-data@dh1.c309.sonassihosting.com:3022}