<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class WebsiteGalleryCategory extends Model
{
    protected $table = 'website_gallery_categories';
    protected $fillable = ['name','slug'];

    public function images()
    {
        return $this->hasMany(WebsiteGalleryImage::class, 'category_id');
    }
}
