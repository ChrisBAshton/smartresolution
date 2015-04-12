#!/bin/bash

if [ "$1" = "--background" ]; then
  php -S 127.0.0.1:8000 -t webapp &
  sleep 3
else
  php -S 127.0.0.1:8000 -t webapp
fi
