from django.db import models


class SiteSetting(models.Model):
    site_name        = models.CharField(max_length=100, default="EduCare International School")
    tagline          = models.CharField(max_length=300, blank=True, default="Nurturing Young Minds for Excellence")
    hero_title       = models.CharField(max_length=200)
    hero_subtitle    = models.CharField(max_length=300)
    hero_cta_text    = models.CharField(max_length=100, default="Enroll Now")
    hero_cta_url     = models.URLField(blank=True)
    stats            = models.JSONField(default=dict, blank=True)
    about_text       = models.TextField(blank=True)
    mission          = models.TextField(blank=True)
    vision           = models.TextField(blank=True)
    history          = models.TextField(blank=True)
    core_values      = models.TextField(blank=True, help_text="One core value per line")
    admissions_info  = models.TextField(blank=True)
    contact_email    = models.EmailField(blank=True)
    contact_phone    = models.CharField(max_length=30, blank=True)
    address          = models.CharField(max_length=255, blank=True)
    map_embed_url    = models.URLField(blank=True, help_text="Google Maps embed src URL")
    facebook         = models.URLField(blank=True)
    twitter          = models.URLField(blank=True)
    instagram        = models.URLField(blank=True)
    youtube          = models.URLField(blank=True)
    logo             = models.ImageField(upload_to='website/', blank=True, null=True)
    updated_at       = models.DateTimeField(auto_now=True)

    class Meta:
        verbose_name = "Site Setting"

    def __str__(self):
        return self.site_name


class StaffMember(models.Model):
    name     = models.CharField(max_length=100)
    position = models.CharField(max_length=100)
    bio      = models.TextField(blank=True)
    photo    = models.ImageField(upload_to='staff/', blank=True, null=True)
    email    = models.EmailField(blank=True)
    order    = models.PositiveIntegerField(default=0)

    class Meta:
        ordering = ['order', 'name']

    def __str__(self):
        return self.name


class Testimonial(models.Model):
    parent_name = models.CharField(max_length=100)
    child_name  = models.CharField(max_length=100, blank=True)
    message     = models.TextField()
    rating      = models.PositiveIntegerField(default=5)
    photo       = models.ImageField(upload_to='testimonials/', blank=True, null=True)
    is_active   = models.BooleanField(default=True)
    created_at  = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ['-created_at']

    def __str__(self):
        return f"{self.parent_name} – Testimonial"


class Program(models.Model):
    LEVEL_CHOICES = [
        ('daycare',      'Day Care'),
        ('nursery',      'Nursery'),
        ('preschool',    'Preschool'),
        ('kindergarten', 'Kindergarten'),
    ]
    name        = models.CharField(max_length=100)
    level       = models.CharField(max_length=20, choices=LEVEL_CHOICES)
    age_range   = models.CharField(max_length=50, help_text="e.g. 2–3 years")
    description = models.TextField()
    curriculum  = models.TextField(blank=True)
    schedule    = models.TextField(blank=True, help_text="Daily schedule details")
    icon        = models.CharField(max_length=80, blank=True, help_text="FontAwesome class e.g. fa-child")
    color       = models.CharField(max_length=30, blank=True, help_text="CSS colour e.g. #4f46e5")
    order       = models.PositiveIntegerField(default=0)
    is_active   = models.BooleanField(default=True)

    class Meta:
        ordering = ['order']

    def __str__(self):
        return self.name


class Event(models.Model):
    title        = models.CharField(max_length=200)
    description  = models.TextField()
    date         = models.DateField()
    time         = models.TimeField(blank=True, null=True)
    location     = models.CharField(max_length=200, blank=True)
    image        = models.ImageField(upload_to='events/', blank=True, null=True)
    is_published = models.BooleanField(default=True)
    created_at   = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ['date']

    def __str__(self):
        return self.title


class GalleryCategory(models.Model):
    name = models.CharField(max_length=100)
    slug = models.SlugField(unique=True)

    class Meta:
        verbose_name_plural = "Gallery Categories"
        ordering = ['name']

    def __str__(self):
        return self.name


class GalleryImage(models.Model):
    title       = models.CharField(max_length=100)
    image       = models.ImageField(upload_to='gallery/')
    caption     = models.CharField(max_length=255, blank=True)
    category    = models.ForeignKey(
        GalleryCategory, on_delete=models.SET_NULL,
        null=True, blank=True, related_name='images'
    )
    uploaded_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ['-uploaded_at']

    def __str__(self):
        return self.title


class BlogPost(models.Model):
    title          = models.CharField(max_length=200)
    slug           = models.SlugField(unique=True)
    excerpt        = models.CharField(max_length=300, blank=True)
    content        = models.TextField()
    featured_image = models.ImageField(upload_to='blog/', blank=True, null=True)
    published      = models.BooleanField(default=False)
    created_at     = models.DateTimeField(auto_now_add=True)
    updated_at     = models.DateTimeField(auto_now=True)

    class Meta:
        ordering = ['-created_at']

    def __str__(self):
        return self.title


class ContactMessage(models.Model):
    name       = models.CharField(max_length=100)
    email      = models.EmailField()
    phone      = models.CharField(max_length=30, blank=True)
    subject    = models.CharField(max_length=200)
    message    = models.TextField()
    is_read    = models.BooleanField(default=False)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ['-created_at']

    def __str__(self):
        return f"{self.name} – {self.subject}"


class AdmissionApplication(models.Model):
    STATUS_CHOICES = [
        ('pending',    'Pending'),
        ('reviewing',  'Under Review'),
        ('accepted',   'Accepted'),
        ('rejected',   'Rejected'),
    ]
    PROGRAM_CHOICES = [
        ('daycare',      'Day Care'),
        ('nursery',      'Nursery'),
        ('preschool',    'Preschool'),
        ('kindergarten', 'Kindergarten'),
    ]
    GENDER_CHOICES = [('M', 'Male'), ('F', 'Female')]

    # Child
    child_first_name  = models.CharField(max_length=100)
    child_last_name   = models.CharField(max_length=100)
    child_dob         = models.DateField()
    child_gender      = models.CharField(max_length=1, choices=GENDER_CHOICES)
    program_applying  = models.CharField(max_length=20, choices=PROGRAM_CHOICES)
    # Parent / Guardian
    parent_name       = models.CharField(max_length=100)
    parent_email      = models.EmailField()
    parent_phone      = models.CharField(max_length=30)
    relationship      = models.CharField(max_length=50)
    address           = models.TextField()
    # Additional
    previous_school   = models.CharField(max_length=200, blank=True)
    special_needs     = models.TextField(blank=True)
    how_did_you_hear  = models.CharField(max_length=200, blank=True)
    # Status (admin use)
    status            = models.CharField(max_length=20, choices=STATUS_CHOICES, default='pending')
    submitted_at      = models.DateTimeField(auto_now_add=True)
    notes             = models.TextField(blank=True)

    class Meta:
        ordering = ['-submitted_at']

    def __str__(self):
        return f"{self.child_first_name} {self.child_last_name} – {self.get_program_applying_display()}"
