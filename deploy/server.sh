#!/bin/bash

if [ "$1" = "--background" ]; then
  # if `./server.sh --background` was called and we are NOT on Travis, this file is probably being called
  # from install.php, where we pull in Travis commands because of the DRY principle. The command should
  # be ignored. If we ARE on Travis, then we need to run the server in the background.
  if [ "${SYSTEM}" = "TRAVIS" ]; then
    php -S 127.0.0.1:8000 -t webapp &
    sleep 3
  fi
else
  # we've just called `./server.sh` directly, so we probably want to run the server!
  php -S 127.0.0.1:8000 -t webapp
fi
