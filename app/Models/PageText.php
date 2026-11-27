<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A super-admin override for one piece of text on a public page. */
class PageText extends Model
{
    protected $fillable = ['page', 'key', 'value', 'updated_by'];
}
