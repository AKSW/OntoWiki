#!/bin/sh

for model in `owcli -l`
do
    filename=`echo "$model" | md5sum | cut -d " " -f 1`
    owcli -m "$model" -e model:export >$filename.rdf
done
