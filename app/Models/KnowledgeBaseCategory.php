<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeBaseCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function knowledgeBases()
    {
        return $this->hasMany(KnowledgeBase::class);
    }
}
