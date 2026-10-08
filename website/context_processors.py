"""
Global context processors — injected into every template automatically.

Provides:
  {{ portal_url }}         – Laravel admin dashboard URL
  {{ student_portal_url }} – Student portal login URL
  {{ teacher_portal_url }} – Teacher/lecturer portal login URL
  {{ site }}               – SiteSetting singleton (so base.html always has it)
"""

from django.conf import settings


def portal_urls(request):
    """Make portal URLs and the SiteSetting available in every template."""
    from .models import SiteSetting

    try:
        site = SiteSetting.objects.first()
    except Exception:
        site = None

    return {
        'portal_url':         getattr(settings, 'PORTAL_URL',         '/admin/dashboard'),
        'student_portal_url': getattr(settings, 'STUDENT_PORTAL_URL', '/portal/login'),
        'teacher_portal_url': getattr(settings, 'TEACHER_PORTAL_URL', '/lecturer/login'),
        'site': site,
    }
