<?php

namespace App\Rules;

use Closure;

class PreventIllegalCharacters
{
  public static function apply()
  {
    return function () {
      return function (string $attribute, mixed $value, Closure $fail) {
        if ($value !== null && preg_match('/[\\\\\/:\*\?"<>|\.]/', $value)) {
          $fail('El nombre contiene caracteres inválidos: \/:*?"<>|.');
          return;
        }
      };
    };
  }
}
