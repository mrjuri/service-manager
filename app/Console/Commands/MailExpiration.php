<?php

namespace App\Console\Commands;

use App\Http\Controllers\Email;
use Illuminate\Console\Command;

class MailExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:exp:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invio email dei servizi in scadenza';

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
     * @return mixed
     */
    public function handle()
    {
        $email = new Email();
        $email->sendExpiration();
    }
}
