<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TeacherAgreement;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * چاپ قرارداد پذیرفته‌شده
 * --------------------------------------------------------------------
 * یک خروجی تمیز و مخصوص چاپ برای یک رکورد پذیرش قرارداد (چه معلم،
 * چه ادمین). فقط سوپرادمین اجازه‌ی دسترسی دارد، چون این صفحه شامل
 * اطلاعات شخصی (IP، مرورگر) کاربر دیگری است.
 */
class AgreementPrintController extends Controller
{
    public function show(
        Request $request,
        TeacherAgreement $agreement
    ): View {

        abort_unless(

            $request->user()?->hasRole('SuperAdmin'),

            403

        );

        $agreement->loadMissing('teacher');

        return view(

            'agreement.print',

            [

                'agreement' => $agreement,

            ]

        );

    }
}
