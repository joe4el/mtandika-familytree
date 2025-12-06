<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyTree extends Model
{
    use HasFactory;

    protected $table = 'family_trees';
    protected $fillable = ['name', 'relation', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(FamilyMember::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(FamilyMember::class, 'parent_id');
    }

    public function childrenRecursive()
    {
    return $this->children()->with('childrenRecursive');
    }
}
