<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'course_name',
        'form_data',
        'status',
        'notes',
        'submitted_at',
        'reviewed_at',
    ];

    protected $casts = [
        'form_data' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the user that submitted the application
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a formatted value from form_data
     */
    public function getFormField($fieldName, $default = null)
    {
        return $this->form_data[$fieldName] ?? $default;
    }

    /**
     * Check if application is submitted
     */
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }
}
