from django import forms
from .models import ContactMessage, AdmissionApplication


class ContactForm(forms.ModelForm):
    class Meta:
        model  = ContactMessage
        fields = ['name', 'email', 'phone', 'subject', 'message']
        widgets = {
            'name':    forms.TextInput(attrs={'placeholder': 'Your Full Name', 'class': 'form-control'}),
            'email':   forms.EmailInput(attrs={'placeholder': 'your@email.com', 'class': 'form-control'}),
            'phone':   forms.TextInput(attrs={'placeholder': '+63 912 345 6789', 'class': 'form-control'}),
            'subject': forms.TextInput(attrs={'placeholder': 'How can we help?', 'class': 'form-control'}),
            'message': forms.Textarea(attrs={'rows': 5, 'placeholder': 'Your message…', 'class': 'form-control'}),
        }


class AdmissionApplicationForm(forms.ModelForm):
    child_dob = forms.DateField(
        widget=forms.DateInput(attrs={'type': 'date', 'class': 'form-control'}),
        label="Child's Date of Birth"
    )

    class Meta:
        model  = AdmissionApplication
        fields = [
            'child_first_name', 'child_last_name', 'child_dob',
            'child_gender', 'program_applying',
            'parent_name', 'parent_email', 'parent_phone',
            'relationship', 'address',
            'previous_school', 'special_needs', 'how_did_you_hear',
        ]
        widgets = {
            'child_first_name': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'First Name'}),
            'child_last_name':  forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Last Name'}),
            'child_gender':     forms.Select(attrs={'class': 'form-control'}),
            'program_applying': forms.Select(attrs={'class': 'form-control'}),
            'parent_name':      forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Parent / Guardian Full Name'}),
            'parent_email':     forms.EmailInput(attrs={'class': 'form-control', 'placeholder': 'parent@email.com'}),
            'parent_phone':     forms.TextInput(attrs={'class': 'form-control', 'placeholder': '+63 912 345 6789'}),
            'relationship':     forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'e.g. Mother, Father, Guardian'}),
            'address':          forms.Textarea(attrs={'class': 'form-control', 'rows': 3, 'placeholder': 'Complete home address'}),
            'previous_school':  forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Previous school (if any)'}),
            'special_needs':    forms.Textarea(attrs={'class': 'form-control', 'rows': 3, 'placeholder': 'Any special needs or medical conditions?'}),
            'how_did_you_hear': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'e.g. Facebook, Friend, Flyer'}),
        }
