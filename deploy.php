<?php
namespace Deployer;

require 'recipe/symfony.php';

// Config

set('repository', 'https://github.com/jaypanchalnexus/learning_symfony');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('192.168.1.222')
    ->set('remote_user', 'mock_test')
    ->set('deploy_path', '~/learning_symfony');

// Hooks

after('deploy:failed', 'deploy:unlock');
