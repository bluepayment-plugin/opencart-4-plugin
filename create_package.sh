#!/usr/bin/env bash

bold=$(tput bold)
normal=$(tput sgr0)
green=$(tput setaf 2)

echo "Creating package..."

rm -rf bluepayment.ocmod.zip

cd ./src
zip -r "../bluepayment.ocmod.zip" ./
cd ../

echo "======================================================================================================"
echo "${green}Package ${bold}bluepayment.ocmod.zip${normal}${green} created"
