<?php
declare(strict_types=1);

/** Correcciones conservadoras para los conceptos heredados del TUPA. */
final class PortalContent
{
    public static function normalize(array $data): array
    {
        foreach ($data['tupa'] ?? [] as $i => $row) {
            $category = $row['categoria'] ?? null;
            if (is_string($category)) $data['tupa'][$i]['categoria'] = match (trim($category)) {
                '6.- CONSTANCIA Y CERTIFICADOS' => '6.- CONSTANCIAS Y CERTIFICADOS',
                '12.-ALQUILERES' => '12.- ALQUILERES',
                default => $category,
            };
            if (($row['codigo'] ?? '') === '4.1' && ($row['denominacion'] ?? '') === '4.1 ATRÍCULA REGULAR') {
                $data['tupa'][$i]['denominacion'] = '4.1 MATRÍCULA REGULAR';
            }
        }
        return $data;
    }
}
