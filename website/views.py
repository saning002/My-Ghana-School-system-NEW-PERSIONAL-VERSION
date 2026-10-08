from django.views.generic import TemplateView, ListView, DetailView, FormView
from django.views import View
from django.contrib import messages
from django.shortcuts import redirect, get_object_or_404
from django.utils import timezone

from .models import (
    SiteSetting, StaffMember, BlogPost, GalleryImage,
    GalleryCategory, Testimonial, Program, Event, AdmissionApplication,
)
from .forms import ContactForm, AdmissionApplicationForm


# ─── Helpers ──────────────────────────────────────────────────────────────────

def get_site():
    return SiteSetting.objects.first()


# ─── Public Pages ─────────────────────────────────────────────────────────────

class HomeView(TemplateView):
    template_name = 'website/home.html'

    def get_context_data(self, **kwargs):
        ctx = super().get_context_data(**kwargs)
        today = timezone.now().date()
        ctx['site']         = get_site()
        ctx['staff']        = StaffMember.objects.all()[:4]
        ctx['posts']        = BlogPost.objects.filter(published=True)[:3]
        ctx['gallery']      = GalleryImage.objects.all()[:8]
        ctx['testimonials'] = Testimonial.objects.filter(is_active=True)[:6]
        ctx['programs']     = Program.objects.filter(is_active=True)[:4]
        ctx['events']       = Event.objects.filter(is_published=True, date__gte=today)[:3]
        return ctx


class AboutView(TemplateView):
    template_name = 'website/about.html'

    def get_context_data(self, **kwargs):
        ctx = super().get_context_data(**kwargs)
        ctx['site']  = get_site()
        ctx['staff'] = StaffMember.objects.all()
        return ctx


class AcademicsView(TemplateView):
    template_name = 'website/academics.html'

    def get_context_data(self, **kwargs):
        ctx = super().get_context_data(**kwargs)
        ctx['site']     = get_site()
        ctx['programs'] = Program.objects.filter(is_active=True)
        return ctx


class AdmissionsView(TemplateView):
    template_name = 'website/admissions.html'

    def get_context_data(self, **kwargs):
        ctx = super().get_context_data(**kwargs)
        ctx['site'] = get_site()
        return ctx


class ApplyView(View):
    template_name = 'website/apply.html'

    def get(self, request):
        from django.shortcuts import render
        return render(request, self.template_name, {
            'site': get_site(),
            'form': AdmissionApplicationForm(),
        })

    def post(self, request):
        from django.shortcuts import render
        form = AdmissionApplicationForm(request.POST)
        if form.is_valid():
            form.save()
            messages.success(
                request,
                "✅ Your application has been submitted! We will contact you shortly."
            )
            return redirect('apply')
        return render(request, self.template_name, {'site': get_site(), 'form': form})


class GalleryView(TemplateView):
    template_name = 'website/gallery.html'

    def get_context_data(self, **kwargs):
        ctx = super().get_context_data(**kwargs)
        ctx['site']       = get_site()
        ctx['categories'] = GalleryCategory.objects.all()
        ctx['images']     = GalleryImage.objects.select_related('category').all()
        return ctx


class EventsView(TemplateView):
    template_name = 'website/events.html'

    def get_context_data(self, **kwargs):
        ctx = super().get_context_data(**kwargs)
        today = timezone.now().date()
        ctx['site']           = get_site()
        ctx['upcoming_events'] = Event.objects.filter(is_published=True, date__gte=today)
        ctx['past_events']     = Event.objects.filter(is_published=True, date__lt=today)[:6]
        ctx['posts']           = BlogPost.objects.filter(published=True)[:5]
        return ctx


class BlogListView(ListView):
    model               = BlogPost
    template_name       = 'website/blog.html'
    context_object_name = 'posts'
    queryset            = BlogPost.objects.filter(published=True)
    paginate_by         = 9

    def get_context_data(self, **kwargs):
        ctx = super().get_context_data(**kwargs)
        ctx['site'] = get_site()
        return ctx


class BlogDetailView(DetailView):
    model               = BlogPost
    template_name       = 'website/blog_detail.html'
    context_object_name = 'post'
    slug_field          = 'slug'
    slug_url_kwarg      = 'slug'

    def get_context_data(self, **kwargs):
        ctx = super().get_context_data(**kwargs)
        ctx['site']         = get_site()
        ctx['recent_posts'] = BlogPost.objects.filter(published=True).exclude(pk=self.object.pk)[:4]
        return ctx


class ContactView(View):
    template_name = 'website/contact.html'

    def get(self, request):
        from django.shortcuts import render
        return render(request, self.template_name, {
            'site': get_site(),
            'form': ContactForm(),
        })

    def post(self, request):
        from django.shortcuts import render
        form = ContactForm(request.POST)
        if form.is_valid():
            form.save()
            messages.success(
                request,
                "✅ Thank you! Your message has been sent. We'll get back to you soon."
            )
            return redirect('contact')
        return render(request, self.template_name, {'site': get_site(), 'form': form})


class PortalView(TemplateView):
    """
    Public-facing School Portal landing page — shows links to the three
    Laravel portals (admin, teacher, student).  No Django auth required.
    """
    template_name = 'website/portal.html'

    def get_context_data(self, **kwargs):
        ctx = super().get_context_data(**kwargs)
        ctx['site'] = get_site()
        return ctx


class StaffView(TemplateView):
    template_name = 'website/staff.html'

    def get_context_data(self, **kwargs):
        ctx = super().get_context_data(**kwargs)
        ctx['site']       = get_site()
        ctx['staff_list'] = StaffMember.objects.all()
        return ctx
