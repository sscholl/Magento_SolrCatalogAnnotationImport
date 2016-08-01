#!/bin/bash
iconv='/usr/bin/iconv'
targetcode='LATIN1//IGNORE'

file=(`echo ${1}|sed 's/^\([^ ]*\) [^$]*$/\1/'`);
code=(`echo ${1}|sed 's/^[^ ]* \([^$]*\)$/\1/'`);
${iconv} -c -f ${code} -t ${targetcode} ${file} |\
	sed 's/[\x01-\x08\x0B\x0C\x0E-\x1F\x7F]//g' \
	> ${file}.tmp;
