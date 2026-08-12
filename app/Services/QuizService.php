<?php

namespace App\Services;

use Throwable;
use App\Models\User;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\QuizRepositoryInterface;

class QuizService
{
    protected QuizRepositoryInterface $quizRepository;


    public function __construct(
        QuizRepositoryInterface $quizRepository
    ) {
        $this->quizRepository = $quizRepository;
    }


    public function getAll(): Collection
    {
        return $this->quizRepository->getAll();
    }


    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return $this->quizRepository->paginate($perPage);
    }


    public function findById(
        int $id
    ): ?Quiz
    {
        return $this->quizRepository->findById($id);
    }


    public function loadRelations(
        Quiz $quiz
    ): Quiz
    {
        return $quiz->load([
            'creator',
            'questions.options',
        ]);
    }


    public function start(
        Quiz $quiz,
        User $user
    ): QuizAttempt
    {
        try {

            return DB::transaction(function () use ($quiz, $user) {

                $attempt = QuizAttempt::create([

                    'quiz_id' => $quiz->id,

                    'user_id' => $user->id,

                    'status' => 'started',

                    'total_score' => 0,

                    'earned_score' => 0,

                    'started_at' => now(),

                ]);


                return $attempt->load([
                    'quiz',
                ]);

            });


        } catch (Throwable $e) {

            Log::error('Quiz start failed.', [

                'quiz_id' => $quiz->id,

                'user_id' => $user->id,

                'error' => $e->getMessage(),

            ]);


            throw $e;
        }
    }


    public function create(
        array $data
    ): Quiz
    {
        try {

            return $this->quizRepository->create($data);

        } catch (Throwable $e) {

            Log::error('Quiz creation failed.', [

                'data' => $data,

                'error' => $e->getMessage(),

            ]);


            throw $e;
        }
    }


    public function update(
        Quiz $quiz,
        array $data
    ): Quiz
    {
        try {

            return $this->quizRepository->update(
                $quiz,
                $data
            );

        } catch (Throwable $e) {

            Log::error('Quiz update failed.', [

                'quiz_id' => $quiz->id,

                'error' => $e->getMessage(),

            ]);


            throw $e;
        }
    }


    public function delete(
        Quiz $quiz
    ): bool
    {
        try {

            return $this->quizRepository->delete($quiz);

        } catch (Throwable $e) {

            Log::error('Quiz delete failed.', [

                'quiz_id' => $quiz->id,

                'error' => $e->getMessage(),

            ]);


            throw $e;
        }
    }
}
