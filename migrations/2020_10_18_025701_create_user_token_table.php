<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;
use Hyperf\DbConnection\Db;

class CreateUserTokenTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_token', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_id');
            $table->string('access_token');
            $table->string('refresh_token');
            $table->integer('effective_time')
                ->unsigned()
                ->default('604800')
                ->comment('有限时长(单位秒)');
            $table->timestamp('created_at')
                ->default(Db::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')
                ->default(Db::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_token');
    }
}
