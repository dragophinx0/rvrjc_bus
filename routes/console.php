<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('bus:store-history')->everyMinute();
Schedule::command('bus:cleanup-history')->daily();
Schedule::command('bus:cleanup-reservations')->everyMinute();
Schedule::command('poll:create-daily')->daily();
