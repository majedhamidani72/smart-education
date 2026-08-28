<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentService;
use App\Repositories\Interfaces\PaymentTransactionRepositoryInterface;

class PaymentCallbackController extends Controller
{
    /**
     * سرویس پرداخت
     */
    protected PaymentService $paymentService;

    /**
     * ریپازیتوری تراکنش
     */
    protected PaymentTransactionRepositoryInterface $transactionRepository;

    public function __construct(
        PaymentService $paymentService,
        PaymentTransactionRepositoryInterface $transactionRepository
    ) {
        $this->paymentService = $paymentService;

        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Callback درگاه پرداخت
     */
    public function __invoke(
        Request $request
    )
    {
        $authority = $request->input('trackId');

        if (!$authority) {

            return redirect()->to(config('app.frontend_url').'?payment=failed');

        }

        $transaction = $this->transactionRepository
            ->findByAuthority(
                $authority
            );

        if (!$transaction) {

            return redirect()->to(config('app.frontend_url').'?payment=failed');

        }

        $result = $this->paymentService
            ->verifyPayment(

                $transaction,

                $request->all()

            );

        $returnTo = $transaction->return_to;
        if (! is_string($returnTo) || ! preg_match('/^\/(?!\/)/', $returnTo)) {
            $returnTo = '/';
        }

        $separator = str_contains($returnTo, '?') ? '&' : '?';
        $status = ($result['success'] ?? false) ? 'success' : 'failed';

        return redirect()->to(
            rtrim(config('app.frontend_url'), '/').$returnTo.$separator.'payment='.$status
        );
    }
}
