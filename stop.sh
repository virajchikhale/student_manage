#!/usr/bin/env bash
# Stops the stack. With DEMO=true (in .env or the environment) it also deletes the volumes,
# so the next start begins from scratch; with DEMO=false the data is kept.
cd "$(dirname "$(readlink -f "$0")")"
demo="${DEMO:-$(sed -n 's/^DEMO=//p' .env 2>/dev/null | tail -1)}"
case "$demo" in
  true|TRUE|True|1|yes) docker compose down -v --remove-orphans ;;
  *)                    docker compose stop ;;
esac
