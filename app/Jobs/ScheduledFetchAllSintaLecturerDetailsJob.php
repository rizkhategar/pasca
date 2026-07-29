<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;

class ScheduledFetchAllSintaLecturerDetailsJob extends FetchAllSintaLecturerDetailsJob implements ShouldBeUnique
{
    /**
     * Satu automatic run hanya boleh mempunyai satu job Fetch All aktif/tertunda.
     * Lock dilepas setelah job selesai atau gagal.
     */
    public int $uniqueFor = 86400;

    public function uniqueId(): string
    {
        return 'scheduled-sinta-fetch-all-' . (string) ($this->automaticRunId ?? 'unknown');
    }
}
