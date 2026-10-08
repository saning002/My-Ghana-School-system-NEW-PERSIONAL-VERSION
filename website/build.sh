#!/usr/bin/env bash
# Build script for Render.com (also works on any bash-capable host)
set -o errexit

pip install -r requirements.txt
python manage.py collectstatic --no-input
python manage.py migrate
