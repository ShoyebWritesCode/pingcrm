<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ContactCustomColumns extends Model
{
    protected $table = 'contact_custom_columns';
    protected $fillable = ['name', 'type'];
    use HasFactory;


    public function ContactsCustomData(): HasMany
    {
        return $this->hasMany(ContactsCustomData::class);
    }


    /**
     * The "booted" method of the model.
     * Here we can define events listener on model changes
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleted(function ($model) {
            $model->handleDeletion();
        });
    }

    /**
     * Handle the deletion of the model.
     *
     * @return void
     */
    protected function handleDeletion()
    {
        $columnName = $this->name;
        $contacts = DB::table('contacts')->whereNotNull('additional_data')->get();
        foreach ($contacts as $contact) {
            $additionalData = json_decode($contact->additional_data, true);

            if (isset($additionalData[$columnName])) {
                unset($additionalData[$columnName]);

                DB::table('contacts')->where('id', $contact->id)->update([
                    'additional_data' => json_encode($additionalData),
                ]);
            }
        }
    }
}
