<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\TeacherAgreement;
use App\Services\AgreementService;
use Illuminate\Console\Command;

/**
 * پر کردن متنِ قراردادهای قدیمی
 * --------------------------------------------------------------------
 * قبل از اضافه شدن ستون agreement_text، فقط «نسخه» (هش متن) ذخیره
 * می‌شد. این دستور برای هر پذیرشِ قدیمیِ بدون متن، نسخه‌ی ذخیره‌شده
 * را با هشِ متنِ *فعلیِ* قرارداد مقایسه می‌کند؛ اگر یکسان بود (یعنی
 * متن از آن موقع عوض نشده)، همان متن فعلی را به‌عنوان متن پذیرفته‌شده
 * ذخیره می‌کند. اگر متن از آن موقع عوض شده باشد (نسخه‌ها فرق دارند)،
 * هیچ حدسی نمی‌زند و آن رکورد را دست‌نخورده رها می‌کند — چون دیگر
 * راهی برای بازسازی مطمئنِ متنِ قدیمی وجود ندارد.
 */
class BackfillAgreementText extends Command
{
    protected $signature = 'agreements:backfill-text';

    protected $description = 'پر کردن متن قراردادهای قدیمی، فقط در صورتی که نسخه با متن فعلی یکسان باشد';

    public function handle(AgreementService $agreementService): int
    {
        $candidates = TeacherAgreement::query()
            ->whereNull('agreement_text')
            ->get();

        $filled = 0;
        $skipped = 0;

        foreach ($candidates as $agreement) {

            $currentText = $agreementService->getAgreementText($agreement->agreement_type);

            $currentVersion = $agreementService->getAgreementVersion($agreement->agreement_type);

            if (
                filled($currentText)
                && $currentVersion === $agreement->agreement_version
            ) {

                $agreement->update([
                    'agreement_text' => $currentText,
                ]);

                $filled++;

                continue;

            }

            $skipped++;

        }

        $this->info("متن {$filled} قرارداد پر شد.");

        $this->comment("{$skipped} قرارداد رد شد (چون نسخه‌شان با متن فعلی یکسان نبود، یعنی متن از آن موقع عوض شده).");

        return self::SUCCESS;
    }
}
