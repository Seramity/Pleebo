<?php

use App\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAnswersTable extends Migration
{
    public function up()
    {
        $this->schema->create('answers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('question_id');
            $table->text('text')->nullable();
            $table->timestamps();
            $table->timestamp('answered_at')->nullable();
        });
    }

    public function down()
    {
        $this->schema->drop('answers');
    }
}