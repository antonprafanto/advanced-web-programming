<?php

declare(strict_types=1);

/**
 * REFACTORING ENTERPRISE ARCHITECTURE: CONTROLLER -> DTO -> SERVICE -> REPOSITORY -> MODEL
 * 
 * Keunggulan Arsitektur Berlapis Lengkap:
 * 1. Controller sangat ramping (Skinny Controller), bebas try-catch kotor (memanfaatkan Renderable Exception).
 * 2. Service Layer murni business logic dan HTTP-agnostic.
 * 3. DTO menjamin tipe data aman dan immutable.
 * 4. Repository Pattern mengisolasi kueri database spesifik.
 * 5. Payment Gateway & Repository diabstraksikan via Interface sehingga mudah diuji (mocking) saat Unit Test.
 */

namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function charge(int $amount, string $paymentMethod): string;
}

namespace App\Contracts\Repositories;

use App\Models\Course;

interface CourseRepositoryInterface
{
    public function findPublished(int $id): ?Course;
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

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentBusinessException extends Exception
{
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => $this->getMessage(),
        ], 422);
    }
}

namespace App\Repositories;

use App\Contracts\Repositories\CourseRepositoryInterface;
use App\Models\Course;

class EloquentCourseRepository implements CourseRepositoryInterface
{
    public function findPublished(int $id): ?Course
    {
        return Course::where('id', $id)->where('status', 'published')->first();
    }
}

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Contracts\Repositories\CourseRepositoryInterface;
use App\DTOs\EnrollmentData;
use App\Exceptions\EnrollmentBusinessException;
use App\Models\Enrollment;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;

class CourseEnrollmentService
{
    public function __construct(
        private CourseRepositoryInterface $courseRepository,
        private PaymentGatewayInterface $paymentGateway
    ) {}

    public function enroll(EnrollmentData $data): Enrollment
    {
        return DB::transaction(function () use ($data) {
            // 1. Ambil Data via Repository
            $course = $this->courseRepository->findPublished($data->courseId);
            if (!$course) {
                throw new EnrollmentBusinessException("Kursus tidak ditemukan atau belum aktif.");
            }

            // 2. Validasi Bisnis: Cegah Duplikasi
            if (Enrollment::where('user_id', $data->userId)->where('course_id', $data->courseId)->exists()) {
                throw new EnrollmentBusinessException("Pengguna sudah terdaftar di kursus ini.");
            }

            // 3. Kalkulasi Biaya & Kupon
            $finalAmount = $this->calculateTotal($course->price, $data->couponCode);

            // 4. Eksekusi Pembayaran via Abstraksi Interface
            $paymentRef = $this->paymentGateway->charge($finalAmount, $data->paymentMethod);

            // 5. Simpan ke Database
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
            throw new EnrollmentBusinessException("Kupon tidak valid atau telah kadaluwarsa.");
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

        // Bersih & Ringkas: Tanpa blok try-catch kotor
        $enrollment = $this->enrollmentService->enroll($dto);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pendaftaran kursus berhasil diproses.',
            'data'    => $enrollment,
        ], 201);
    }
}
