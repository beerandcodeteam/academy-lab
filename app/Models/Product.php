<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'eduzz_id',
        'name',
        'description',
        'slug',
        'cover',
        'redirect_url',
        'featured',
        'position',
    ];

    /**
     * Get the users that have access to this product.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('starts_at', 'expires_at', 'status')
            ->withTimestamps()
            ->withCasts([
                'starts_at' => 'datetime',
                'expires_at' => 'datetime',
            ]);
    }

    /**
     * Get the tracks associated with this product.
     */
    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class)
            ->withPivot('position', 'visibility')
            ->withTimestamps()
            ->orderByPivot('position');
    }

    public function productTracks(): HasMany
    {
        return $this->hasMany(ProductTrack::class);
    }

    public function getTotalDurationAttribute()
    {
        $totalSeconds = DB::table('product_track')
            ->join('product_track_path', 'product_track.id', '=', 'product_track_path.product_track_id')
            ->join('paths', 'product_track_path.path_id', '=', 'paths.id')
            ->where('product_track.product_id', $this->id)
            ->sum('paths.duration');

        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);

        return sprintf('%dh %02dmin', $hours, $minutes);
    }
}
