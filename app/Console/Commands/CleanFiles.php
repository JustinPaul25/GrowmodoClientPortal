<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\TempFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $files = TempFile::all();
        foreach ($files as $file) {
            $difference = $file->created_at->diffInSeconds(Carbon::now());
            if ($difference >= 10800) {
                Storage::disk('local')->delete($file->path);
                $file->delete();
            }
        };

        Log::info("Temp Files Cleaned!");
    }
}
