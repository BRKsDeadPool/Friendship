<?php

use Illuminate\Database\Migrations\Migration;
use BRKsDeadPool\Friendship\Interfaces\MigrationContract;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CreateFriendshipsTable extends Migration implements MigrationContract
{
    public function up()
    {
        Schema::create('friendships', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('sender');
            $table->morphs('recipient');
            $table->integer('sender_status')->default(0);
            $table->integer('recipient_status')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('friendships');
    }
}