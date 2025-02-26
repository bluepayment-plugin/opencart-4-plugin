#!/usr/bin/env bash

bold=$(tput bold)
normal=$(tput sgr0)
green=$(tput setaf 2)

echo "Creating package..."

rm -rf bm.ocmod.zip

cd src && zip -r "../bm.ocmod.zip" ./upload/
cd ../

echo "======================================================================================================"
echo "${green}Package ${bold}bm.ocmod.zip${normal}${green} created"
