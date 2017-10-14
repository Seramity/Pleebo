<?php

use App\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateQuestionsTable extends Migration
{
    public function up()
    {
        $this->schema->create('questions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sender_id');
            $table->integer('receiver_id');
            $table->text('text');
            $table->tinyInteger('anonymous')->nullable();
            $table->tinyInteger('answered')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->drop('questions');

    }
}