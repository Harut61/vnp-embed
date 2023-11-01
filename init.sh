#!/bin/sh

cd /ivnews
composer require symfony/runtime
composer run-script post-install-cmd

cp -ar /ivnews /app