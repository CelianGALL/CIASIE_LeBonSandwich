<?php

namespace lbs\auth\api\models;

class User extends \Illuminate\Database\Eloquent\Model
{
	protected $table = 'user';
	protected $primaryKey = 'id';
	public $timestamps = true;
	public $incrementing = false;
	protected $keytype = "string";
}