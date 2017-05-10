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
            $table->integer('sender');
            $table->integer('recipient');
            $table->integer('sender_status')->default(0);
            $table->integer('recipient_status')->default(0);
            $table->timestamps();

            $table->unique(['sender', 'recipient']);
            $table->foreign('sender')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('recipient')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('friendships');
    }
}