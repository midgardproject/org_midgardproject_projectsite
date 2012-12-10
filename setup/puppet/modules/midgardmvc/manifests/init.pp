class midgardmvc {
  $home_path = '/home/vagrant'
  $bin_dir = '/usr/local/bin'
  $project_path = '/opt/midgardmvc'

  $dbuser = 'midgard'
  $dbpass = 'midgard'
  $dbname = 'midgard'

  # The PHP and Apache environment with Midgard2
  package { ['php5-cli', 'php5-midgard2', 'libgda-5.0-mysql', 'php5-memcache', 'php5-gd', 'php-apc', 'fonts-freefont-ttf']:
      ensure => latest;
  }

  # SSH should go straight to project root
  file { "${home_path}/.bash_aliases":
      content => "cd ${project_path}";
  }

  # We need these in order to run Composer
  package { ['git-core', 'curl']:
      ensure => latest;
  }

  exec { 'download_composer':
    command => '/usr/bin/curl -s http://getcomposer.org/installer | /usr/bin/php',
    cwd => $home_path,
    require => [
      Package['curl', 'php5-cli'],
    ],
    creates => "${home_path}/composer.phar";
  }

  file { $bin_dir:
    ensure => directory;
  }

  file { "${bin_dir}/composer":
    ensure => present,
    source => "${home_path}/composer.phar",
    require => [
      Exec['download_composer'],
      File[$bin_dir],
    ],
    mode => '0755'
  }

  # Set up PHP timezone
  file { '/etc/php5/conf.d/01-settings.ini':
    source => 'puppet:///modules/midgardmvc/01-settings.ini',
    require => Package['php5-cli'],
  }

  # MySQL setup
  package { ['mysql-server', 'mysql-client']:
    ensure => latest
  }

  service { 'mysql':
    enable => true,
    ensure => running,
    require => Package['mysql-server']
  }

  # Some extra stuff needed by our PHP code
  package { ['imagemagick', 'rcs', 'memcached']:
    ensure => latest
  }

  exec { 'create_database':
    unless => "/usr/bin/mysql -u${dbuser} -p${dbpass} ${dbname}",
    command => "/usr/bin/mysql -uroot -p -e \"create database ${dbname}; grant all on ${dbname}.* to ${dbuser}@localhost identified by '${dbpass}';\"",
    require => Service['mysql']
  }

  # Set up Midgard2 config
  file { 'midgard_config':
    path => "/etc/midgard2/conf.d/midgard2.conf",
    source => 'puppet:///modules/midgardmvc/midgard2.conf',
    require => Package['php5-midgard2']
  }

  # Set up Midgard2 PHP config
  file { '/etc/php5/conf.d/midgardmvc.ini':
    source => 'puppet:///modules/midgardmvc/midgardmvc.ini',
    require => Package['php5-midgard2']
  }

  # Remove unnecessary example config
  tidy { '/etc/midgard2/conf.d/midgard.conf.example':
    require => Package['php5-midgard2']
  }
}
