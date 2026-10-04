<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date:Y-m-d',
    ];

    public function setDueDateAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['due_date'] = null;
            return;
        }

        try {
            $this->attributes['due_date'] = \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            $this->attributes['due_date'] = substr((string) $value, 0, 10);
        }
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(TaskRule::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignments', 'task_id', 'user_id')
                    ->withPivot('assigned_at')
                    ->withTimestamps();
    }

    public function scopeOrderByPriority($query, string $direction = 'asc')
    {
        $sql = "CASE priority 
            WHEN 'critical' THEN 1 
            WHEN 'high' THEN 2 
            WHEN 'medium' THEN 3 
            WHEN 'low' THEN 4 
            ELSE 5 END";

        return $query->orderByRaw("{$sql} {$direction}")
                     ->orderByRaw("CASE WHEN due_date IS NULL THEN 1 ELSE 0 END ASC")
                     ->orderBy('due_date', 'asc')
                     ->orderBy('id', 'asc');
    }

    public function scopeUnassigned($query)
    {
        return $query->doesntHave('assignments');
    }
}
