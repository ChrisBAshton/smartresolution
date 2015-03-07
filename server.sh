#!/bin/bash
if [ "${SYSTEM}" = "TRAVIS" ]; then
  php -S 127.0.0.1:8000 -t webapp &
fi