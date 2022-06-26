#!/bin/bash

## Description: Ensure default bucket. Only run once.
## Usage: create-bucket

echo ddev-${DDEV_SITENAME}-mc

# TODO: `ddev ssh -s mc` fails, so does a command. Exit code 1. needs debugging.
docker exec ddev-${DDEV_SITENAME}-mc mc config host add --quiet --api s3v4 storage http://storage:8080 minioadmin minioadmin
docker exec ddev-${DDEV_SITENAME}-mc mc mb --quiet storage/cask
docker exec ddev-${DDEV_SITENAME}-mc mc policy set public storage/cask
