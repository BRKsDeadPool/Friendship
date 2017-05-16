<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CreateFriendshipsTable extends Migration
{
    public function up()
    {
        Schema::create('friendships', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sender')->unsigned();
            $table->integer('recipient')->unsigned();
            $table->integer('sender_status')->default(0);
            $table->integer('recipient_status')->default(0);
            $table->foreign('sender')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('recipient')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['sender', 'recipient']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('friendships');
    }
}