#!/usr/bin/env bash

rm -rf /usr/share/nginx/jamfluencer/api/storage/logs/*
rm -rf /usr/share/nginx/jamfluencer/api/storage/framework/*

if [[ -e /usr/share/nginx/jamfluencer/api/.env ]]; then
    rm /usr/share/nginx/jamfluencer/api/.env
fi
