############################################################
# Dockerfile to build MultiFaucet container images
# Based on Centos
############################################################

# Set the base image to Centos
FROM centos
MAINTAINER Daniel Morante
RUN yum update -y
RUN yum install git -y
RUN yum install httpd mysql-server php php-mysqli -y
RUN cd /var/www
RUN git clone https://github.com/tuaris/multifaucet.git
