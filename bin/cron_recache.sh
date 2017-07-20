#!/bin/bash

if [ ! $path_app ]; then
	path=`dirname $(readlink -f $0)`
	path=`dirname ${path}`
	path=`dirname ${path}`
	path=`dirname ${path}`
	source ${path}/lib/scripts/paths.sh
fi


proc_myname=$0
proc_check=`ps aux | grep ${proc_myname} | grep bash | grep -v grep | wc -l`
proc_count=$(($proc_check - 2))


if [ "$proc_count" -gt "3" ]
then
echo "${proc_myname} as already running with count: ${proc_count}"
echo "proc_check: ${proc_check}"
echo "proc_count: ${proc_count}"
echo `ps aux | grep ${proc_myname} | grep bash | grep -v grep`
echo '################################'
exit
fi

me=`basename $0`
time_start=`date +%s`
echo "Running: ${me} with pid of $$ - starting on: `date`"

${path_cake_cmd} cacher.cacher recache
${path_cake_cmd} cacher.cacher cleanup

time_end=`date +%s`
time_diff=$(expr ${time_end} - ${time_start})
echo "Completed: ${me} with pid of $$ - seconds to complete: ${time_diff}"