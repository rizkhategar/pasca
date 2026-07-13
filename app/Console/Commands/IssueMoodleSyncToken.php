<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class IssueMoodleSyncToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moodle-sync:issue-token
                            {email : Email of the Pasca user that owns the integration token}
                            {--name=moodle-elearning : Token name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Issue a read-only Sanctum token for the Moodle user synchronization API';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $name = trim((string) $this->option('name'));

        if ($name === '') {
            $this->error('The token name cannot be empty.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->error("No Pasca user was found for {$email}.");

            return self::FAILURE;
        }

        $user->tokens()->where('name', $name)->delete();

        $token = $user->createToken($name, ['moodle-users:read']);

        $this->warn('Store this token securely. It will not be displayed again.');
        $this->line($token->plainTextToken);

        return self::SUCCESS;
    }
}
