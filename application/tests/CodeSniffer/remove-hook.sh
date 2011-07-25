#!/bin/bash

hgrcfile=".hg/hgrc"

if  grep 'hooks' $hgrcfile --quiet; then
    if grep 'precommit.phpcs' $hgrcfile --quiet; then
        cp -n $hgrcfile $hgrcfile.org
        sed -i '/precommit.phpcs.*$/d' $hgrcfile
    fi
fi
