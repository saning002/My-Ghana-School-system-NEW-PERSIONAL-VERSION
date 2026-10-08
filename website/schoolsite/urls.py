"""Root URL configuration for the school website project."""

from django.contrib import admin
from django.urls import path, include
from django.conf import settings
from django.conf.urls.static import static

urlpatterns = [
    # Django admin (for managing content: blog posts, gallery, events, staff …)
    path('site-admin/', admin.site.urls),

    # All public website pages (home, about, academics, admissions, gallery,
    # events, blog, contact, portal landing)
    path('', include('website.urls')),
]

# Serve uploaded media files in development (whitenoise handles static)
if settings.DEBUG:
    urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)

# Customise admin site header
admin.site.site_header  = 'School Website Admin'
admin.site.site_title   = 'School Website'
admin.site.index_title  = 'Content Management'
