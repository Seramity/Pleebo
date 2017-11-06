<?php

use App\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateQuestionFavoritesTables extends Migration
{
    public function up()
    {
        $this->schema->create('question_favorites', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('question_id');
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->drop('question_favorites');
    }
}