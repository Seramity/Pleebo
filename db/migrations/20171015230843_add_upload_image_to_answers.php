<?php

use App\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUploadImageToAnswers extends Migration
{
    public function up()
    {
        $this->schema->table('answers', function (Blueprint $table) {
             $table->string('uploaded_image')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('', function (Blueprint $table) {
            $table->dropColumn('uploaded_image');
        });
    }
}