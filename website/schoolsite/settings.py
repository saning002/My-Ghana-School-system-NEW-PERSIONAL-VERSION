"""
Django settings for the school website project.

Environment variables (set in .env or hosting dashboard):
  SECRET_KEY      – required in production
  DEBUG           – 'True' or 'False' (default False in production)
  ALLOWED_HOSTS   – comma-separated hostnames, e.g. 'myschool.com,www.myschool.com'
  DATABASE_URL    – optional; defaults to SQLite if not set
  PORTAL_URL      – URL of the Laravel admin portal, e.g. https://myschool.com/admin
  STUDENT_PORTAL_URL – URL of the student portal, e.g. https://myschool.com/portal/login
  TEACHER_PORTAL_URL – URL of the teacher portal, e.g. https://myschool.com/lecturer/login
"""

import os
from pathlib import Path

# ─── Base ─────────────────────────────────────────────────────────────────────
BASE_DIR = Path(__file__).resolve().parent.parent

SECRET_KEY = os.environ.get(
    'SECRET_KEY',
    'django-insecure-change-me-in-production-use-a-real-random-key-here'
)

DEBUG = os.environ.get('DEBUG', 'False').strip().lower() in ('true', '1', 'yes')

_allowed_raw = os.environ.get('ALLOWED_HOSTS', '')
ALLOWED_HOSTS = [h.strip() for h in _allowed_raw.split(',') if h.strip()] or ['*']

# Trust Render / Heroku / Railway reverse proxies
CSRF_TRUSTED_ORIGINS = [
    f'https://{h}' for h in ALLOWED_HOSTS if h != '*'
]

# ─── Applications ─────────────────────────────────────────────────────────────
INSTALLED_APPS = [
    'django.contrib.admin',
    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.messages',
    'django.contrib.staticfiles',
    'website',
]

# ─── Middleware ───────────────────────────────────────────────────────────────
MIDDLEWARE = [
    'django.middleware.security.SecurityMiddleware',
    'whitenoise.middleware.WhiteNoiseMiddleware',       # static files in prod
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.middleware.common.CommonMiddleware',
    'django.middleware.csrf.CsrfViewMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'django.contrib.messages.middleware.MessageMiddleware',
    'django.middleware.clickjacking.XFrameOptionsMiddleware',
]

ROOT_URLCONF = 'schoolsite.urls'

# ─── Templates ────────────────────────────────────────────────────────────────
TEMPLATES = [
    {
        'BACKEND': 'django.template.backends.django.DjangoTemplates',
        'DIRS': [BASE_DIR / 'templates'],
        'APP_DIRS': True,
        'OPTIONS': {
            'context_processors': [
                'django.template.context_processors.debug',
                'django.template.context_processors.request',
                'django.contrib.auth.context_processors.auth',
                'django.contrib.messages.context_processors.messages',
                # Inject portal URLs and site into every template automatically
                'website.context_processors.portal_urls',
            ],
        },
    },
]

WSGI_APPLICATION = 'schoolsite.wsgi.application'

# ─── Database ─────────────────────────────────────────────────────────────────
_db_url = os.environ.get('DATABASE_URL', '')

if _db_url.startswith('postgres'):
    try:
        import dj_database_url
        DATABASES = {'default': dj_database_url.config(default=_db_url, conn_max_age=600)}
    except ImportError:
        raise ImportError(
            "DATABASE_URL is set to a Postgres URL but dj-database-url is not installed. "
            "Uncomment psycopg2-binary and dj-database-url in requirements.txt then re-run pip install."
        )
else:
    DATABASES = {
        'default': {
            'ENGINE': 'django.db.backends.sqlite3',
            'NAME': BASE_DIR / 'db.sqlite3',
        }
    }

# ─── Password validation ──────────────────────────────────────────────────────
AUTH_PASSWORD_VALIDATORS = [
    {'NAME': 'django.contrib.auth.password_validation.UserAttributeSimilarityValidator'},
    {'NAME': 'django.contrib.auth.password_validation.MinimumLengthValidator'},
    {'NAME': 'django.contrib.auth.password_validation.CommonPasswordValidator'},
    {'NAME': 'django.contrib.auth.password_validation.NumericPasswordValidator'},
]

# ─── Internationalisation ─────────────────────────────────────────────────────
LANGUAGE_CODE = 'en-us'
TIME_ZONE     = 'Africa/Accra'
USE_I18N      = True
USE_TZ        = True

# ─── Static files ─────────────────────────────────────────────────────────────
STATIC_URL  = '/static/'
STATIC_ROOT = BASE_DIR / 'staticfiles'
STATICFILES_DIRS = [BASE_DIR / 'static']
STATICFILES_STORAGE = 'whitenoise.storage.CompressedManifestStaticFilesStorage'

# ─── Media files ──────────────────────────────────────────────────────────────
MEDIA_URL  = '/media/'
MEDIA_ROOT = BASE_DIR / 'media'

# ─── Default PK ───────────────────────────────────────────────────────────────
DEFAULT_AUTO_FIELD = 'django.db.models.BigAutoField'

# ─── Portal links (injectable via env so they work on any deployment) ─────────
PORTAL_URL         = os.environ.get('PORTAL_URL',         '/admin/dashboard')
STUDENT_PORTAL_URL = os.environ.get('STUDENT_PORTAL_URL', '/portal/login')
TEACHER_PORTAL_URL = os.environ.get('TEACHER_PORTAL_URL', '/lecturer/login')

# ─── Email (configure for production) ────────────────────────────────────────
EMAIL_BACKEND = os.environ.get(
    'EMAIL_BACKEND',
    'django.core.mail.backends.console.EmailBackend'
)
EMAIL_HOST     = os.environ.get('EMAIL_HOST', 'smtp.gmail.com')
EMAIL_PORT     = int(os.environ.get('EMAIL_PORT', 587))
EMAIL_USE_TLS  = True
EMAIL_HOST_USER     = os.environ.get('EMAIL_HOST_USER', '')
EMAIL_HOST_PASSWORD = os.environ.get('EMAIL_HOST_PASSWORD', '')
DEFAULT_FROM_EMAIL  = os.environ.get('DEFAULT_FROM_EMAIL', 'noreply@myschool.com')

# ─── Security (enable in production automatically) ───────────────────────────
if not DEBUG:
    SECURE_PROXY_SSL_HEADER      = ('HTTP_X_FORWARDED_PROTO', 'https')
    SECURE_SSL_REDIRECT          = True
    SESSION_COOKIE_SECURE        = True
    CSRF_COOKIE_SECURE           = True
    SECURE_BROWSER_XSS_FILTER   = True
    SECURE_CONTENT_TYPE_NOSNIFF  = True
    X_FRAME_OPTIONS              = 'DENY'
