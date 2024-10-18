<!-- <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sensors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('street_name');
            $table->date('date');
            $table->time('time');
            $table->decimal('water_level', 8, 2); 
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // delete if exists
        Schema::dropIfExists('sensors');
    }
}; 