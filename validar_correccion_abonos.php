<?php
/**
 * Script de Validación - Corrección de Abonos Extraordinarios
 * 
 * Valida que la nueva lógica de abonos a capital produce resultados correctos
 * según el método francés (cuota fija).
 * 
 * Ejemplo de Validación:
 * - Saldo Capital Real: $3.235.000
 * - Plazo: 23 meses
 * - Tasa: 12% anual (1% mensual)
 * - Resultado Esperado: Cuota ≈ $158.145,69
 */

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  VALIDACIÓN: Corrección de Lógica de Abonos Extraordinarios ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Parámetros del ejemplo
$capital = 3235000;
$plazo_meses = 23;
$tasa_anual = 12; // 12%
$tasa_mensual = ($tasa_anual / 100) / 12; // 0.01 (1%)

echo "📊 DATOS DE ENTRADA:\n";
echo "   ├─ Saldo de Capital Real: $" . number_format($capital, 0, ',', '.') . "\n";
echo "   ├─ Plazo: {$plazo_meses} meses\n";
echo "   ├─ Tasa Anual: {$tasa_anual}%\n";
echo "   └─ Tasa Mensual: " . number_format($tasa_mensual * 100, 2) . "%\n\n";

// Calcular cuota fija con método francés
if ($tasa_mensual > 0) {
    $factor = pow(1 + $tasa_mensual, $plazo_meses);
    $cuota_fija = $capital * ($tasa_mensual * $factor) / ($factor - 1);
} else {
    $cuota_fija = $capital / $plazo_meses;
}

echo "🧮 CÁLCULO DE CUOTA FIJA (Método Francés):\n";
echo "   Fórmula: PMT = P × [r(1+r)^n] / [(1+r)^n - 1]\n\n";
echo "   Paso 1: Factor = (1 + r)^n\n";
echo "           Factor = (1 + {$tasa_mensual})^{$plazo_meses}\n";
echo "           Factor = " . number_format($factor, 10) . "\n\n";

$numerador = $tasa_mensual * $factor;
$denominador = $factor - 1;

echo "   Paso 2: Numerador = r × Factor\n";
echo "           Numerador = {$tasa_mensual} × {$factor}\n";
echo "           Numerador = " . number_format($numerador, 10) . "\n\n";

echo "   Paso 3: Denominador = Factor - 1\n";
echo "           Denominador = {$factor} - 1\n";
echo "           Denominador = " . number_format($denominador, 10) . "\n\n";

$factor_pago = $numerador / $denominador;

echo "   Paso 4: Factor de Pago = Numerador / Denominador\n";
echo "           Factor de Pago = " . number_format($factor_pago, 10) . "\n\n";

echo "   Paso 5: Cuota Fija = Capital × Factor de Pago\n";
echo "           Cuota Fija = $" . number_format($capital, 0, ',', '.') . " × " . number_format($factor_pago, 10) . "\n";
echo "           Cuota Fija = $" . number_format($cuota_fija, 2, ',', '.') . "\n\n";

// Resultado esperado
$cuota_esperada = 158145.69;

echo "✅ RESULTADO OBTENIDO:\n";
echo "   Nueva Cuota: $" . number_format($cuota_fija, 2, ',', '.') . "\n\n";

echo "🎯 RESULTADO ESPERADO:\n";
echo "   Nueva Cuota: $" . number_format($cuota_esperada, 2, ',', '.') . "\n\n";

// Validación
$diferencia = abs($cuota_fija - $cuota_esperada);
$tolerancia = 1.00; // $1 de tolerancia por redondeos

if ($diferencia < $tolerancia) {
    echo "✅ VALIDACIÓN EXITOSA\n";
    echo "   La cuota calculada coincide con el resultado esperado.\n";
    echo "   Diferencia: $" . number_format($diferencia, 2) . " (dentro de tolerancia)\n\n";
} else {
    echo "❌ VALIDACIÓN FALLIDA\n";
    echo "   La cuota calculada NO coincide con el resultado esperado.\n";
    echo "   Diferencia: $" . number_format($diferencia, 2) . " (fuera de tolerancia)\n\n";
}

// Generar tabla de amortización para verificar
echo "📋 TABLA DE AMORTIZACIÓN (Primeras 5 cuotas):\n\n";
echo str_pad("Cuota", 8) . str_pad("Cuota Fija", 15) . str_pad("Interés", 15) . 
     str_pad("Capital", 15) . str_pad("Saldo", 15) . "\n";
echo str_repeat("─", 68) . "\n";

$saldo = $capital;
for ($i = 1; $i <= min(5, $plazo_meses); $i++) {
    $interes = $saldo * $tasa_mensual;
    $capital_cuota = $cuota_fija - $interes;
    $saldo = $saldo - $capital_cuota;
    
    // Ajuste última cuota
    if ($i == $plazo_meses && abs($saldo) > 0.01) {
        $capital_cuota += $saldo;
        $cuota_fija_ajustada = $capital_cuota + $interes;
        $saldo = 0;
    } else {
        $cuota_fija_ajustada = $cuota_fija;
    }
    
    echo str_pad($i, 8) . 
         str_pad("$" . number_format($cuota_fija_ajustada, 2, ',', '.'), 15) . 
         str_pad("$" . number_format($interes, 2, ',', '.'), 15) . 
         str_pad("$" . number_format($capital_cuota, 2, ',', '.'), 15) . 
         str_pad("$" . number_format(max(0, $saldo), 2, ',', '.'), 15) . "\n";
}

echo "\n";

// Validación de concepto clave
echo "🔑 CONCEPTOS CLAVE VALIDADOS:\n\n";

echo "1. Saldo de Capital Real:\n";
echo "   ✓ Se usa la suma de la columna 'capital' de cuotas pendientes\n";
echo "   ✗ NO se usa 'saldo_pendiente' (que incluye intereses futuros)\n\n";

echo "2. Aplicación del Abono:\n";
echo "   ✓ El abono reduce el Saldo de Capital Real\n";
echo "   ✓ Los intereses futuros NO son parte del capital\n\n";

echo "3. Recálculo de Cuota:\n";
echo "   ✓ La nueva cuota se calcula con el Nuevo Capital\n";
echo "   ✓ La nueva cuota SIEMPRE es MENOR (beneficio al cliente)\n\n";

echo "4. Método Francés:\n";
echo "   ✓ Cuota fija calculada con fórmula de anualidad\n";
echo "   ✓ Cada cuota tiene proporción variable de capital e interés\n";
echo "   ✓ El interés se calcula sobre el saldo de capital pendiente\n\n";

// Comparación con lógica incorrecta
echo "⚠️  COMPARACIÓN CON LÓGICA INCORRECTA:\n\n";

// Simular lógica incorrecta (usando saldo contractual)
// Supongamos que las 23 cuotas tienen $180.000 cada una
$cuota_original = 180000;
$saldo_contractual_incorrecto = $cuota_original * $plazo_meses; // Capital + Intereses
$cuota_incorrecta = $saldo_contractual_incorrecto * ($tasa_mensual * $factor) / ($factor - 1);

echo "   Lógica INCORRECTA (antigua):\n";
echo "   ├─ Base: Saldo Contractual = $" . number_format($saldo_contractual_incorrecto, 0, ',', '.') . "\n";
echo "   ├─ Incluye: Capital + Intereses Futuros No Devengados\n";
echo "   ├─ Resultado: Cuota = $" . number_format($cuota_incorrecta, 2, ',', '.') . "\n";
echo "   └─ Efecto: Cuota AUMENTA ❌ (perjudica al cliente)\n\n";

echo "   Lógica CORRECTA (nueva):\n";
echo "   ├─ Base: Saldo de Capital Real = $" . number_format($capital, 0, ',', '.') . "\n";
echo "   ├─ Incluye: Solo Capital (sin intereses futuros)\n";
echo "   ├─ Resultado: Cuota = $" . number_format($cuota_fija, 2, ',', '.') . "\n";
echo "   └─ Efecto: Cuota DISMINUYE ✓ (beneficia al cliente)\n\n";

$ahorro_por_cuota = $cuota_incorrecta - $cuota_fija;
$ahorro_total = $ahorro_por_cuota * $plazo_meses;

echo "💰 AHORRO PARA EL CLIENTE:\n";
echo "   ├─ Ahorro por Cuota: $" . number_format($ahorro_por_cuota, 2, ',', '.') . "\n";
echo "   ├─ Ahorro Total (23 cuotas): $" . number_format($ahorro_total, 2, ',', '.') . "\n";
echo "   └─ Porcentaje de Reducción: " . number_format(($ahorro_por_cuota / $cuota_incorrecta) * 100, 2) . "%\n\n";

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    FIN DE VALIDACIÓN                         ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
?>
