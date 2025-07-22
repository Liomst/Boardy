<?php

namespace App\Models;

use Database\Factories\TodoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Todo extends Model
{
    /** @use HasFactory<TodoFactory> */
    use HasFactory;

    protected $fillable = ['title', 'position', 'status', 'description', 'due_date'];
}
