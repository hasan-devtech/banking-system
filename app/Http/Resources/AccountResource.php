<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
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
            'user_id' => $this->user_id,
            'account_number' => $this->account_number,
            'type' => $this->type,
            'status' => $this->status,
            'balance' => $this->balance,
            'currency' => $this->currency,
            'parent_id' => $this->parent_id,
            'children' => AccountResource::collection($this->whenLoaded('allChildren')),
        ];
    }
}
