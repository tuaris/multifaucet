############################################################
# Dockerfile to build MultiFaucet container images
# Based on Centos 7
############################################################

# Set the base image to Centos
FROM centos
MAINTAINER Daniel Morante

# Udate and Install Required RPM Packages
RUN yum update -y
RUN yum install git -y
RUN yum install httpd -y
RUN yum install mariadb-server mariadb -y
RUN yum install php php-mysqli -y

# Enable and Run Apache
RUN systemctl enable httpd.service
RUN systemctl start httpd.service

# Enable and Run MySQL/MariaDB
RUN systemctl enable mariadb.service
RUN systemctl start mariadb.service

# Download MultiFaucet
RUN git clone https://github.com/tuaris/multifaucet.git /var/www/html/
RUN rm -f /var/www/html/Dockerfile

# Genorate random password
RUN MULTIFAUCET_DB_PASS=`date | md5sum | head -c 8`
RUN MULTIFAUCET_WALLET_PASS=`date | md5sum | head -c 8`

# Create MySQL/MariaDB Database and User
RUN mysql -e "CREATE DATABASE multifaucet;"
RUN mysql -e "CREATE USER 'multifaucet'@'localhost' IDENTIFIED BY '${MULTIFAUCET_DB_PASS}';"
RUN mysql -e "GRANT ALL ON multifaucet.* TO 'multifaucet'@'localhost';"

# Pre-Configure the database settings for MultiFaucet
RUN echo "<?php" >> /var/www/html/config/db.conf.php
RUN echo "define(\"DB_HOST\", \"localhost\");" >> /var/www/html/config/db.conf.php
RUN echo "define(\"DB_NAME\", \"multifaucet\");" >> /var/www/html/config/db.conf.php
RUN echo "define(\"DB_USER\", \"multifaucet\");" >> /var/www/html/config/db.conf.php
RUN echo "define(\"DB_PASS\", \"${MULTIFAUCET_DB_PASS}\");" >> /var/www/html/config/db.conf.php
RUN echo "define(\"TB_PRFX\", \"faucet_\");" >> /var/www/html/config/db.conf.php
RUN echo "?>" >> /var/www/html/config/db.conf.php

# Pre-Configure a cold wallet storage file
RUN mkdir -p /var/db/multifaucet/
RUN echo "<?php" >> /var/www/html/config/wallet.conf.php
RUN echo "define(\"PAYMENT_GW_RPC_USER\", \"admin\");" >> /var/www/html/config/wallet.conf.php
RUN echo "define(\"PAYMENT_GW_RPC_PASS\", \"${MULTIFAUCET_WALLET_PASS}\");" >> /var/www/html/config/wallet.conf.php
RUN echo "define(\"PAYMENT_GW_DATAFILE\", \"/var/db/multifaucet/balance.dat\");" >> /var/www/html/config/wallet.conf.php
RUN echo "define(\"ADDRESS_VERSION\", \"0\");" >> /var/www/html/config/wallet.conf.php
RUN echo "?>" >> /var/www/html/config/wallet.conf.php

# Make configuration writable so the user can continue withe the web installer
RUN chown -R apache:apache /var/www/html/config/
RUN chmod -R 700 /var/www/html/config/
RUN chcon -Rt httpd_sys_content_rw_t /var/www/html/config/

# Make cold wallet directory writable so the user can continue withe the web installer
RUN chown -R apache:apache /var/db/multifaucet/
RUN chmod -R 700 /var/db/multifaucet/
RUN chcon -Rt httpd_sys_content_rw_t /var/db/multifaucet/

#Open port 80 on Firewall
RUN firewall-cmd --permanent --add-port=80/tcp

##################### INSTALLATION END #####################
EXPOSE 80

RUN echo "To complete the installation go to http://`ip addr show | grep -E '^\s*inet' | grep -m1 global | awk '{ print $2 }' | sed 's|/.*||'`/install.php"