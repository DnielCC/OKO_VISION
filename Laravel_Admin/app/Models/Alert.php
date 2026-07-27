<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Alert extends Model
{
    public const STATUS_PENDING   = 'PENDING';
    public const STATUS_REVIEW    = 'REVIEW';
    public const STATUS_VALIDATED = 'VALIDATED';
    public const STATUS_REPORTED  = 'REPORTED';
    public const STATUS_RESOLVED  = 'RESOLVED';

    public const STATUSES_LABELS = [
        self::STATUS_PENDING   => 'Pendiente',
        self::STATUS_REVIEW    => 'En revisión',
        self::STATUS_VALIDATED => 'Validada',
        self::STATUS_REPORTED  => 'Reportada',
        self::STATUS_RESOLVED  => 'Resuelta',
    ];

    public const SEVERITY_LABELS = [
        'CRITICAL' => 'Crítica',
        'HIGH'     => 'Alta',
        'MEDIUM'   => 'Media',
        'LOW'      => 'Baja',
    ];

    protected $fillable = [
        'title',
        'description',
        'severity',
        'created_at',
        'is_resolved',
        'vehicle_plate',
        'vehicle_owner',
        'location',
        'camera',
        'status',
        'resolved_at',
        'image_url',
        'notes',
    ];

    public $timestamps = false;

    protected $casts = [
        'created_at'  => 'datetime',
        'resolved_at' => 'datetime',
        'is_resolved' => 'boolean',
    ];

    public function getSeverityLabelAttribute(): string
    {
        return self::SEVERITY_LABELS[strtoupper($this->severity)] ?? $this->severity;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES_LABELS[$this->status] ?? $this->status;
    }

    public function getFormattedIdAttribute(): string
    {
        return 'ALT-' . str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }

    public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['severity'])) {
            $query->where('severity', strtoupper($filters['severity']));
        }

        if (!empty($filters['status'])) {
            $statusMap = [
                'pending'   => self::STATUS_PENDING,
                'review'    => self::STATUS_REVIEW,
                'validated' => self::STATUS_VALIDATED,
                'reported'  => self::STATUS_REPORTED,
                'resolved'  => self::STATUS_RESOLVED,
            ];
            $key = strtolower($filters['status']);
            if (isset($statusMap[$key])) {
                $query->where('status', $statusMap[$key]);
            }
        }

        if (!empty($filters['date_range'])) {
            $range = $filters['date_range'];
            $now = now();
            switch ($range) {
                case '24h':
                    $query->where('created_at', '>=', $now->copy()->subDay());
                    break;
                case '7d':
                    $query->where('created_at', '>=', $now->copy()->subDays(7));
                    break;
                case '30d':
                    $query->where('created_at', '>=', $now->copy()->subDays(30));
                    break;
                case 'all':
                default:
                    break;
            }
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('title', 'like', $term)
                  ->orWhere('description', 'like', $term)
                  ->orWhere('vehicle_plate', 'like', $term)
                  ->orWhere('vehicle_owner', 'like', $term)
                  ->orWhere('location', 'like', $term)
                  ->orWhere('notes', 'like', $term);
            });
        }

        return $query;
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_plate', 'plate');
    }
}
