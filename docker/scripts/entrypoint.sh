#!/bin/bash
# =============================================================================
# Docker Entrypoint
# =============================================================================
# Starts the security cleanup script in background, then runs Nginx
# =============================================================================

# Hand off to supervisor (runs php-fpm, nginx, and cleanup-php in background)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
