#!/bin/bash
if [[ $EUID -ne 0 ]]
then
	echo "You hav to be root to install!";
else
	chmod uga+x codepagePara.sh 
	cp codepagePara.sh /usr/bin/codepagePara.sh;
	chmod uga+x convertHtm.sh 
	cp convertHtm.sh /usr/bin/convertHtm.sh;
	cp tidy_libri.conf /etc/tidy_libri.conf;
fi
