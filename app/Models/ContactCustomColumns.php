<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class ContactCustomColumns extends Model
{
    protected $table = 'contact_custom_columns';
    protected $fillable = ['name', 'type'];
    use HasFactory;


    public function ContactsCustomData(): HasMany
    {
        return $this->hasMany(ContactsCustomData::class);
    }
}
