# cs511-tech-report: 
### A system for the storage of research papers from CSSE
***

### Installation Instructions
Some of these instructions are specific to Ubuntu, but should be similiar in other systems.

#### Install apache2
		sudo apt-get install apache2
		sudo service apache2 restart

		sudo nano /etc/apache2/apache2.conf
		# Add the follow line: 
		ServerName localhost

#### Install PHP5
		sudo apt-get install libapache2-mod-php5
		sudo a2enmod php5
		sudo service apache2 restart

#### Install MySQL
		sudo apt-get install mysql-server php5-mysql

#### Setup email server (system sends email via given gmail account)

		sudo apt-get install ssmtp
		
		sudo nano /etc/ssmtp/ssmtp.conf
		# remove existing mailhub line and add the following lines:
		mailhub=smtp.gmail.com:587
		UseSTARTTLS=YES
		AuthUser=<SYSTEM-EMAIL>@gmail.com
		AuthPass=<SYSTEM-EMAIL-PASSWORD>
		FromLineOverride=YES

		sudo nano /etc/php5/apache2/php.ini
		# replace existing sendmail line with:
		sendmail_path = /usr/sbin/sendmail -t

		sudo service apache2 restart

#### Change directory permissions (necessary if you receive 403-Forbidden or permission denied errors)
		sudo usermod -a -G www-data your-system-username
		sudo chown your-system-username:www-data -R /var/www
		sudo chmod 0775 -R /var/www
		sudo chmod g+s -R /var/www

		sudo service apache2 restart

#### Deploy tech reports project via Git (alternatively use ftp)
		sudo apt-get install git
		cd /var/www/
		sudo rm -r .* # remove files from current directory 
		git clone your-git-repo-url .

#### Input passwords (passwords shouldn't live in version control)
		nano setup-db.sql
		# replace 'your-password-here' to include desired password
			
		nano wp-config.php
		#replace 'your-password-here' to include the same password
Be sure to record passwords in a safe place

#### Setup DB:
		mysql -u root -p < setup-db.sql

#### Visit wordpress install URL, then log in
http://your-server-url/wp-admin/install.php

#### Install Plugin + Theme via UI (http://localhost/wp-admin/plugins.php)
Plugins > Installed Plugins > Technical Report Plugin > Activate

#### Disable comments via UI
Plugins > Installed Plugins > Disable Comments > Settings > Select Everywhere > Save Changes

#### Finished!
