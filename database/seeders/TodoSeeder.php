<?php

namespace Database\Seeders;

use App\Models\Todo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TodoSeeder extends Seeder
{
    public function run()
    {
        $statuses = ['backlog', 'to do', 'in progress', 'testing', 'done'];

        foreach ($statuses as $status) {
            for ($i = 1; $i <= 3; $i++) {
                Todo::create([
                    'title' => ucfirst($status) . " task $i",
                    'description' => "This is a description for " . strtolower($status) . " task $i.",
                    'status' => $status,
                    'position' => $i,
                    'due_date' => Carbon::now()->addDays(rand(1, 30)),
                ]);
            }
        }
    }

}
