exec { '/usr/bin/apt-get update':
  timeout => 0
}

class { 'midgardmvc':
}

file { ['/opt/midgardmvc/blobs', '/opt/midgardmvc/logs', '/opt/midgardmvc/cache']:
  ensure => 'directory',
  owner => 'vagrant'
}

exec { 'composer_install':
  command => '/usr/local/bin/composer install',
  timeout => 0,
  cwd => '/opt/midgardmvc',
  environment => ['COMPOSER_PROCESS_TIMEOUT=4000'],
  require => [
    Exec['download_composer'],
    Package['php5-cli', 'php5-midgard2']
  ]
}

exec { 'setup_locale':
  command => '/usr/sbin/locale-gen fi_FI.utf8'
}

exec { 'install_mgdschemas':
  command => '/opt/midgardmvc/vendor/bin/midgardmvc_install_mgdschemas',
  require => [
    Exec['composer_install', 'setup_locale'],
    File['/etc/php5/conf.d/midgardmvc.ini', 'midgard_config']
  ]
}

exec { 'create_database_tables':
  command => '/opt/midgardmvc/vendor/bin/midgardmvc_create_database',
  require => [
    Exec['create_database', 'install_mgdschemas']
  ]
}

exec { 'create_mvc_nodes':
  command => '/opt/midgardmvc/vendor/bin/midgardmvc_setup_hierarchy',
  require => [
    Exec['create_database_tables']
  ]
}

file { 'aip_config':
  path => '/opt/midgardmvc/midgardmvc_aip.yml',
  source => 'puppet:///modules/midgardmvc/aip.yml',
  require => Exec['composer_install']
}

# Set up upstart
file { '/etc/init/midgardmvc.conf':
  source => 'puppet:///modules/midgardmvc/midgardmvc.conf',
  require => File['aip_config']
}

# See http://projects.puppetlabs.com/issues/14297
file { '/etc/init.d/midgardmvc':
  ensure => link,
  target => '/lib/init/upstart-job'
}

# Start the service
service { 'midgardmvc':
  ensure => running,
  provider => 'upstart',
  require => [
    File['/etc/init/midgardmvc.conf', '/etc/init.d/midgardmvc']
  ]
}
