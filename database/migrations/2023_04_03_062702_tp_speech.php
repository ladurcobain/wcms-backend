<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tp_speech', function (Blueprint $table) {
            $table->integer('info_id')->autoIncrement();
            $table->primary(['info_id']);
            // $table->increments('info_id');
            $table->longText('info_text_in')->nullable();
            $table->longText('info_text_en')->nullable();
            $table->tinyInteger('info_status')->length('1')->default('1')->comment('1=aktif, 2=tdk aktif');
            $table->char('info_satker')->nullable();
            $table->integer('satker_id');
            $table->index(['satker_id']);
            $table->foreign('satker_id')->references('satker_id')->on('tm_satker')->onDelete('cascade')->onUpdate('cascade');
            $table->tinyInteger('is_deleted')->length('1')->default('0');
            $table->timestamps('');
            $table->char('last_user')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tp_speech');
    }
};
