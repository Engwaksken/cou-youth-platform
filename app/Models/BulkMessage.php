<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkMessage extends Model
{
    protected $fillable = ['channel', 'subject', 'body', 'audience', 'recipient_count', 'status', 'created_by'];

    protected $casts = ['recipient_count' => 'integer'];
}
