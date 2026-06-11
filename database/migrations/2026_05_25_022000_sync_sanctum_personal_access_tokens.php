<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->morphs('tokenable');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            if (!Schema::hasColumn('personal_access_tokens', 'tokenable_type')) {
                $table->string('tokenable_type')->nullable()->after('id');
            }
            if (!Schema::hasColumn('personal_access_tokens', 'tokenable_id')) {
                $table->unsignedBigInteger('tokenable_id')->nullable()->after('tokenable_type');
            }
            if (!Schema::hasColumn('personal_access_tokens', 'name')) {
                $table->string('name')->nullable()->after('tokenable_id');
            }
            if (!Schema::hasColumn('personal_access_tokens', 'token')) {
                $table->string('token', 64)->nullable()->after('name');
            }
            if (!Schema::hasColumn('personal_access_tokens', 'abilities')) {
                $table->text('abilities')->nullable()->after('token');
            }
            if (!Schema::hasColumn('personal_access_tokens', 'last_used_at')) {
                $table->timestamp('last_used_at')->nullable()->after('abilities');
            }
            if (!Schema::hasColumn('personal_access_tokens', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('last_used_at');
            }
            if (!Schema::hasColumn('personal_access_tokens', 'created_at')) {
                $table->timestamp('created_at')->nullable()->after('expires_at');
            }
            if (!Schema::hasColumn('personal_access_tokens', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });

        $this->addIndexIfMissing('personal_access_tokens', ['tokenable_type', 'tokenable_id'], 'personal_access_tokens_tokenable_type_tokenable_id_index');
        $this->addUniqueIfMissing('personal_access_tokens', ['token'], 'personal_access_tokens_token_unique');
    }

    public function down()
    {
        // Non-destructive migration: keep issued API tokens intact.
    }

    private function addIndexIfMissing(string $tableName, array $columns, string $indexName): void
    {
        $exists = DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('INDEX_NAME', $indexName)
            ->exists();

        if (!$exists) {
            Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        }
    }

    private function addUniqueIfMissing(string $tableName, array $columns, string $indexName): void
    {
        $exists = DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('INDEX_NAME', $indexName)
            ->exists();

        if (!$exists) {
            Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                $table->unique($columns, $indexName);
            });
        }
    }
};
