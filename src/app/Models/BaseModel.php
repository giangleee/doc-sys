<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CommonQuery;
use App\Traits\LimitRelationshipQuery;

class BaseModel extends Model
{
    use LimitRelationshipQuery;
    use CommonQuery;
}
