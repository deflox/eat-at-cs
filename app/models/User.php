<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The keywords that belong to the user.
     */
    public function keywords()
    {
        return $this->belongsToMany('App\Models\Keyword', 'user_keyword', 'user_id', 'keyword_id');
    }
}