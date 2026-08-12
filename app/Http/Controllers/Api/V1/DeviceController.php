<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Device;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Services\DeviceService;
use App\Http\Controllers\Controller;
use App\Http\Resources\DeviceResource;
use App\Http\Requests\Device\StoreDeviceRequest;
use App\Http\Requests\Device\UpdateDeviceRequest;

class DeviceController extends Controller
{
    /**
     * Device Service
     */
    protected DeviceService $deviceService;

    /**
     * Constructor
     */
    public function __construct(
        DeviceService $deviceService
    ) {
        $this->deviceService = $deviceService;
    }

    /**
     * لیست دستگاه‌های کاربر
     */
    public function index(
        Request $request
    )
    {
        $this->authorize(
            'viewAny',
            Device::class
        );

        $devices = $this->deviceService->getUserDevices(
            $request->user()
        );

        return ApiResponse::success(
            DeviceResource::collection($devices),
            'Devices retrieved successfully.'
        );
    }

    /**
     * ثبت دستگاه
     */
    public function store(
        StoreDeviceRequest $request
    )
    {
        $this->authorize(
            'create',
            Device::class
        );

        $device = $this->deviceService->registerDevice(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success(
            new DeviceResource($device),
            'Device registered successfully.'
        );
    }

    /**
     * نمایش یک دستگاه
     */
    public function show(
        Device $device
    )
    {
        $this->authorize(
            'view',
            $device
        );

        return ApiResponse::success(
            new DeviceResource($device),
            'Device retrieved successfully.'
        );
    }

    /**
     * بروزرسانی دستگاه
     */
    public function update(
        UpdateDeviceRequest $request,
        Device $device
    )
    {
        $this->authorize(
            'update',
            $device
        );

        $device = $this->deviceService->updateDevice(
            $device,
            $request->validated()
        );

        return ApiResponse::success(
            new DeviceResource($device),
            'Device updated successfully.'
        );
    }

    /**
     * حذف دستگاه
     */
    public function destroy(
        Device $device
    )
    {
        $this->authorize(
            'delete',
            $device
        );

        $this->deviceService->deactivateDevice(
            $device
        );

        return ApiResponse::success(
            null,
            'Device deactivated successfully.'
        );
    }

    /**
     * دستگاه‌های فعال
     */
    public function activeDevices(
        Request $request
    )
    {
        $this->authorize(
            'viewAny',
            Device::class
        );

        $devices = $this->deviceService->getActiveDevices(
            $request->user()
        );

        return ApiResponse::success(
            DeviceResource::collection($devices),
            'Active devices retrieved successfully.'
        );
    }

    /**
     * فعال کردن دستگاه
     */
    public function activate(
        Device $device
    )
    {
        $this->authorize(
            'update',
            $device
        );

        $this->deviceService->activateDevice(
            $device
        );

        return ApiResponse::success(
            null,
            'Device activated successfully.'
        );
    }

    /**
     * غیرفعال کردن دستگاه
     */
    public function deactivate(
        Device $device
    )
    {
        $this->authorize(
            'update',
            $device
        );

        $this->deviceService->deactivateDevice(
            $device
        );

        return ApiResponse::success(
            null,
            'Device deactivated successfully.'
        );
    }
}
