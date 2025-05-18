<?php

namespace App\Repositories;
use Illuminate\Support\Facades\Cache;
use App\Models\TrainerReview;
use App\Repositories\Contracts\TrainerReviewRepositoryInterface;

class TrainerReviewRepository implements TrainerReviewRepositoryInterface
{
    public function create(array $data)
    {
        return TrainerReview::create($data);
    }

    public function getPending()
    {
        return TrainerReview::where('status', 'pending')->paginate(10);
    }

    public function approve($id)
    {
        return TrainerReview::where('id', $id)->update(['status' => 'approved']);
    }

    public function reject($id)
    {
        return TrainerReview::where('id', $id)->update(['status' => 'rejected']);
    }
}
