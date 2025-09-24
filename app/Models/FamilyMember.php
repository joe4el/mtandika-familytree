<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyMember extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'relation', 'parent_id'];

    // Parent relationship
    public function parent()
    {
        return $this->belongsTo(FamilyMember::class, 'parent_id');
    }

    // Children relationship
    public function children()
    {
        return $this->hasMany(FamilyMember::class, 'parent_id');
    }

    public function childrenRecursive()
    {
    return $this->children()->with('childrenRecursive');
    }
}
