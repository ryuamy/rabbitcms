<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $table = "news";

    protected $primaryKey = "id";

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'content',
        'featured_image',
        'status',
        'created_by',
        'updated_by'
    ];

    public function categories() {
        return $this->hasMany(Newscategories::class);
    }

    public function tags() {
        return $this->hasMany(Newstags::class);
    }

    public function logs() {
        return $this->hasMany(Newslogs::class);
    }

    public function created_by() {
        return $this->belongsTo(Admins::class, 'created_by', 'id');
    }

    public function updated_by() {
        return $this->belongsTo(Admins::class, 'updated_by', 'id');
    }

    // List of statuses
    const IS_INACTIVE = 0;
    const IS_ACTIVE = 1;
}
