<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HealthTip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'category',
        'provided_by',
        'provider_facility',
        'provider_specialty',
        'source',
        'is_active',
        'priority'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    // Accessors
    public function getExcerptAttribute()
    {
        return Str::limit($this->content, 100);
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('M j, Y g:i A');
    }

    // Mutators
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = ucfirst(trim($value));
    }

    public function setCategoryAttribute($value)
    {
        $this->attributes['category'] = strtolower(trim($value));
    }

    // Helper Methods
    public function isFromTempAccess()
    {
        return $this->source === 'temp_access';
    }

    public function isFromRegisteredDoctor()
    {
        return $this->source === 'registered_doctor';
    }

    public function getProviderDisplayName()
    {
        if ($this->provided_by) {
            $name = $this->provided_by;
            if ($this->provider_specialty) {
                $name .= ' (' . $this->provider_specialty . ')';
            }
            if ($this->provider_facility) {
                $name .= ' - ' . $this->provider_facility;
            }
            return $name;
        }
        return 'Healthcare Provider';
    }

    public function getCategoryDisplayName()
    {
        return ucfirst(str_replace('_', ' ', $this->category));
    }

    // Static Methods
    public static function getAvailableCategories()
    {
        return [
            'general' => 'General Health',
            'diet' => 'Diet & Nutrition',
            'exercise' => 'Exercise & Fitness',
            'medication' => 'Medication',
            'lifestyle' => 'Lifestyle',
            'prevention' => 'Prevention',
            'mental_health' => 'Mental Health',
            'chronic_care' => 'Chronic Care',
            'emergency' => 'Emergency Care'
        ];
    }

    public static function getAvailableSources()
    {
        return [
            'temp_access' => 'External Doctor (Temp Access)',
            'registered_doctor' => 'Registered Doctor',
            'system' => 'System Generated',
            'patient' => 'Patient Reported'
        ];
    }
}