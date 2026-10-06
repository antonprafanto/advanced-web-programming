<?php

declare(strict_types=1);

/**
 * REFACTORING ENTERPRISE ARCHITECTURE: CONTROLLER -> DTO -> SERVICE -> MODEL
 * 
 * Keunggulan Arsitektur Berlapis:
 * 1. Controller sangat ramping (Skinny Controller), hanya mengurus protokol HTTP.
 * 2. Service Layer HTTP-agnostic: dapat dipanggil bebas dari Controller, Artisan CLI, atau Queue Job.
 * 3. DTO menjamin tipe data aman dan immutable (bebas typo array key).
 * 4. Payment Gateway diabstraksikan via Interface sehingga mudah diuji (mocking) saat Unit Test.
 */

namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function charge(int $amount, string $paymentMethod): string;
}

namespace App\DTOs;

readonly class EnrollmentData
{
    public function __construct(
        public int $userId,
        public int $courseId,
        public string $paymentMethod,
        public ?string $couponCode = null
    ) {}
}

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\EnrollmentData;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use DomainException;

class CourseEnrollmentService
{
    public function __construct(
        private PaymentGatewayInterface $paymentGateway
    ) {}

    public function enroll(EnrollmentData $data): Enrollment
    {
        return DB::transaction(function () use ($data) {
            $course = Course::findOrFail($data->courseId);

            // 1. Validasi Bisnis: Cegah Duplikasi
            if (Enrollment::where('user_id', $data->userId)->where('course_id', $data->courseId)->exists()) {
                throw new DomainException("Pengguna sudah terdaftar di kursus ini.");
            }

            // 2. Kalkulasi Biaya & Kupon
            $finalAmount = $this->calculateTotal($course->price, $data->couponCode);

            // 3. Eksekusi Pembayaran via Abstraksi Interface
            $paymentRef = $this->paymentGateway->charge($finalAmount, $data->paymentMethod);

            // 4. Simpan ke Database
            return Enrollment::create([
                'user_id'           => $data->userId,
                'course_id'         => $course->id,
                'amount_paid'       => $finalAmount,
                'payment_method'    => $data->paymentMethod,
                'payment_reference' => $paymentRef,
                'status'            => 'active',
            ]);
        });
    }

    private function calculateTotal(int $basePrice, ?string $couponCode): int
    {
        if (!$couponCode) {
            return $basePrice;
        }

        $coupon = Coupon::where('code', $couponCode)->where('is_active', true)->first();
        if (!$coupon) {
            throw new DomainException("Kupon tidak ditemukan atau tidak aktif.");
        }

        return max(0, $basePrice - $coupon->discount_value);
    }
}

namespace App\Http\Controllers;

use App\DTOs\EnrollmentData;
use App\Http\Requests\EnrollmentRequest;
use App\Services\CourseEnrollmentService;
use Illuminate\Http\JsonResponse;

class EnrollCourseController extends Controller
{
    public function __construct(
        private CourseEnrollmentService $enrollmentService
    ) {}

    public function __invoke(EnrollmentRequest $request): JsonResponse
    {
        $dto = new EnrollmentData(
            userId: (int) auth()->id(),
            courseId: (int) $request->validated('course_id'),
            paymentMethod: (string) $request->validated('payment_method'),
            couponCode: $request->validated('coupon_code')
        );

        $enrollment = $this->enrollmentService->enroll($dto);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pendaftaran kursus berhasil diproses.',
            'data'    => $enrollment,
        ], 201);
    }
}
