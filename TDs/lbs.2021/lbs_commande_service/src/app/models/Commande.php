<?php

namespace lbs\command\app\models;
use lbs\command\app\models;

class Commande extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'commande';
	protected $primaryKey = 'id';
	public $timestamps = true;
	public $incrementing = false;
	protected $keytype = "string";


	public function items() {
		return $this->hasMany(Item::class, "commande_id");
	}
}
