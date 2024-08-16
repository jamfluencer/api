<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Playback\Jobs\StorePlaylist;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;

class LoadPlaylist extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:load-playlist
        {as : The ID or email of a user to perform the task as.}
        {playlist : The ID of the playlist to load.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load a playlist from Spotify into local store.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var User $user */
        $user = User::query()
            ->where('email', $this->argument('as'))
            ->orWhere('id', $this->argument('as'))
            ->sole();
        StorePlaylist::dispatchSync($user, $this->argument('playlist'));
    }
}
