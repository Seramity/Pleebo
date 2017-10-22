<?php

use App\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddProfileCustomizationFields extends Migration
{
    public function up()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->string('bg_color')->nullable();
            $table->string('box_color')->nullable();
            $table->string('text_color')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->dropColumn('bg_color');
            $table->dropColumn('box_color');
            $table->dropColumn('text_color');
        });
    }
}