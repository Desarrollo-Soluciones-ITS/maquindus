<?php

use App\Filament\Actions\RestoreAction;

test('la ruta restaurada usa el nuevo nombre del equipo', function () {
    $method = new ReflectionMethod(RestoreAction::class, 'restoredFilePath');
    $method->setAccessible(true);

    $result = $method->invoke(null, 'Superado/Equipos/Bomba/Manuales/x.pdf', [
        'old' => 'Bomba',
        'new' => 'Bomba (restaurado)',
    ]);

    expect($result)->toBe('Equipos/Bomba (restaurado)/Manuales/x.pdf');
});

test('la ruta restaurada no toma otros documentables', function () {
    $method = new ReflectionMethod(RestoreAction::class, 'restoredFilePath');
    $method->setAccessible(true);

    $result = $method->invoke(null, 'Superado/Repuestos/Pieza/x.pdf', [
        'old' => 'Bomba',
        'new' => 'Bomba (restaurado)',
    ]);

    expect($result)->toBe('Repuestos/Pieza/x.pdf');
});

test('el sufijo de restaurado respeta el limite de longitud', function () {
    $method = new ReflectionMethod(RestoreAction::class, 'withRestoredSuffix');
    $method->setAccessible(true);

    $result = $method->invoke(null, str_repeat('A', 90), ' (restaurado 2)');

    expect(mb_strlen($result))->toBe(80)
        ->and($result)->toEndWith(' (restaurado 2)');
});
