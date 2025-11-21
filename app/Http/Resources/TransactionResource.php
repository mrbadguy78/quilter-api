<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'account_id' => $this->account_id,
            'type'       => $this->type,
            'amount'     => number_format(
                (float) $this->amount,
                2,
                '.',
                ''
            ),
            'new_balance' => number_format(
                (float) $this->account->balance,
                2,
                '.',
                ''
            ),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
