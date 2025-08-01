<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $hidden = ['image', 'user', 'category', 'media', 'created_at', 'updated_at'];
    protected $appends = ['image_url', 'preview_image_url', 'author', 'category_name', 'post_date'];

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
        return $this->belongsTo(PostCategory::class, 'post_category_id');
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

    public function getPostDateAttribute(): string
    {
        return Carbon::parse($this->created_at)->format('d M, Y h:i a');
    }

    public function getRelatedPosts()
    {
        return Post::where('post_category_id', $this->post_category_id)
            ->where('id', '<>', $this->id)
            ->inRandomOrder()
            ->limit(3)
            ->get();
    }
}
