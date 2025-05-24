<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'comments';

    /**
     * The primary key associated with the table.
     *  
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     * 
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'guest_name',
        'comment',
    ];

    /**
     * Get the post that owns the comment.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user that owns the comment (nullable for guest comments).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the author name (user name or guest name).
     */
    public function getAuthorNameAttribute(): string
    {
        return $this->user ? $this->user->name : $this->guest_name ?? 'Anonymous';
    }

    /**
     * Check if the comment is by a guest.
     */
    public function isGuestComment(): bool
    {
        return is_null($this->user_id);
    }

    /**
     * Check if the comment is by an authenticated user.
     */
    public function isUserComment(): bool
    {
        return !is_null($this->user_id);
    }
} 