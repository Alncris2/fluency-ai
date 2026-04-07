<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'level' => $this->level,
            'preferences' => $this->preferences,
            'subscription_plan' => $this->subscription_plan,
            'streak_current' => $this->streak_current,
            'streak_best' => $this->streak_best,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
