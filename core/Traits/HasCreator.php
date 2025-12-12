<?php

namespace MyCore\Traits;

use App\Models\User;

trait HasCreator
{
	public static function bootHasCreator()
	{
		static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            } else {
                $model->created_by = 1;
            }
		});
		static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            } else {
                $model->updated_by = 1;
            }
		});
	}

	public function creator()
	{
		return $this->belongsTo(User::class, 'created_by');
	}

	public function updater()
	{
		return $this->belongsTo(User::class, 'updated_by');
	}

	public function getCreatorAttribute()
	{
		$creator = $this->creator()->first();
		if (!$creator) {
			return null;
		}
		return $creator->only('name','phone','position');
	}

	public function getUpdaterAttribute()
	{
		$updater = $this->updater()->first();
		if (!$updater) {
			return null;	
		}
		return $updater->only('name','phone','position');
	}
}
