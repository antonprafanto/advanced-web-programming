<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\EnrollmentSuccessMail;

/**
 * ANTI-PATTERN: MONOLITHIC FAT CONTROLLER (BURUK & RAPUH)
 * 
 * Mengapa kode ini adalah bencana di skala enterprise?
 * 1. Logika bisnis terkunci di dalam controller HTTP. Jika sistem ingin menambahkan
 *    fitur pendaftaran lewat Bot WhatsApp, CLI Command, atau Mobile App, kodenya harus di-copy-paste!
 * 2. Tidak dapat di-unit test tanpa membuat HTTP request palsu.
 * 3. Bergantung langsung pada implementasi payment gateway konkret (vendor lock-in).
 * 4. Melanggar Single Responsibility Principle (SRP) dan Open-Closed Principle (OCP).
 */
class MonolithicEnrollmentController extends Controller
{
    public function enroll(Request $request)
    {
        // 1. Validasi HTTP inline di controller
        $validated = $request->validate([
            'course_id'      => 'required|exists:courses,id',
            'payment_method' => 'required|in:bca_va,mandiri_va,qris,credit_card',
            'coupon_code'    => 'nullable|string',
        ]);

        $user = auth()->user();
        $course = Course::findOrFail($validated['course_id']);

        // 2. Validasi Logika Bisnis: Cek Duplikasi
        $alreadyEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json(['message' => 'Anda sudah terdaftar di kursus ini.'], 422);
        }

        // 3. Kalkulasi Diskon & Kupon langsung di controller
        $finalAmount = $course->price;
        if (!empty($validated['coupon_code'])) {
            $coupon = Coupon::where('code', $validated['coupon_code'])
                ->where('is_active', true)
                ->where('expires_at', '>', now())
                ->first();

            if (!$coupon) {
                return response()->json(['message' => 'Kupon tidak valid atau kadaluwarsa.'], 422);
            }

            if ($coupon->type === 'fixed') {
                $finalAmount = max(0, $finalAmount - $coupon->discount_value);
            } elseif ($coupon->type === 'percent') {
                $finalAmount = (int) ($finalAmount * (1 - ($coupon->discount_value / 100)));
            }

            $coupon->increment('used_count');
        }

        // 4. Integrasi Payment Gateway Pihak Ketiga Langsung (Hardcoded)
        $paymentRef = 'TRX-' . rand(100000, 999999);
        // Simulasi panggil API eksternal...

        // 5. Database Insertion
        DB::beginTransaction();
        try {
            $enrollment = Enrollment::create([
                'user_id'           => $user->id,
                'course_id'         => $course->id,
                'amount_paid'       => $finalAmount,
                'payment_method'    => $validated['payment_method'],
                'payment_reference' => $paymentRef,
                'status'            => 'active',
            ]);

            // 6. Pengiriman Email Notifikasi
            Mail::to($user->email)->send(new EnrollmentSuccessMail($enrollment));

            DB::commit();

            // 7. Response HTTP
            return response()->json([
                'status'  => 'success',
                'message' => 'Pendaftaran berhasil.',
                'data'    => $enrollment,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memproses: ' . $e->getMessage()], 500);
        }
    }
}
