#!/bin/bash
#
# Build script for a LAMP EC2 server from scratch, using
# the Elastic Block Storage for persistent storage.
#
# Designed for Ubuntu Intrepid ami-80c0e8f4.
#
# EC2 Ubuntu:
# <https://help.ubuntu.com/community/EC2StartersGuide>
# Eric Hammond on EC2 MySQL:
# <http://ec2ebs-mysql.notlong.com/>

# ------ process input options

# usage instructions and help

usageHelp="Usage: ${0##*/}"
pwdHelp='-p mysql root password to setup once mysql is installed (compulsory)'
emailHelp='-e email address to use for apach server admin setup (compulsory)'
domainHelp='-d domains to use, if multiple separate by spaces'
badOptionHelp='Option not recognised.'

printHelpAndExit()
{
	echo "$usageHelp"
	echo "$pwdHelp"
	echo "$emailHelp"
    echo "$domainHelp"
	exit $1
}

printErrorHelpAndExit()
{
        echo
        echo "$@"
        echo
        echo
        printHelpAndExit 1
}

# get actual argument values

mysql_pwd=''
server_email=''
domains=''
while getopts "hp:e:d:" optionName; do
	case "$optionName" in
		h) printHelpAndExit 0;;
		e) server_email="$OPTARG";;
		p) mysql_pwd="$OPTARG";;
        d) domains="$OPTARG";;
		[?]) printErrorHelpAndExit "$badOptionHelp";;
	esac
done

# check compulsory vars

if [ -z "$mysql_pwd" ]
then
    printErrorHelpAndExit "$pwdHelp"
fi

if [ -z "$server_email" ]
then
    printErrorHelpAndExit "$emailHelp"
fi

# ------- update meta-data and any installed packages
echo "Updating apt meta data and already installed packages..."

sudo apt-get -y update
sudo apt-get -y upgrade

# ------- mount and format XFS EBS vol to /mnt/vol
#         (assumes vol attached on /dev/sdh)
echo "Formatting (xfs) and mounting EBS volume..."

sudo apt-get -y install xfsprogs  # xfs tools
sudo modprobe xfs
sudo mkfs.xfs /dev/sdh
echo "/dev/sdh /mnt/vol xfs noatime 0 0" | sudo tee -a /etc/fstab
sudo mkdir /mnt/vol
sudo mount /mnt/vol

# create standard default directories
sudo mkdir /mnt/vol/etc /mnt/vol/log

# ------- install mysql-server
echo "Installing mysql server..."

# install, skipping prompts for root password
export DEBIAN_FRONTEND=noninteractive
    # (skips specification of root password)
sudo -E apt-get -y install mysql-server

# stop mysql server
sudo /etc/init.d/mysql stop
sudo killall mysqld_safe

# move mysql files onto mounted volume. NOte that for compatibility
# with AppArmour we bind the original locations back across to the
# new locations.
sudo mv /etc/mysql     /mnt/vol/etc/
sudo mv /var/lib/mysql /mnt/vol/
sudo mv /var/log/mysql /mnt/vol/log/

sudo mkdir /etc/mysql
sudo mkdir /var/lib/mysql
sudo mkdir /var/log/mysql

echo "/mnt/vol/etc/mysql /etc/mysql     none bind" | sudo tee -a /etc/fstab
sudo mount /etc/mysql

echo "/mnt/vol/mysql /var/lib/mysql none bind" | sudo tee -a /etc/fstab
sudo mount /var/lib/mysql

echo "/mnt/vol/log/mysql /var/log/mysql none bind" | sudo tee -a /etc/fstab
sudo mount /var/log/mysql

# setup basic configuration
echo '[mysqld]
innodb_file_per_table
max_binlog_size = 1000M' | sudo tee -a /mnt/vol/etc/mysql/conf.d/ec2.cnf

# restart mysql server
sudo /etc/init.d/mysql start

# set mysql server root password
mysqladmin -u root password "$mysql_pwd"

# ------- install mysql-server
echo "Installing Apache..."

sudo apt-get -y install apache2
sudo a2enmod rewrite

# setup http.conf basics

sudo mkdir /mnt/vol/etc/apache2
echo "
ServerAdmin $server_email
<Directory />
  Options -Indexes
  AllowOverride All
</Directory>
# deny access to any svn files
<Directory ~ "\.svn">
  Order allow,deny
  Deny from all
</Directory>
DirectoryIndex index.htm index.html index.php
DefaultType application/octet-stream
ServerSignature Off
Include /mnt/vol/etc/apache2/" | sudo tee -a /etc/apache2/httpd.conf

# remove any default setup
sudo rm -f /etc/apache2/sites-enabled/*
sudo rm -f /etc/apache2/sites-available/*

# setup 'www' directory for sites, and add placeholders and virtual hosts
sudo mkdir /mnt/vol/www /mnt/vol/log/apache2
if [ -n "$domains" ]
then
    for host in $domains
    do
        echo "Adding host $host..."

        # add entry in www dir
        sudo mkdir "/mnt/vol/www/$host"

        # create default landing page
        echo "<html>
<head>
<title>Default page for $host</title>
</head>
<body>
<p>Default page for host $host</p>
</body>
</html>" | sudo tee -a /mnt/vol/www/$host/index.htm

        # add setup to virtual hosts file
        echo "# $host

<VirtualHost *:80>
  DocumentRoot /mnt/vol/www/$host/
  ServerName $host
  CustomLog /var/log/apache2/$host.log combined
</VirtualHost>

"  | sudo tee -a /mnt/vol/etc/apache2/hosts.conf

    done
fi
sudo chown -R www-data:www-data /mnt/vol/www

# restart apache
sudo /etc/init.d/apache2 restart

# ------- install PHP5
echo "Installing PHP5..."

# install software

sudo apt-get -y install php5 libapache2-mod-php5 php5-cli
sudo apt-get -y install php5-mcrypt php-soap php5-gd php5-mysql php5-memcache php5-imap php5-sqlite php5-curl

# Update php.ini config

sudo mkdir /mnt/vol/etc/php5
echo '#
# PHP.ini configuration for EC2.
#

# Note that this file is stored in /mnt/vol/etc/php5/ec2.ini and SYMLINKED into
# the usual PHP additional .ini directory. If you want to add another additional
# PHP5 ini file you need to makes sure the new file is also symlinked into the
# standard PHP include directory (/etc/php5/apache2/conf.d/).

short_open_tag = Off
allow_call_time_pass_reference = Off
max_execution_time = 30
max_input_time = 120
max_input_nesting_level = 64
memory_limit = 32M
error_reporting = E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR
display_errors = Off
display_startup_errors = Off
log_errors = On
ignore_repeated_errors = Off
ignore_repeated_source = Off
html_errors = Off
error_log = syslog
register_globals = Off
post_max_size = 8M
magic_quotes_gpc = Off
magic_quotes_runtime = Off
default_mimetype = "text/html"
default_charset = "utf-8"
file_uploads = On
upload_max_filesize = 5M
allow_url_fopen = On
allow_url_include = Off
default_socket_timeout = 10
auto_detect_line_endings = On
define_syslog_variables  = Off
[Session]
session.use_cookies = 1
session.use_only_cookies = 1
session.name = sid
session.cookie_httponly = 1
[mbstring]
mbstring.language = Neutral
mbstring.internal_encoding = UTF-8
mbstring.http_input = pass
mbstring.http_output = pass
mbstring.encoding_translation = Off
mbstring.detect_order = auto
mbstring.substitute_character = none;
mbstring.func_overload = 0' | sudo tee -a  /mnt/vol/etc/php5/ec2.ini
# create symlink from main config directory
sudo ln -s /mnt/vol/etc/php5/ec2.ini /etc/php5/apache2/conf.d/ec2.ini

# restart apache
sudo /etc/init.d/apache2 restart

# ------- Install APC PHP opt-code cache
echo "Installing APC opt-code cache..."

# compile and install
sudo apt-get -y install php-pear php5-dev apache2-prefork-dev build-essential
echo "
" | sudo pecl install apc # this requires a prompt, single return piped in
sudo apt-get -y remove php5-dev apache2-prefork-dev

# configure
echo 'extension=apc.so
apc.apc.stat = 0
apc.include_once_override = 1
apc.shm_size = 64' | sudo tee -a  /etc/php5/conf.d/apc.ini

#restart apache
sudo /etc/init.d/apache2 restart

# ------- CRON server maintainence
echo "Installing Server maintenance cron jobs..."

# time syncing
echo '#
# Update server time CRON job (every hour)
#
01 * * * * root ntpdate ntp.ubuntu.com pool.ntp.org' | sudo tee -a  /etc/cron.d/clock

# ------- Tidy up
echo "Tidying up..."

# clean up apt-get
sudo apt-get -y autoremove --purge
sudo apt-get -y autoclean

# ensure all permissions set appropriately
sudo chown -R www-data:www-data /mnt/vol/www /mnt/vol/log/apache2
sudo chown -R mysql:mysql /mnt/vol/mysql /mnt/vol/log/mysql

echo "Completed OK! It is advised that you now reboot the server.

Your EBS volume on /dev/sdh has been mounted to /mnt/vol/ with the directory
structure:

/mnt/vol/             EBS volume root
         etc/           configuration dir
            apache2/      apache config (hosts.conf for virtual hosts)
            php5/         php5 (ec2.ini for php.ini directives)
         log/           log dir
            apache2/      apache logs
            mysql/        mysql logs
         mysql/         mysql data dir
         www/           site HTML/PHP files

"
