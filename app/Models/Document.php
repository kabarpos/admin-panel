<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Document extends Model implements HasMedia
{
    use HasFactory, LogsActivity, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'user_id',
        'document_form_id',
        'uploaded_at',
        'status',
        'metadata',
        'whatsapp_number',
        'notification_sent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'uploaded_at' => 'datetime',
        'metadata' => 'array',
        'notification_sent' => 'boolean',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($document) {
            Log::info('Document creating event fired', [
                'document_form_id' => $document->document_form_id,
                'name' => $document->name,
                'has_form_id' => !empty($document->document_form_id),
                'document_form_id_type' => gettype($document->document_form_id),
                'document_attributes' => $document->getAttributes()
            ]);
            
            // Validasi dokumen form ID
            if (empty($document->document_form_id)) {
                Log::error('Mencoba membuat dokumen tanpa document_form_id');
                throw new \Exception('Document form ID wajib diisi');
            }
            
            // Pastikan document_form_id adalah integer
            if (is_string($document->document_form_id)) {
                $document->document_form_id = (int)$document->document_form_id;
                Log::info('Mengkonversi document_form_id dari string ke integer: ' . $document->document_form_id);
            }
            
            if (empty($document->uploaded_at)) {
                $document->uploaded_at = Carbon::now();
            }
            if (empty($document->user_id)) {
                $document->user_id = auth()->id() ?? 1; // Default to admin user if not authenticated
            }
        });
        
        static::created(function ($document) {
            Log::info('Document created event fired', [
                'id' => $document->id,
                'document_form_id' => $document->document_form_id,
                'name' => $document->name
            ]);
        });
        
        static::saving(function ($document) {
            Log::info('Document saving event fired', [
                'id' => $document->id ?? 'new',
                'document_form_id' => $document->document_form_id,
                'name' => $document->name
            ]);
            
            // Pastikan document_form_id tidak kosong
            if (empty($document->document_form_id)) {
                Log::error('Mencoba menyimpan dokumen tanpa document_form_id');
                return false;
            }
            
            return true;
        });
    }

    /**
     * Get the activity log options for the model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the user that owns the document.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the document form this document belongs to.
     */
    public function documentForm()
    {
        return $this->belongsTo(DocumentForm::class);
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents')
            ->useDisk('public');
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
} 