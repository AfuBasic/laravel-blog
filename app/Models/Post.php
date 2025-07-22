<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $hidden = ['image', 'user', 'category', 'media'];
    protected $appends = ['image_url', 'preview_image_url', 'author', 'category_name'];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->slug = str($model->title)->slug();
            $model->user_id = auth()->id();
        });

        static::updating(function ($model) {
            $model->slug = str($model->title)->slug();
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('post_images') ?: asset('images/default-category.png');
    }

    public function getPreviewImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('post_images', 'preview') ?: asset('images/default-category.png');
    }

    public function getAuthorAttribute(): string
    {
        return $this->user ? $this->user->name : 'Unknown';
    }

    public function getCategoryNameAttribute(): string
    {
        return $this->category ? $this->category->name : 'Uncategorized';
    }
}
