<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Carbon\Carbon;

class Document extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'patient_id',
        'uploaded_by',
        'title',
        'description',
        'file_name',
        'file_type',
        'file_size',
        'file_data',
        'file_hash',
        'category',
        'tags',
        'is_confidential',
        'status',
        'metadata'
    ];

    protected $casts = [
        'tags' => 'array',
        'is_confidential' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $hidden = [
        'file_data' // Hide file data from JSON serialization for performance
    ];

    protected $appends = [
        'file_size_human',
        'category_label',
        'file_icon',
        'created_at_human'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($document) {
            if (empty($document->id)) {
                $document->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    /**
     * Relationships
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

// In PatientDocument model



    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function shares()
    {
        return $this->hasMany(DocumentShare::class);
    }

    public function comments()
    {
        return $this->hasMany(DocumentComment::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeConfidential($query)
    {
        return $query->where('is_confidential', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function($q) use ($searchTerm) {
            $q->where('title', 'like', "%{$searchTerm}%")
              ->orWhere('description', 'like', "%{$searchTerm}%")
              ->orWhere('tags', 'like', "%{$searchTerm}%");
        });
    }

    public function scopeRecentUploads($query, $days = 30)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Accessors
     */
    public function getFileSizeHumanAttribute()
    {
        return $this->formatFileSize($this->file_size);
    }

    public function getCategoryLabelAttribute()
    {
        $labels = [
            'lab_report' => 'Lab Report',
            'imaging' => 'Medical Imaging',
            'prescription' => 'Prescription',
            'insurance' => 'Insurance Document',
            'consultation_note' => 'Consultation Note',
            'discharge_summary' => 'Discharge Summary',
            'referral' => 'Referral Letter',
            'consent_form' => 'Consent Form',
            'other' => 'Other Document'
        ];

        return $labels[$this->category] ?? 'Document';
    }

    public function getFileIconAttribute()
    {
        $icons = [
            'application/pdf' => '📄',
            'application/msword' => '📝',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '📝',
            'image/jpeg' => '🖼️',
            'image/jpg' => '🖼️',
            'image/png' => '🖼️',
            'image/gif' => '🖼️',
            'text/plain' => '📋',
            'text/csv' => '📊',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => '📊',
            'application/vnd.ms-excel' => '📊'
        ];

        return $icons[$this->file_type] ?? '📄';
    }

    public function getCreatedAtHumanAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getTagsArrayAttribute()
    {
        if (is_string($this->tags)) {
            return json_decode($this->tags, true) ?? [];
        }
        return $this->tags ?? [];
    }

    /**
     * Methods
     */
    public function formatFileSize($bytes)
    {
        if ($bytes === 0) return '0 Bytes';

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    public function isImage()
    {
        return strpos($this->file_type, 'image/') === 0;
    }

    public function isPdf()
    {
        return $this->file_type === 'application/pdf';
    }

    public function isViewableInline()
    {
        return $this->isImage() || $this->isPdf();
    }

    public function canBeSharedWith($userType)
    {
        $allowedShares = [
            'doctor' => true,
            'emergency_contact' => !$this->is_confidential,
            'family' => !$this->is_confidential,
            'insurance' => $this->category === 'insurance'
        ];

        return $allowedShares[$userType] ?? false;
    }

    public function hasTag($tag)
    {
        $tags = $this->tags_array;
        return in_array(strtolower($tag), array_map('strtolower', $tags));
    }

    public function addTag($tag)
    {
        $tags = $this->tags_array;
        if (!$this->hasTag($tag)) {
            $tags[] = trim($tag);
            $this->tags = array_values(array_unique($tags));
            $this->save();
        }
        return $this;
    }

    public function removeTag($tag)
    {
        $tags = $this->tags_array;
        $tags = array_filter($tags, function($t) use ($tag) {
            return strtolower($t) !== strtolower($tag);
        });
        $this->tags = array_values($tags);
        $this->save();
        return $this;
    }

    public function getFileContent()
    {
        return base64_decode($this->file_data);
    }

    public function updateFileData($fileContent)
    {
        $this->file_data = base64_encode($fileContent);
        $this->file_hash = hash('sha256', $this->file_data);
        $this->file_size = strlen($fileContent);
        return $this;
    }

    public function duplicate($newTitle = null)
    {
        $duplicate = $this->replicate();
        $duplicate->id = (string) \Illuminate\Support\Str::uuid();
        $duplicate->title = $newTitle ?? ($this->title . ' (Copy)');
        $duplicate->created_at = now();
        $duplicate->updated_at = now();
        $duplicate->save();

        return $duplicate;
    }

    public function archive()
    {
        $this->status = 'archived';
        $this->save();
        return $this;
    }

    public function restore()
    {
        $this->status = 'active';
        $this->save();
        return $this;
    }

    public function markAsDeleted()
    {
        $this->status = 'deleted';
        $this->save();
        $this->delete(); // Soft delete
        return $this;
    }

    public function getSecurityLevel()
    {
        if ($this->is_confidential) {
            return 'high';
        }

        $sensativeCategories = ['lab_report', 'prescription', 'consultation_note'];
        if (in_array($this->category, $sensativeCategories)) {
            return 'medium';
        }

        return 'low';
    }

    public function generateShareToken($expiresInDays = 7)
    {
        $token = \Illuminate\Support\Str::random(64);

        DocumentShare::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'document_id' => $this->id,
            'share_token' => $token,
            'expires_at' => Carbon::now()->addDays($expiresInDays),
            'status' => 'active'
        ]);

        return $token;
    }

    public function getMetadata($key = null, $default = null)
    {
        if ($key === null) {
            return $this->metadata ?? [];
        }

        return data_get($this->metadata, $key, $default);
    }

    public function setMetadata($key, $value = null)
    {
        if (is_array($key)) {
            $this->metadata = array_merge($this->metadata ?? [], $key);
        } else {
            $metadata = $this->metadata ?? [];
            $metadata[$key] = $value;
            $this->metadata = $metadata;
        }

        $this->save();
        return $this;
    }

    /**
     * Static methods
     */
    public static function findByHash($hash, $patientId = null)
    {
        $query = static::where('file_hash', $hash)->where('status', 'active');

        if ($patientId) {
            $query->where('patient_id', $patientId);
        }

        return $query->first();
    }

    public static function getStorageStats($patientId = null)
    {
        $query = static::where('status', 'active');

        if ($patientId) {
            $query->where('patient_id', $patientId);
        }

        $totalSize = $query->sum('file_size');
        $totalCount = $query->count();

        return [
            'total_size' => $totalSize,
            'total_size_human' => (new static)->formatFileSize($totalSize),
            'total_count' => $totalCount,
            'average_size' => $totalCount > 0 ? $totalSize / $totalCount : 0
        ];
    }

    public static function getCategoryStats($patientId = null)
    {
        $query = static::where('status', 'active');

        if ($patientId) {
            $query->where('patient_id', $patientId);
        }

        return $query->selectRaw('category, COUNT(*) as count, SUM(file_size) as total_size')
            ->groupBy('category')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->category => [
                    'count' => $item->count,
                    'total_size' => $item->total_size,
                    'total_size_human' => (new static)->formatFileSize($item->total_size)
                ]];
            });
    }

    public static function searchDocuments($searchTerm, $patientId = null, $filters = [])
    {
        $query = static::where('status', 'active');

        if ($patientId) {
            $query->where('patient_id', $patientId);
        }

        if (!empty($searchTerm)) {
            $query->search($searchTerm);
        }

        // Apply filters
        if (isset($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        if (isset($filters['confidential'])) {
            $query->where('is_confidential', $filters['confidential']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['file_type'])) {
            $query->where('file_type', 'like', "%{$filters['file_type']}%");
        }

        if (isset($filters['tags'])) {
            foreach ((array) $filters['tags'] as $tag) {
                $query->where('tags', 'like', "%{$tag}%");
            }
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        $allowedSorts = ['created_at', 'updated_at', 'title', 'file_size', 'category'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query;
    }

    /**
     * Event handlers
     */
    protected static function booted()
    {
        static::created(function ($document) {
            // Log document creation
            \Illuminate\Support\Facades\Log::info('Document created', [
                'document_id' => $document->id,
                'patient_id' => $document->patient_id,
                'title' => $document->title,
                'category' => $document->category,
                'file_size' => $document->file_size
            ]);

            // Create activity log
            if (class_exists('\App\Models\ActivityLog')) {
                \App\Models\ActivityLog::create([
                    'user_id' => $document->uploaded_by,
                    'action' => 'document_uploaded',
                    'model_type' => self::class,
                    'model_id' => $document->id,
                    'description' => "Uploaded document: {$document->title}",
                    'metadata' => [
                        'document_title' => $document->title,
                        'category' => $document->category,
                        'file_size' => $document->file_size,
                        'is_confidential' => $document->is_confidential
                    ]
                ]);
            }
        });

        static::updated(function ($document) {
            // Log significant updates
            $changes = $document->getChanges();
            $significantChanges = ['title', 'category', 'is_confidential', 'status'];

            if (array_intersect_key($changes, array_flip($significantChanges))) {
                \Illuminate\Support\Facades\Log::info('Document updated', [
                    'document_id' => $document->id,
                    'changes' => $changes
                ]);
            }
        });

        static::deleted(function ($document) {
            \Illuminate\Support\Facades\Log::info('Document deleted', [
                'document_id' => $document->id,
                'patient_id' => $document->patient_id,
                'title' => $document->title
            ]);
        });
    }

    /**
     * JSON serialization customization
     */
    public function toArray()
    {
        $array = parent::toArray();

        // Remove sensitive data when converting to array
        unset($array['file_data']);

        // Add computed fields
        $array['file_size_human'] = $this->file_size_human;
        $array['category_label'] = $this->category_label;
        $array['file_icon'] = $this->file_icon;
        $array['created_at_human'] = $this->created_at_human;
        $array['is_image'] = $this->isImage();
        $array['is_pdf'] = $this->isPdf();
        $array['is_viewable_inline'] = $this->isViewableInline();
        $array['security_level'] = $this->getSecurityLevel();

        return $array;
    }

    /**
     * Route model binding customization
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Validation rules
     */
    public static function validationRules($isUpdate = false)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|in:lab_report,imaging,prescription,insurance,consultation_note,discharge_summary,referral,consent_form,other',
            'tags' => 'nullable|string|max:500',
            'is_confidential' => 'boolean'
        ];

        if (!$isUpdate) {
            $rules['file'] = 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif,txt,csv,xlsx';
        }

        return $rules;
    }

    /**
     * Search suggestions based on existing documents
     */
    public static function getSearchSuggestions($patientId, $limit = 10)
    {
        $titles = static::where('patient_id', $patientId)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->pluck('title')
            ->toArray();

        $tags = static::where('patient_id', $patientId)
            ->where('status', 'active')
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatMap(function ($tagJson) {
                return json_decode($tagJson, true) ?? [];
            })
            ->unique()
            ->take($limit)
            ->values()
            ->toArray();

        return [
            'recent_titles' => $titles,
            'popular_tags' => $tags
        ];
    }

    /**
     * Bulk operations
     */
    public static function bulkUpdateCategory($documentIds, $category, $patientId)
    {
        return static::whereIn('id', $documentIds)
            ->where('patient_id', $patientId)
            ->where('status', 'active')
            ->update(['category' => $category, 'updated_at' => now()]);
    }

    public static function bulkArchive($documentIds, $patientId)
    {
        return static::whereIn('id', $documentIds)
            ->where('patient_id', $patientId)
            ->where('status', 'active')
            ->update(['status' => 'archived', 'updated_at' => now()]);
    }

    public static function bulkRestore($documentIds, $patientId)
    {
        return static::whereIn('id', $documentIds)
            ->where('patient_id', $patientId)
            ->where('status', 'archived')
            ->update(['status' => 'active', 'updated_at' => now()]);
    }

    public static function bulkDelete($documentIds, $patientId)
    {
        $documents = static::whereIn('id', $documentIds)
            ->where('patient_id', $patientId)
            ->get();

        foreach ($documents as $document) {
            $document->markAsDeleted();
        }

        return $documents->count();
    }

    /**
     * Export functionality
     */
    public function getExportData()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'category_label' => $this->category_label,
            'file_name' => $this->file_name,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'file_size_human' => $this->file_size_human,
            'tags' => $this->tags_array,
            'is_confidential' => $this->is_confidential,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString()
        ];
    }

    public static function exportPatientDocuments($patientId, $format = 'json')
    {
        $documents = static::where('patient_id', $patientId)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $exportData = $documents->map(function($doc) {
            return $doc->getExportData();
        });

        switch ($format) {
            case 'csv':
                return static::exportToCsv($exportData);
            case 'xlsx':
                return static::exportToExcel($exportData);
            default:
                return $exportData->toJson(JSON_PRETTY_PRINT);
        }
    }

    private static function exportToCsv($data)
    {
        if ($data->isEmpty()) {
            return '';
        }

        $headers = array_keys($data->first());
        $csv = implode(',', $headers) . "\n";

        foreach ($data as $row) {
            $values = array_map(function($value) {
                return is_array($value) ? implode(';', $value) : $value;
            }, array_values($row));
            $csv .= implode(',', $values) . "\n";
        }

        return $csv;
    }

    private static function exportToExcel($data)
    {
        // This would require a package like PhpSpreadsheet
        // For now, return CSV format
        return static::exportToCsv($data);
    }

    /**
     * Document versioning (if needed)
     */
    public function createVersion($fileContent, $versionNote = null)
    {
        if (!class_exists('\App\Models\DocumentVersion')) {
            return false;
        }

        \App\Models\DocumentVersion::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'document_id' => $this->id,
            'version_number' => $this->getNextVersionNumber(),
            'file_data' => base64_encode($fileContent),
            'file_size' => strlen($fileContent),
            'file_hash' => hash('sha256', $fileContent),
            'version_note' => $versionNote,
            'created_by' => auth()->id()
        ]);

        // Update current document
        $this->updateFileData($fileContent);
        $this->save();

        return true;
    }

    private function getNextVersionNumber()
    {
        if (!class_exists('\App\Models\DocumentVersion')) {
            return 1;
        }

        $lastVersion = \App\Models\DocumentVersion::where('document_id', $this->id)
            ->orderBy('version_number', 'desc')
            ->first();

        return $lastVersion ? $lastVersion->version_number + 1 : 1;
    }
}
