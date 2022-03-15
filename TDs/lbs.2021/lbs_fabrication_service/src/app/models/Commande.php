<?php

namespace lbs\fab\app\models;
use lbs\fab\app\models;

class Commande extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'commande';
	protected $primaryKey = 'id';
	public $timestamps = true;
	public $incrementing = false;
	protected $keytype = "string";

	const CREATED = 1;

	public function items() {
		return $this->hasMany(Item::class, "commande_id");
	}
}