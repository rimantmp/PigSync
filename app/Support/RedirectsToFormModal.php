<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Mengalihkan validasi gagal ke halaman asal sambil menandai modal mana yang
 * harus terbuka kembali.
 *
 * Tanpa ini, form di dalam modal submit → validasi gagal → halaman reload →
 * modal tertutup dan user tidak melihat pesan errornya sama sekali.
 */
trait RedirectsToFormModal
{
    /**
     * Validasi; kalau gagal, kembali dengan modal & kunci field yang ditandai.
     *
     * @param  array<string, mixed>  $rules
     * @param  array<string, string>  $messages
     * @return array<string, mixed>
     */
    protected function validateForModal(Request $request, array $rules, array $messages = []): array
    {
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            // Response TIDAK boleh sudah carrying withErrors: Handler Laravel
            // mengembalikan response exception apa adanya tanpa menambah flash
            // 'errors', sehingga pesan validasi hilang total. Di sini errors
            // dipasang manual, lalu exception tetap dilempar supaya Handler
            // menulis old input.
            $response = redirect()
                ->to($this->modalReturnUrl($request))
                ->withInput()
                ->withErrors($validator->errors()->messages());

            // filled(), bukan boolean(): nilainya string nama modal
            // ("form-pigs"), yang oleh boolean() dianggap false.
            if ($request->filled('form_modal')) {
                $response->with('form_modal', $request->input('form_modal'))
                    ->with('form_keys', $validator->errors()->keys());
            }

            throw new ValidationException($validator, $response);
        }

        return $validator->validated();
    }

    /**
     * Halaman tujuan setelah submit gagal.
     *
     * Prioritas: tujuan eksplisit dari form → URL referer → fallback.
     */
    protected function modalReturnUrl(Request $request): string
    {
        if ($back = $request->input('form_back')) {
            return $back;
        }

        if ($referer = $request->headers->get('referer')) {
            // Hanya terima referer sendiri supaya input user tidak bisa
            // mengarahkan redirect ke luar situs.
            if (str_starts_with($referer, config('app.url')) || str_starts_with($referer, '/')) {
                return $referer;
            }
        }

        return url()->previous();
    }
}
