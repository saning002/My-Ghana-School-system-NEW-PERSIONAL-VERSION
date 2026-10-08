from django.urls import path
from .views import (
    HomeView, AboutView, AcademicsView, AdmissionsView, ApplyView,
    GalleryView, EventsView, BlogListView, BlogDetailView, ContactView,
    PortalView, StaffView,
)

urlpatterns = [
    path('',                  HomeView.as_view(),      name='home'),
    path('about/',            AboutView.as_view(),     name='about'),
    path('academics/',        AcademicsView.as_view(), name='academics'),
    path('admissions/',       AdmissionsView.as_view(),name='admissions'),
    path('admissions/apply/', ApplyView.as_view(),     name='apply'),
    path('gallery/',          GalleryView.as_view(),   name='gallery'),
    path('events/',           EventsView.as_view(),    name='events'),
    path('blog/',             BlogListView.as_view(),  name='blog'),
    path('blog/<slug:slug>/', BlogDetailView.as_view(),name='blog_detail'),
    path('contact/',          ContactView.as_view(),   name='contact'),
    path('portal/',           PortalView.as_view(),    name='portal'),
    path('staff/',            StaffView.as_view(),     name='staff'),
]

