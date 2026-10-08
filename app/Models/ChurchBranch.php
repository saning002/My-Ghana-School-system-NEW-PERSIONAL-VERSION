<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChurchBranch extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'location'];

    /**
     * Derive a branch code from the branch name if none is set.
     * Takes the first two letters of each word, uppercased, max 4 chars.
     */
    public function getCodeOrDerived(): string
    {
        if ($this->code) {
            return strtoupper($this->code);
        }
        // e.g. "Kasoa Ghana" → "KA", "Ho Branch" → "HO"
        $words = preg_split('/\s+/', trim($this->name));
        $code  = strtoupper(substr($words[0], 0, 2));
        return $code ?: 'XX';
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function admins()
    {
        return $this->hasMany(User::class, 'church_branch_id')->where('role', 'admin');
    }
}
