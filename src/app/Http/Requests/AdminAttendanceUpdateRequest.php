<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'corrected_clock_in_time' => 'required|date_format:H:i',
            'corrected_clock_out_time' => 'required|date_format:H:i',
            'remarks' => 'required|string|max:1000',
            'breaks' => 'nullable|array',
            'breaks.*.break_id' => 'nullable|exists:breaks,id',
            'breaks.*.corrected_break_start' => 'required_with:breaks.*.break_id|nullable|date_format:H:i',
            'breaks.*.corrected_break_end' => 'required_with:breaks.*.break_id|nullable|date_format:H:i',
            'new_breaks' => 'nullable|array',
            'new_breaks.*.corrected_break_start' => 'nullable|date_format:H:i',
            'new_breaks.*.corrected_break_end' => 'nullable|date_format:H:i',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'remarks.required' => '備考を記入してください。',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('corrected_clock_in_time');
            $clockOut = $this->input('corrected_clock_out_time');

            // Rule 1: 出勤時間が退勤時間より後 / 退勤時間が出勤時間より前
            if ($clockIn && $clockOut && strtotime($clockIn) >= strtotime($clockOut)) {
                $validator->errors()->add(
                    'corrected_clock_in_time',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            $breaks = $this->input('breaks', []);
            $newBreaks = $this->input('new_breaks', []);

            // 新規休憩: 片方だけ入力されている場合はエラー
            foreach ($newBreaks ?? [] as $index => $break) {
                $start = $break['corrected_break_start'] ?? null;
                $end = $break['corrected_break_end'] ?? null;
                $hasStart = !empty(trim((string) $start));
                $hasEnd = !empty(trim((string) $end));
                if ($hasStart && !$hasEnd) {
                    $validator->errors()->add("new_breaks.{$index}.corrected_break_end", '休憩の終了時刻を入力してください。');
                    return;
                }
                if (!$hasStart && $hasEnd) {
                    $validator->errors()->add("new_breaks.{$index}.corrected_break_start", '休憩の開始時刻を入力してください。');
                    return;
                }
            }

            // Rule 2 & 3: 休憩時間の検証
            foreach (array_merge($breaks, $newBreaks ?? []) as $break) {
                $start = $break['corrected_break_start'] ?? null;
                $end = $break['corrected_break_end'] ?? null;

                if (!$start || !$end) {
                    continue;
                }

                // 休憩終了 > 休憩開始
                if (strtotime($end) <= strtotime($start)) {
                    $validator->errors()->add('breaks', '休憩の終了時刻は開始時刻より後にしてください。');
                    return;
                }

                // Rule 2: 休憩開始時間が出勤時間より前 または 退勤時間より後
                if ($clockIn && strtotime($start) < strtotime($clockIn)) {
                    $validator->errors()->add('breaks', '休憩時間が不適切な値です');
                    return;
                }
                if ($clockOut && strtotime($start) >= strtotime($clockOut)) {
                    $validator->errors()->add('breaks', '休憩時間が不適切な値です');
                    return;
                }

                // Rule 3: 休憩終了時間が退勤時間より後
                if ($clockOut && strtotime($end) > strtotime($clockOut)) {
                    $validator->errors()->add('breaks', '休憩時間もしくは退勤時間が不適切な値です');
                    return;
                }
            }
        });
    }
}
