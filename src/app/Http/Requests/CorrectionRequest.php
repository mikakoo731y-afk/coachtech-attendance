<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in' => ['required'],
            'clock_out' => ['required'],
            'reason' => ['required'],
            'rests.*.start' => ['nullable'],
            'rests.*.end' => ['nullable'],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            // 時刻文字列を数値（分）に変換して比較しやすくする関数
            $toMinutes = function ($time) {
                if (!$time) return null;
                $parts = explode(':', $time);
                return (int)$parts[0] * 60 + (int)($parts[1] ?? 0);
            };

            $in = $toMinutes($this->clock_in);
            $out = $toMinutes($this->clock_out);
            $rests = $this->rests ?? [];

            // 1. 出勤・退勤の相関チェック (FN029-1 / FN039-1)
            if ($in !== null && $out !== null && $in >= $out) {
                $msg = (auth()->user()->role == 1) 
                    ? '出勤時間もしくは退勤時間が不適切な値です' 
                    : '出勤時間が不適切な値です';
                $validator->errors()->add('clock_in', $msg);
            }

            // 2. 休憩時間のチェック (FN029-2,3 / FN039-2,3)
            foreach ($rests as $index => $rest) {
                $start = $toMinutes($rest['start'] ?? null);
                $end = $toMinutes($rest['end'] ?? null);

                if ($start !== null) {
                    // 休憩開始が出勤前、または退勤後
                    if (($in !== null && $start < $in) || ($out !== null && $start > $out)) {
                        $validator->errors()->add("rests.{$index}.start", '休憩時間が不適切な値です');
                    }
                }

                if ($end !== null && $out !== null) {
                    // 休憩終了が退勤後
                    if ($end > $out) {
                        $validator->errors()->add("rests.{$index}.end", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'reason.required' => '備考を記入してください',
        ];
    }
}
