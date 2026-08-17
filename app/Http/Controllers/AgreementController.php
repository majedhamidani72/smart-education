<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AgreementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgreementController extends Controller
{
    public function __construct(
        protected AgreementService $agreementService
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | نمایش صفحه قوانین
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request
    ): View {

        $user = $request->user();


        abort_unless(

            $user,

            403

        );


        $type = $this->agreementService
            ->resolveAgreementType(
                $user
            );


        return view(

            'agreement.show',

            [

                'agreementType' => $type,

                'agreementText' => $this->agreementService
                    ->getAgreementText($type),

                'agreementVersion' => $this->agreementService
                    ->getAgreementVersion($type),

            ]

        );

    }


    /*
    |--------------------------------------------------------------------------
    | ثبت پذیرش قوانین
    |--------------------------------------------------------------------------
    */

    public function accept(
        Request $request
    ): RedirectResponse {

        $user = $request->user();


        abort_unless(

            $user,

            403

        );


        $request->validate([

            'accept' => [

                'accepted',

            ],

        ]);


        $this->agreementService->accept(

            $user,

            $request->ip(),

            $request->userAgent()

        );


        return redirect()->intended(

            config(

                'filament.path',

                'admin'

            )

        )->with(

            'success',

            'قوانین با موفقیت پذیرفته شد.'

        );

    }
}
