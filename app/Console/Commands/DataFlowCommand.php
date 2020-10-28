<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\DataFlow\Exception\ParamsErrorException;
use App\DataFlow\Exception\ConfigErrorException;


class DataFlowCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:flow {job} {date?} {argv?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'data flow 核心调度';

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
        try {



        } catch (ConfigErrorException $ce) {


        } catch (ParamsErrorException $pe) {

        }  catch (\Exception $e) {

        }
    }
}
