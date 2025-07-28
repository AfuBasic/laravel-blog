<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PostCategory extends Model implements HasMedia
{
    protected $table = 'categories';
    protected $appends = ['image_url', 'preview_image_url'];
    protected $hidden = ['pivot', 'created_at', 'updated_at', 'media', 'category_image'];
    use InteractsWithMedia;
    //

    public static function booted()
    {
        static::creating(function ($model) {
            $model->slug = str($model->name)->slug();
        });
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'post_category_id');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();
    }

    public function getImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('category_images') ?: asset('images/default-category.png');
    }

    public function getPreviewImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('category_images', 'preview') ?: asset('images/default-category.png');
    }
}
