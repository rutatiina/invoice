<?php

namespace Rutatiina\Invoice\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Scopes\TenantIdScope;

class InvoiceAnnex extends Model
{
    use LogsActivity;

    protected static $logName = 'Invoice Annex';
    protected static $logFillable = true;
    protected static $logAttributes = ['*'];
    protected static $logAttributesToIgnore = ['updated_at'];
    protected static $logOnlyDirty = true;

    protected $connection = 'tenant';

    protected $table = 'rg_invoice_annexes';

    protected $primaryKey = 'id';

    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new TenantIdScope);

    }

    public function rgGetAttributes()
    {
        $attributes = [];
        $describeTable =  \DB::connection('tenant')->select('describe ' . $this->getTable());

        foreach ($describeTable  as $row) {

            if (in_array($row->Field, ['id', 'created_at', 'updated_at', 'deleted_at', 'tenant_id', 'user_id'])) continue;

            if (in_array($row->Field, ['currencies', 'taxes'])) {
                $attributes[$row->Field] = [];
                continue;
            }

            if ($row->Default == '[]') {
                $attributes[$row->Field] = [];
            } else {
                $attributes[$row->Field] = ''; //$row->Default; //null affects laravel validation
            }
        }

        //add the relationships
        $attributes['receipts'] = [];

        return $attributes;
    }

    public function estimate()
    {
        return $this->hasOne($this->attributes['model'], 'model_id')->orderBy('id', 'asc');
    }

    public function order()
    {
        return $this->hasOne($this->attributes['model'], 'model_id')->orderBy('id', 'asc');
    }

    public function receipt()
    {
        return $this->hasOne('Rutatiina\Receipt\Models\Receipt', 'id', 'model_id')->orderBy('id', 'asc');
    }

    public function credit_note()
    {
        return $this->hasOne($this->attributes['model'], 'model_id')->orderBy('id', 'asc');
    }

}
