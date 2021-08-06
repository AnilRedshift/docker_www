#!/bin/bash

setenv() {
  local env_path
  if { [ -f .env ] && env_path='.env'; } || { env_path=$(git  rev-parse --show-toplevel 2>/dev/null)/.env && [ -f "$env_path" ]; }; then
    echo "sourcing $env_path"
    set -o allexport
    source "$env_path"
    set +o allexport
  else
    echo "No env file found"
  fi
}

setenv
docker-compose pull
docker-compose build
systemctl restart docker-compose-www
docker system prune -f
