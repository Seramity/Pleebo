<?php

use App\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserBlocksTable extends Migration
{
    public function up()
    {
        $this->schema->create('user_blocks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('blocked_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->drop('user_blocks'); // Drop a table
    }
}