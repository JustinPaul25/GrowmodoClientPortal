<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleDriveFolder extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $hidden = [
        'folder_id',
        'organization_id',
        'created_at',
        'updated_at',
        'created_by_id',
    ];

    protected $with = ['childFolders', 'files'];

    protected $appends = ['parents'];

    public function getParentsAttribute()
    {
        if ($this->parent_id) {
            $parent = [];
            $parent_id = $this->parent_id;

            for ($found = false; !$found;) {
                $folder = GoogleDriveFolder::find($parent_id);
                $parent[] = [
                    "label" => $folder->name,
                    "value" => $folder->id
                ];
                if ($folder->parent_id) {
                    $parent_id = $folder->parent_id;
                } else {
                    $found = true;
                }
            }

            return array_reverse($parent);
        } else {
            return [];
        }
    }

    public function childFolders()
    {
        return $this->hasMany(GoogleDriveFolder::class, 'parent_id', 'id');
    }

    public function files()
    {
        return $this->hasMany(GoogleDriveFile::class, 'google_drive_folder_id', 'id');
    }
}
