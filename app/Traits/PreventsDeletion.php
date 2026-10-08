<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Trait PreventsDeletion
 * Prevents accidental deletion of critical data
 */
trait PreventsDeletion
{
    /**
     * Prevent deletion of models
     * @throws \Exception
     */
    public function delete()
    {
        throw new \Exception("Deletion of " . class_basename($this) . " is disabled for data protection. Contact administrator if needed.");
    }

    /**
     * Prevent force deletion
     * @throws \Exception
     */
    public function forceDelete()
    {
        throw new \Exception("Force deletion of " . class_basename($this) . " is disabled for data protection. Contact administrator if needed.");
    }
}
