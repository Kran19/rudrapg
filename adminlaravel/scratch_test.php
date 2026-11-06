<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student = App\Models\Student::first();
$reading = app(App\Services\StudentService::class)->submitElectricityReading($student, [
    'current_reading' => '58.64',
]);
echo "Created reading: curr=" . $reading->current_reading . " units=" . $reading->units_consumed . " total=" . $reading->total_amount . PHP_EOL;

// Test validator
$v = Illuminate\Support\Facades\Validator::make(['current_reading' => '58.64'], (new App\Http\Requests\Student\SubmitElectricityReadingRequest)->rules());
echo "Validator passes: " . ($v->passes() ? "YES" : "NO") . PHP_EOL;

