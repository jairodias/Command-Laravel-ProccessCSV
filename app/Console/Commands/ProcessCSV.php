<?php

namespace App\Console\Commands;

use App\Models\Sales;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ProcessCSV extends Command
{
    const PATH = 'files';
    const EXTENSION = 'csv';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proccess:csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Proccessing an file CSV';

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
        if (File::exists(public_path(static::PATH))) {
            $files = File::files(public_path(static::PATH));

            foreach ($files as $file) {
                $header = [];
                if (strtolower($file->getExtension()) == static::EXTENSION) {
                    $data = array_map('str_getcsv', file($file));

                    $progressBar = $this->output->createProgressBar(count($data));
                    $progressBar->start();

                    $header = $data[0];

                    unset($data[0]);

                    $saleData = [];
                    foreach ($data as $sale) {
                        $saleCombine = array_combine($header, $sale);
                        
                        $saleData[] = $saleCombine;

                        $this->info("Proccessing ORDER: " . $saleCombine['order_id']);

                        $progressBar->advance();
                    }

                    Sales::insert($saleData);

                    $progressBar->finish();
                }

                unlink($file);
            }
        } else {
            mkdir(public_path(static::PATH), 0755, true);
        }
    }
}
