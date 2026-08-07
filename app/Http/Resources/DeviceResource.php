<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeviceResource extends JsonResource
{
    /**
     * تبدیل مدل به آرایه
     */
    public function toArray(
        Request $request
    ): array {

        return [

            'id' => $this->id,

            'device_identifier' => $this->device_identifier,

            'device_name' => $this->device_name,

            'manufacturer' => $this->manufacturer,

            'model' => $this->model,

            'platform' => $this->platform,

            'os_version' => $this->os_version,

            'app_version' => $this->app_version,

            'fcm_token' => $this->fcm_token,

            'last_ip' => $this->last_ip,

            'last_login_at' => $this->last_login_at,

            'is_active' => $this->is_active,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];

    }
}
