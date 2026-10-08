from django.contrib import admin
from .models import (
    SiteSetting, StaffMember, Testimonial, Program,
    Event, GalleryCategory, GalleryImage, BlogPost,
    ContactMessage, AdmissionApplication,
)


@admin.register(SiteSetting)
class SiteSettingAdmin(admin.ModelAdmin):
    list_display  = ('site_name', 'contact_email', 'contact_phone', 'updated_at')
    fieldsets = (
        ('General', {'fields': ('site_name', 'tagline', 'logo')}),
        ('Hero Section', {'fields': ('hero_title', 'hero_subtitle', 'hero_cta_text', 'hero_cta_url', 'stats')}),
        ('About / Mission / Vision', {'fields': ('about_text', 'mission', 'vision', 'history', 'core_values')}),
        ('Admissions', {'fields': ('admissions_info',)}),
        ('Contact & Location', {'fields': ('contact_email', 'contact_phone', 'address', 'map_embed_url')}),
        ('Social Media', {'fields': ('facebook', 'twitter', 'instagram', 'youtube')}),
    )


@admin.register(StaffMember)
class StaffMemberAdmin(admin.ModelAdmin):
    list_display  = ('name', 'position', 'order')
    search_fields = ('name', 'position')
    ordering      = ('order', 'name')


@admin.register(Testimonial)
class TestimonialAdmin(admin.ModelAdmin):
    list_display  = ('parent_name', 'child_name', 'rating', 'is_active', 'created_at')
    list_filter   = ('is_active', 'rating')
    search_fields = ('parent_name', 'child_name')
    list_editable = ('is_active',)


@admin.register(Program)
class ProgramAdmin(admin.ModelAdmin):
    list_display  = ('name', 'level', 'age_range', 'order', 'is_active')
    list_filter   = ('level', 'is_active')
    search_fields = ('name',)
    list_editable = ('order', 'is_active')


@admin.register(Event)
class EventAdmin(admin.ModelAdmin):
    list_display  = ('title', 'date', 'time', 'location', 'is_published')
    list_filter   = ('is_published',)
    search_fields = ('title', 'location')
    list_editable = ('is_published',)
    date_hierarchy = 'date'


@admin.register(GalleryCategory)
class GalleryCategoryAdmin(admin.ModelAdmin):
    list_display      = ('name', 'slug')
    prepopulated_fields = {'slug': ('name',)}


@admin.register(GalleryImage)
class GalleryImageAdmin(admin.ModelAdmin):
    list_display  = ('title', 'category', 'uploaded_at')
    list_filter   = ('category',)
    search_fields = ('title',)
    date_hierarchy = 'uploaded_at'


@admin.register(BlogPost)
class BlogPostAdmin(admin.ModelAdmin):
    list_display        = ('title', 'published', 'created_at')
    search_fields       = ('title',)
    prepopulated_fields = {'slug': ('title',)}
    list_filter         = ('published',)
    list_editable       = ('published',)
    date_hierarchy      = 'created_at'


@admin.register(ContactMessage)
class ContactMessageAdmin(admin.ModelAdmin):
    list_display  = ('name', 'email', 'subject', 'is_read', 'created_at')
    list_filter   = ('is_read',)
    search_fields = ('name', 'email', 'subject')
    list_editable = ('is_read',)
    readonly_fields = ('name', 'email', 'phone', 'subject', 'message', 'created_at')


@admin.register(AdmissionApplication)
class AdmissionApplicationAdmin(admin.ModelAdmin):
    list_display   = ('child_first_name', 'child_last_name', 'program_applying', 'parent_name', 'status', 'submitted_at')
    list_filter    = ('status', 'program_applying', 'child_gender')
    search_fields  = ('child_first_name', 'child_last_name', 'parent_name', 'parent_email')
    list_editable  = ('status',)
    date_hierarchy = 'submitted_at'
    readonly_fields = (
        'child_first_name', 'child_last_name', 'child_dob', 'child_gender',
        'program_applying', 'parent_name', 'parent_email', 'parent_phone',
        'relationship', 'address', 'previous_school', 'special_needs',
        'how_did_you_hear', 'submitted_at',
    )
