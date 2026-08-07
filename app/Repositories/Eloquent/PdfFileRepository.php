<?php

namespace App\Repositories\Eloquent;

use App\Models\PdfFile;
use App\Repositories\Interfaces\PdfFileRepositoryInterface;


class PdfFileRepository extends BaseRepository implements PdfFileRepositoryInterface
{
    public function __construct(
    PdfFile $pdfFile
) {
    parent::__construct($pdfFile);
}
}
