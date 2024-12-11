<?php

namespace A17\Twill\Models;

use A17\Twill\Services\FileLibrary\FileService;
use Illuminate\Support\Facades\DB;

class File extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'filename',
        'size',
        'width',
        'height',
        'duration',
        'requested_hls',
    ];

    protected $videoFilesFormats = ['mp4', 'webm', 'ogg'];

    public function getSizeAttribute($value)
    {
        return bytesToHuman($value);
    }

    public function canDeleteSafely()
    {
        return DB::table(config('twill.fileables_table', 'twill_fileables'))
            ->where('file_id', $this->id)->doesntExist();
    }

    public function scopeUnused($query)
    {
        $usedIds = DB::table(config('twill.fileables_table'))->pluck('file_id');

        return $query->whereNotIn('id', $usedIds->toArray())->get();
    }

    public function scopeVideosOnly($query)
    {
        return $query->where(function ($query) {
            for ($i = 0; $i < count($this->videoFilesFormats); $i++) {
                $query->orwhere('uuid', 'like', '%' . $this->videoFilesFormats[$i]);
            }
        });
    }

    public function scopeFilesOnly($query)
    {
        return $query->where(function ($query) {
            for ($i = 0; $i < count($this->videoFilesFormats); $i++) {
                $query->where('uuid', 'not like', '%' . $this->videoFilesFormats[$i]);
            }
        });
    }

    public function getMetadata($name, $fallback = null)
    {
        $metadatas = (object) json_decode($this->pivot->metadatas);
        $language = app()->getLocale();

        if ($metadatas->$name->$language ?? false) {
            return $metadatas->$name->$language;
        }

        $fallbackLocale = config('translatable.fallback_locale');

        if (in_array($name, config('twill.media_library.translatable_metadatas_fields', [])) && config('translatable.use_property_fallback', false) && ($metadatas->$name->$fallbackLocale ?? false)) {
            return $metadatas->$name->$fallbackLocale;
        }

        $fallbackValue = $fallback ? $this->$fallback : $this->$name;

        $fallback = $fallback ?? $name;

        if (in_array($fallback, config('twill.media_library.translatable_metadatas_fields', []))) {
            $fallbackValue = $fallbackValue[$language] ?? '';

            if ($fallbackValue === '' && config('translatable.use_property_fallback', false)) {
                $fallbackValue = $this->$fallback[config('translatable.fallback_locale')] ?? '';
            }
        }

        if (is_object($metadatas->$name ?? null)) {
            return $fallbackValue ?? '';
        }

        return $metadatas->$name ?? $fallbackValue ?? '';
    }



    public function toCmsArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->filename,
            'src' => FileService::getUrl($this->uuid),
            'original' => FileService::getUrl($this->uuid),
            'size' => $this->size,
            'filesizeInMb' => number_format($this->attributes['size'] / 1048576, 2),
            'metadatas' => [
                'default' => [
                    'caption' => null
                ],
                'custom' => [
                    'caption' => null
                ],
            ],
        ];
    }

    public function getTable()
    {
        return config('twill.files_table', 'twill_files');
    }
}
