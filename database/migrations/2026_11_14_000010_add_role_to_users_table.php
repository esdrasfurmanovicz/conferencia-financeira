<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 'admin' gerencia usuários; 'user' usa a conferência.
            $table->string('role', 20)->default('user')->after('email');
        });

        // Quem já está cadastrado não fica sem administrador: o usuário mais antigo vira admin.
        $first = DB::table('users')->orderBy('id')->value('id');
        if ($first) {
            DB::table('users')->where('id', $first)->update(['role' => 'admin']);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
