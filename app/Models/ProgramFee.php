<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ChurchBranch;

class ProgramFee extends Model
{
    use HasFactory;

    protected $fillable = ['program_id', 'church_branch_id', 'amount', 'exam_fee'];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function churchBranch()
    {
        return $this->belongsTo(ChurchBranch::class);
    }
}
