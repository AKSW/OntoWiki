#!/bin/bash

extensionName="$1"
if [ "$extensionName" == "" ]
then
    echo "No extension name provided"
    exit 1;
fi

extensionDir="./extensions/$extensionName"
if [ ! -e "$extensionDir" ]
then
  echo "The extension $extensionDir does not exist ..."
  exit 1
fi

phpunit --bootstrap application/tests/Bootstrap.php $extensionDir
