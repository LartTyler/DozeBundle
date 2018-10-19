# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure("2") do |config|
	config.vm.box = "ubuntu/bionic64"

	config.vm.provider "virtualbox" do |vb|
		vb.memory = "2048"
	end

	config.vm.provision "shell", inline: <<-SHELL
		apt-get update -y

		add-apt-repository -y ppa:ondrej/php

		apt-get install -y build-essential software-properties-common php7.2 php7.2-zip php7.2-mbstring php7.2-xml
		apt-get install -y composer
	SHELL

	config.vm.provision "shell", privileged: false, inline: <<-SHELL
		echo "[client]" > ~/.my.cnf
		echo "user=application" >> ~/.my.cnf
		echo "database=application" >> ~/.my.cnf

		echo
		echo "Installed packages:"
		echo "  -> PHP 7.2 (with extensions: zip, mbstring, xml)"
		echo "  -> Composer"
	SHELL
end