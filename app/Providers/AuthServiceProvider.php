<?php

namespace App\Providers;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\ContentItem;
use App\Models\Grade;
use App\Models\PaymentTransaction;
use App\Models\PdfFile;
use App\Models\Plan;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\SampleQuestion;
use App\Models\Section;
use App\Models\StepByStepPage;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\Video;
use App\Models\Device;
use App\Models\OtpCode;
use App\Models\TeacherAssignment;
use App\Policies\TeacherAssignmentPolicy;

use App\Policies\DevicePolicy;
use App\Policies\OtpCodePolicy;
use App\Policies\BookPolicy;
use App\Policies\ChapterPolicy;
use App\Policies\ContentItemPolicy;
use App\Policies\GradePolicy;
use App\Policies\PaymentTransactionPolicy;
use App\Policies\PdfFilePolicy;
use App\Policies\PlanPolicy;
use App\Policies\PurchaseItemPolicy;
use App\Policies\PurchasePolicy;
use App\Policies\QuestionOptionPolicy;
use App\Policies\QuestionPolicy;
use App\Policies\QuizPolicy;
use App\Policies\QuizAttemptPolicy;
use App\Policies\SampleQuestionPolicy;
use App\Policies\SectionPolicy;
use App\Policies\StepByStepPagePolicy;
use App\Policies\SubjectPolicy;
use App\Policies\SubscriptionPolicy;
use App\Policies\VideoPolicy;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * ثبت Policy های پروژه
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [

        Grade::class => GradePolicy::class,

        Subject::class => SubjectPolicy::class,

        Book::class => BookPolicy::class,

        Chapter::class => ChapterPolicy::class,

        Section::class => SectionPolicy::class,

        ContentItem::class => ContentItemPolicy::class,

        Video::class => VideoPolicy::class,

        PdfFile::class => PdfFilePolicy::class,

        StepByStepPage::class => StepByStepPagePolicy::class,

        SampleQuestion::class => SampleQuestionPolicy::class,

        Quiz::class => QuizPolicy::class,

        QuizAttempt::class => QuizAttemptPolicy::class,

        Question::class => QuestionPolicy::class,

        QuestionOption::class => QuestionOptionPolicy::class,

        Purchase::class => PurchasePolicy::class,

        PurchaseItem::class => PurchaseItemPolicy::class,

        Plan::class => PlanPolicy::class,

        Subscription::class => SubscriptionPolicy::class,

        PaymentTransaction::class => PaymentTransactionPolicy::class,

        Device::class => DevicePolicy::class,

        OtpCode::class => OtpCodePolicy::class,

        TeacherAssignment::class => TeacherAssignmentPolicy::class,

        
    ];

    /**
     * Bootstrap
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
