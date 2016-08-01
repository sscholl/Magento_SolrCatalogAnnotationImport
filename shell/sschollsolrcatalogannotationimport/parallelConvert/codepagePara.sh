#!/bin/bash
CONVERTHTM='/usr/bin/convertHtm.sh';
CHARDET='/usr/bin/chardet';

iconv='/usr/bin/iconv'
tidy='/usr/bin/tidy'
tidyconf='/etc/tidy_libri.conf'
xml='/usr/bin/xmlstarlet'

convertparallel() {
	echo "HOME: $HOME"
	echo "HOME: $SHELL"
	echo "determining codepages ..."
	ls *.[Hh][Tt][Mm] | parallel -m "$CHARDET {}" >> filePageMap.tmp

	echo 'converting files ...'
	cat filePageMap.tmp  | sed 's/^\([^:]*\): \([^ ]*\) [^$]*$/\1 \2/' | parallel -u "$CONVERTHTM"
	rm filePageMap.tmp

	echo 'converting to UTF-8 ...'
	ls *.tmp | parallel -u ${iconv} -c -f LATIN1 -t UTF-8//IGNORE -o {.}.tmpUtf {}

	echo 'tidy files ...'
	ls *.[Hh][Tt][Mm].tmpUtf | parallel -m ${tidy} -config ${tidyconf} {} 2> tidy.err

	echo 'move files ...'
	ls *.[Hh][Tt][Mm].tmpUtf | parallel -u "mv {} {.}"

	echo 'extract p-tags to *.txt' ...
	ls *.[Hh][Tt][Mm] | parallel "${xml} sel -t -v '/html/body/p' {} > {.}.txt" 2>xmlstarlet.err

	echo 'removing temporary-files ...'
	ls *.[Hht][Ttm][Mmp] | parallel -u -m "rm {}"
	#ls *.txtasc | parallel -u -m "rm {}"
}

setVars() {
	if [ ${SHELL}x == x ]; then
		echo 'setting $SHELL to /bin/bash'
		export SHELL=/bin/bash
	fi

	if [ ${HOME}x == x ]; then
		echo 'setting $HOME to /tmp'
		export HOME=/tmp
	fi
}

#start of main script
if [ -d $1 ]; then
	if [ ${1}x != x ]; then
		echo "change to dir $1";
		cd $1;
		if [ $? ]; then
			setVars
			convertparallel
			exit 0;
		fi
	fi
fi
exit 1;
