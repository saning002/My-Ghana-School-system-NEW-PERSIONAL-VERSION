<?php

namespace App\Models\Website;

use App\Models\Concerns\TenantAwareConnection;
use Illuminate\Database\Eloquent\Model;

class WebsiteGalleryCategory extends Model
{
    use TenantAwareConnection;

    protected $table = 'website_gallery_categories';
    protected $fillable = ['name','slug'];

    public function images()
    {
        return $this->hasMany(WebsiteGalleryImage::class, 'category_id');
    }
}
