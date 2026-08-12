<?php

namespace App\Services;

use Throwable;
use App\Models\Question;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\QuestionRepositoryInterface;

class QuestionService
{
    protected QuestionRepositoryInterface $questionRepository;


    public function __construct(
        QuestionRepositoryInterface $questionRepository
    ) {
        $this->questionRepository = $questionRepository;
    }



    public function getAll(): Collection
    {
        return $this->questionRepository->getAll();
    }



    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return $this->questionRepository->paginate($perPage);
    }



    public function findById(
        int $id
    ): ?Question
    {
        $question = $this->questionRepository->findById($id);


        if (!$question) {
            return null;
        }


        return $this->loadRelations($question);
    }



    public function loadRelations(
        Question $question
    ): Question
    {
        return $question->load([
            'topic',
            'creator',
            'reviewer',
            'options',
        ]);
    }



    public function create(
        array $data
    ): Question
    {
        try {

            return $this->questionRepository->create($data);

        } catch (Throwable $e) {

            Log::error('Question creation failed.', [

                'data' => $data,

                'error' => $e->getMessage(),

            ]);


            throw $e;
        }
    }



    public function update(
        Question $question,
        array $data
    ): Question
    {
        try {

            return $this->questionRepository->update(
                $question,
                $data
            );

        } catch (Throwable $e) {

            Log::error('Question update failed.', [

                'question_id' => $question->id,

                'error' => $e->getMessage(),

            ]);


            throw $e;
        }
    }



    public function delete(
        Question $question
    ): bool
    {
        try {

            return $this->questionRepository->delete($question);

        } catch (Throwable $e) {

            Log::error('Question delete failed.', [

                'question_id' => $question->id,

                'error' => $e->getMessage(),

            ]);


            throw $e;
        }
    }
}
