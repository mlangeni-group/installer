<?php

namespace Machinjiri\Installer\Stubs;

final class Environment
{
    public static function template(string $projectName): string
    {
        $email = strtolower(trim(str_replace([' ', '-', '_', '.'], '', $projectName))) . '.com';

        return <<<ENV
# -------------------------------------------------
#   Application configurations                    |
# -------------------------------------------------
APP_NAME="{$projectName}"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:3000
APP_KEY=null
APP_CIPHER=aes-256-gcm
APP_VERSION=1.0.0
APP_SUPPORT_EMAIL=admin@{$email}

# ------------------------------------------------
# Client Site Forgery (CSRF Token Name)          |
# ------------------------------------------------
CSRF_TOKEN_NAME=token-name-here

# ------------------------------------------------
# Error Reporting - to support email             |
# ------------------------------------------------
REPORT_ERRORS=false

# ------------------------------------------------
# Database Configuration                         |
# ------------------------------------------------
DB_FOREIGN_KEYS=true
DB_PREFETCH=true

DB_PLACEHOLDER=null

# -------------------------------------------------
# Cache Configuration                             |
# -------------------------------------------------
CACHE_DRIVER=file
CACHE_PREFIX={$projectName}
CACHE_DEFAULT_TTL=300
CACHE_LOCAL_STORAGE=

# -------------------------------------------------
# Session Configuration                           |
# -------------------------------------------------
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_COOKIE={$projectName}_session
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false

# ------------------------------------------------
# Queue Configuration                            |
# ------------------------------------------------
QUEUE_DRIVER=sync
QUEUE_FAILED_DRIVER=database
QUEUE_WORKER_MAX_MEMORY=256
QUEUE_WORKER_MAX_JOBS=1000

# Supervisor monitor settings
QUEUE_WORKER_CHECK_INTERVAL=5
QUEUE_WORKER_HEARTBEAT_TTL=15
QUEUE_WORKER_GRACE_PERIOD=10
QUEUE_WORKER_STOP_ON_EMPTY=false
QUEUE_WORKER_HEARTBEAT_INTERVAL=60

# ------------------------------------------------
# Mailer Configuration                           |
# ------------------------------------------------
MAIL_DRIVER=phpmailer
MAIL_DEBUG=0
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_ENCRYPTION=tls
MAIL_USERNAME=someone@{$email}
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME={$projectName}

# -------------------------------------------------
# Redis Server Configuration                      |
# -------------------------------------------------
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_DATABASE=0
REDIS_TIMEOUT=5
REDIS_READ_WRITE_TIMEOUT=2.5
REDIS_RETRY_INTERVAL=100
REDIS_PREFIX=
REDIS_SERIALIZE=true

# -------------------------------------------------
# Logging Configuration                           |
# -------------------------------------------------
LOG_CHANNEL=stack
LOG_LEVEL=debug

# ------------------------------------------------
# Asset Configuration                            |
# ------------------------------------------------
ASSET_URL=http://localhost:3000

# -------------------------------------------------
# View Configuration                              |
# -------------------------------------------------
VIEW_COMPILED_PATH=storage/framework/views

# ------------------------------------------------
# Bangwe Encryption Configuration                |
# ------------------------------------------------
JWT_SECRET=null
JWT_ALGO=HS256
JWT_EXPIRATION=3600
JWT_ISSUER={$projectName}
JWT_AUDIENCE={$projectName}-audience

# ------------------------------------------------
# File System Configuration                      |
# ------------------------------------------------
FILE_SYSTEM_DEFAULT_DRIVER=local
FILE_SYSTEM_ROOT=

# ftp connection
FILE_SYSTEM_FTP_HOST=ftp.example.com
FILE_SYSTEM_FTP_USER=your-username-here
FILE_SYSTEM_FTP_PASSWORD=null
FILE_SYSTEM_FTP_ROOT=public_html/uploads
FILE_SYSTEM_FTP_PORT=21
FILE_SYSTEM_FTP_SSL=null
FILE_SYSTEM_FTP_PASSIVE=null
FILE_SYSTEM_FTP_TIMEOUT=90
ENV;
    }
}
