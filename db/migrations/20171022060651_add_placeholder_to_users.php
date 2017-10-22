<?php

use App\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPlaceholderToUsers extends Migration
{
    public function up()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->text('placeholder')->nullable();
        });
    }

    public function down()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->dropColumn('placeholder');
        });

    }
}