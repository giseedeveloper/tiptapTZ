<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReport extends Model
{
    public const SOURCE_SCHEDULED = 'scheduled';

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_API = 'api';

    protected $fillable = [
        'restaurant_id',
        'report_date',
        'metrics',
        'pdf_path',
        'excel_path',
        'generated_at',
        'generation_source',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'metrics' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function hasPdf(): bool
    {
        return filled($this->pdf_path);
    }

    public function hasExcel(): bool
    {
        return filled($this->excel_path);
    }
}
